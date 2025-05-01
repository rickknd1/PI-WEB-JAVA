package com.syncylinky.controllers;

import com.syncylinky.models.Post;
import com.syncylinky.services.CommentService;
import com.syncylinky.services.PostService;
import com.syncylinky.services.ReactionService;
import com.syncylinky.services.ShareService;
import com.syncylinky.utils.SessionManager;
import javafx.application.Platform;
import javafx.concurrent.Task;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.control.*;
import javafx.scene.layout.GridPane;
import javafx.scene.layout.VBox;
import javafx.stage.FileChooser;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.StandardCopyOption;
import java.sql.SQLException;
import java.util.List;

public class MainController {
    private static final Logger logger = LoggerFactory.getLogger(MainController.class);

    @FXML private VBox feedContainer;
    @FXML private ProgressIndicator loadingIndicator;
    private final PostService postService;
    private final CommentService commentService;
    private final ReactionService reactionService;
    private final ShareService shareService;

    public MainController() {
        this.postService = new PostService();
        this.commentService = new CommentService();
        this.reactionService = new ReactionService();
        this.shareService = new ShareService();
    }

    @FXML
    public void initialize() {
        // Initialiser l'utilisateur connecté (exemple, à remplacer par une authentification)
        SessionManager.setCurrentUserId(1);
        loadFeed();
    }

    private void loadFeed() {
        Task<List<Post>> task = new Task<>() {
            @Override
            protected List<Post> call() throws SQLException {
                return postService.getVisiblePosts(SessionManager.getCurrentUserId());
            }
        };

        loadingIndicator.setVisible(true);
        task.setOnSucceeded(event -> {
            Platform.runLater(() -> {
                loadingIndicator.setVisible(false);
                feedContainer.getChildren().clear();
                List<Post> posts = task.getValue();
                if (posts.isEmpty()) {
                    Label emptyLabel = new Label("Aucune publication à afficher");
                    emptyLabel.getStyleClass().add("empty-label");
                    feedContainer.getChildren().add(emptyLabel);
                } else {
                    for (Post post : posts) {
                        try {
                            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/syncylinky/views/components/post-component.fxml"));
                            VBox postNode = loader.load();
                            PostController controller = loader.getController();
                            controller.initializeData(post, postService, commentService, reactionService, shareService);
                            feedContainer.getChildren().add(postNode);
                        } catch (IOException e) {
                            logger.error("Erreur lors du chargement du post {}", post.getId(), e);
                            showAlert("Erreur", "Impossible de charger le post", e.getMessage(), Alert.AlertType.ERROR);
                        }
                    }
                }
            });
        });

        task.setOnFailed(event -> {
            logger.error("Erreur lors du chargement du feed", task.getException());
            Platform.runLater(() -> {
                loadingIndicator.setVisible(false);
                showAlert("Erreur", "Impossible de charger le feed", task.getException().getMessage(), Alert.AlertType.ERROR);
            });
        });

        new Thread(task).start();
    }

