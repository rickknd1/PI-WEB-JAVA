package com.syncylinky.controllers;

import com.syncylinky.models.Post;
import com.syncylinky.services.*;
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
import java.util.ArrayList;
import java.util.List;

public class MainController {
    private static final Logger logger = LoggerFactory.getLogger(MainController.class);

    @FXML private VBox feedContainer;
    @FXML private VBox recommendedContainer;
    @FXML private ProgressIndicator loadingIndicator;
    @FXML private TextField searchField;
    @FXML private Button searchButton;
    @FXML private Button recommendedButton;
    @FXML private Button backButton;
    @FXML private Button notificationButton;
    @FXML private Label notificationCountLabel;

    private final PostService postService;
    private final CommentService commentService;
    private final ReactionService reactionService;
    private final ShareService shareService;

    // Historique des états du feed
    private final List<FeedState> feedHistory = new ArrayList<>();
    private boolean isNavigatingBack = false;

    // Classe interne pour représenter un état du feed
    private static class FeedState {
        enum Type { MAIN_FEED, SEARCH, USER_POSTS }
        final Type type;
        final String keyword; // null pour MAIN_FEED et USER_POSTS

        FeedState(Type type, String keyword) {
            this.type = type;
            this.keyword = keyword;
        }
    }

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
        // Désactiver le bouton Retour au démarrage
        backButton.setDisable(true);
        loadFeed();
        loadRecommendedPosts();
    }

    @FXML
    private void handleSearch() {
        String keyword = searchField.getText().trim();
        if (!isNavigatingBack) {
            feedHistory.add(new FeedState(FeedState.Type.SEARCH, keyword));
            backButton.setDisable(feedHistory.size() <= 1);
        }
        loadFeed(keyword);
    }

    @FXML
    private void handleShowRecommended() {
        loadRecommendedPosts();
    }

    @FXML
    private void handleViewUserPosts() {
        if (!isNavigatingBack) {
            feedHistory.add(new FeedState(FeedState.Type.USER_POSTS, null));
            backButton.setDisable(feedHistory.size() <= 1);
        }
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

    @FXML
    private void handleBack() {
        if (feedHistory.size() <= 1) {
            return; // Aucun état précédent
        }
        // Supprimer l'état actuel
        feedHistory.remove(feedHistory.size() - 1);
        // Récupérer l'état précédent
        FeedState previousState = feedHistory.get(feedHistory.size() - 1);
        isNavigatingBack = true;
        try {
            switch (previousState.type) {
                case MAIN_FEED:
                    loadFeed();
                    break;
                case SEARCH:
                    searchField.setText(previousState.keyword);
                    loadFeed(previousState.keyword);
                    break;
                case USER_POSTS:
                    handleViewUserPosts();
                    break;
            }
        } finally {
            isNavigatingBack = false;
        }
        backButton.setDisable(feedHistory.size() <= 1);
    }

    private void loadFeed() {
        loadFeed(null);
    }

    private void loadFeed(String keyword) {
        if (!isNavigatingBack && keyword == null) {
            feedHistory.add(new FeedState(FeedState.Type.MAIN_FEED, null));
            backButton.setDisable(feedHistory.size() <= 1);
        }
        Task<List<Post>> task = new Task<>() {
            @Override
            protected List<Post> call() throws SQLException {
                int userId = SessionManager.getCurrentUserId();
                return keyword == null || keyword.isEmpty() ? postService.getVisiblePosts(userId) : postService.searchPosts(keyword, userId);
            }
        };

        loadingIndicator.setVisible(true);
        task.setOnSucceeded(event -> {
            Platform.runLater(() -> {
                loadingIndicator.setVisible(false);
                feedContainer.getChildren().clear();
                List<Post> posts = task.getValue();
                if (posts.isEmpty()) {
                    Label emptyLabel = new Label(keyword == null ? "Aucune publication à afficher" : "Aucun post trouvé pour '" + keyword + "'");
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

    private void loadRecommendedPosts() {
        Task<List<Post>> task = new Task<>() {
            @Override
            protected List<Post> call() throws SQLException {
                return postService.getRecommendedPosts(SessionManager.getCurrentUserId());
            }
        };

        loadingIndicator.setVisible(true);
        task.setOnSucceeded(event -> {
            Platform.runLater(() -> {
                loadingIndicator.setVisible(false);
                recommendedContainer.getChildren().clear();
                List<Post> posts = task.getValue();
                if (posts.isEmpty()) {
                    Label emptyLabel = new Label("Aucune publication recommandée");
                    emptyLabel.getStyleClass().add("empty-label");
                    recommendedContainer.getChildren().add(emptyLabel);
                } else {
                    Label headerLabel = new Label("Publications recommandées");
                    headerLabel.getStyleClass().add("recommendation-header");
                    recommendedContainer.getChildren().add(headerLabel);
                    for (Post post : posts) {
                        try {
                            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/syncylinky/views/components/post-component.fxml"));
                            VBox postNode = loader.load();
                            PostController controller = loader.getController();
                            controller.initializeData(post, postService, commentService, reactionService, shareService);
                            recommendedContainer.getChildren().add(postNode);
                        } catch (IOException e) {
                            logger.error("Erreur lors du chargement du post recommandé {}", post.getId(), e);
                            showAlert("Erreur", "Impossible de charger le post recommandé", e.getMessage(), Alert.AlertType.ERROR);
                        }
                    }
                }
            });
        });

        task.setOnFailed(event -> {
            logger.error("Erreur lors du chargement des posts recommandés", task.getException());
            Platform.runLater(() -> {
                loadingIndicator.setVisible(false);
                showAlert("Erreur", "Impossible de charger les posts recommandés", task.getException().getMessage(), Alert.AlertType.ERROR);
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
        ChoiceBox<String> visibilityChoice = new ChoiceBox<>();
        visibilityChoice.getItems().addAll("public", "private");
        visibilityChoice.setValue("public");
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
        grid.add(new Label("Visibilité:"), 0, 2);
        grid.add(visibilityChoice, 1, 2);
        grid.add(new Label("Image:"), 0, 3);
        grid.add(fileButton, 1, 3);
        grid.add(fileLabel, 1, 4);
        dialog.getDialogPane().setContent(grid);

        dialog.getDialogPane().getButtonTypes().addAll(ButtonType.OK, ButtonType.CANCEL);

        dialog.setResultConverter(buttonType -> {
            if (buttonType == ButtonType.OK) {
                String content = contentField.getText().trim();
                if (content.isEmpty()) {
                    showAlert("Erreur", "Contenu vide", "Le contenu du post ne peut pas être vide.", Alert.AlertType.WARNING);
                    return null;
                }
                Post post = new Post();
                post.setUserId(SessionManager.getCurrentUserId());
                post.setTitre(titleField.getText());
                post.setContent(content);
                post.setVisibility(visibilityChoice.getValue());
                post.setCreatedAt(java.time.LocalDateTime.now());
                post.setUpdateAt(java.time.LocalDateTime.now());
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
                loadRecommendedPosts();
            }));
            task.setOnFailed(event -> Platform.runLater(() -> {
                loadingIndicator.setVisible(false);
                logger.error("Erreur lors de la création du post", task.getException());
                String errorMessage = task.getException() instanceof IllegalArgumentException
                        ? task.getException().getMessage()
                        : "Échec de la création : " + task.getException().getMessage();
                showAlert("Erreur", "Échec de la création", errorMessage, Alert.AlertType.ERROR);
            }));
            new Thread(task).start();
        });
    }

    private void showAlert(String title, String header, String content, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(header);
        alert.setContentText(content);
        alert.getDialogPane().getStylesheets().add(getClass().getResource("/com/syncylinky/views/main.css").toExternalForm());
        alert.showAndWait();
    }
}