    @FXML
    private void showPostDialog() {
        Dialog<Post> dialog = new Dialog<>();
        dialog.setTitle("Nouveau Post");

        TextField titleField = new TextField();
        titleField.setPromptText("Titre");
        TextArea contentField = new TextArea();
        contentField.setPromptText("Quoi de neuf ?");
        Button fileButton = new Button("Choisir une image");
        Label fileLabel = new Label("Aucun fichier sélectionné");
        File[] selectedFile = new File[1];

        fileButton.setOnAction(e -> {
            FileChooser fileChooser = new FileChooser();
            fileChooser.getExtensionFilters().add(new FileChooser.ExtensionFilter("Images", "*.png", "*.jpg", "*.jpeg"));
            File file = fileChooser.showOpenDialog(dialog.getDialogPane().getScene().getWindow());
            if (file != null) {
                selectedFile[0] = file;
                fileLabel.setText(file.getName());
            }
        });

        GridPane grid = new GridPane();
        grid.add(new Label("Titre:"), 0, 0);
        grid.add(titleField, 1, 0);
        grid.add(new Label("Contenu:"), 0, 1);
        grid.add(contentField, 1, 1);
        grid.add(new Label("Image:"), 0, 2);
        grid.add(fileButton, 1, 2);
        grid.add(fileLabel, 1, 3);
        dialog.getDialogPane().setContent(grid);

        dialog.getDialogPane().getButtonTypes().addAll(ButtonType.OK, ButtonType.CANCEL);

        dialog.setResultConverter(buttonType -> {
            if (buttonType == ButtonType.OK) {
                Post post = new Post();
                post.setUserId(SessionManager.getCurrentUserId());
                post.setTitre(titleField.getText());
                post.setContent(contentField.getText());
                post.setVisibility("public");
                if (selectedFile[0] != null) {
                    try {
                        Path uploadDir = Path.of("uploads");
                        if (!Files.exists(uploadDir)) {
                            Files.createDirectory(uploadDir);
                        }
                        Path targetPath = uploadDir.resolve(selectedFile[0].getName());
                        Files.copy(selectedFile[0].toPath(), targetPath, StandardCopyOption.REPLACE_EXISTING);
                        post.setFile(targetPath.toString());
                    } catch (IOException e) {
                        logger.error("Erreur lors de la copie du fichier", e);
                        showAlert("Erreur", "Impossible de charger l'image", e.getMessage(), Alert.AlertType.ERROR);
                        return null;
                    }
                }
                return post;
            }
            return null;
        });

        dialog.showAndWait().ifPresent(post -> {
            Task<Void> task = new Task<>() {
                @Override
                protected Void call() throws SQLException {
                    postService.createPost(post);
                    return null;
                }
            };

            loadingIndicator.setVisible(true);
            task.setOnSucceeded(event -> Platform.runLater(() -> {
                loadingIndicator.setVisible(false);
                showAlert("Succès", "Post créé", "Votre publication a été ajoutée.", Alert.AlertType.INFORMATION);
                loadFeed();
            }));
            task.setOnFailed(event -> Platform.runLater(() -> {
                loadingIndicator.setVisible(false);
                logger.error("Erreur lors de la création du post", task.getException());
                showAlert("Erreur", "Échec de la création", task.getException().getMessage(), Alert.AlertType.ERROR);
            }));
            new Thread(task).start();
        });
    }

    @FXML
    private void handleViewUserPosts() {
        Task<List<Post>> task = new Task<>() {
            @Override
            protected List<Post> call() throws SQLException {
                return postService.getUserPosts(SessionManager.getCurrentUserId());
            }
        };

        loadingIndicator.setVisible(true);
        task.setOnSucceeded(event -> {
            Platform.runLater(() -> {
                loadingIndicator.setVisible(false);
                feedContainer.getChildren().clear();
                List<Post> posts = task.getValue();
                if (posts.isEmpty()) {
                    Label emptyLabel = new Label("Aucune publication à afficher");
                    emptyLabel.getStyleClass().add("empty-label");
                    feedContainer.getChildren().add(emptyLabel);
                } else {
                    for (Post post : posts) {
                        try {
                            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/syncylinky/views/components/post-component.fxml"));
                            VBox postNode = loader.load();
                            PostController controller = loader.getController();
                            controller.initializeData(post, postService, commentService, reactionService, shareService);
                            feedContainer.getChildren().add(postNode);
                        } catch (IOException e) {
                            logger.error("Erreur lors du chargement du post {}", post.getId(), e);
                            showAlert("Erreur", "Impossible de charger le post", e.getMessage(), Alert.AlertType.ERROR);
                        }
                    }
                }
            });
        });

        task.setOnFailed(event -> {
            logger.error("Erreur lors du chargement des publications", task.getException());
            Platform.runLater(() -> {
                loadingIndicator.setVisible(false);
                showAlert("Erreur", "Impossible de charger les publications", task.getException().getMessage(), Alert.AlertType.ERROR);
            });
        });

        new Thread(task).start();
    }

    private void showAlert(String title, String header, String content, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(header);
        alert.setContentText(content);
        alert.getDialogPane().getStylesheets().add(getClass().getResource("/com/syncylinky/css/main.css").toExternalForm());
        alert.showAndWait();
    }
}