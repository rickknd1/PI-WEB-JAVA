package com.syncylinky.controllers;

import com.syncylinky.models.Comment;
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
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;
import javafx.scene.text.Text;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.IOException;
import java.net.URL;
import java.sql.SQLException;
import java.time.format.DateTimeFormatter;
import java.util.List;

public class PostController {
    private static final Logger logger = LoggerFactory.getLogger(PostController.class);

    @FXML private Label authorLabel;
    @FXML private Label timestampLabel;
    @FXML private Text contentLabel;
    @FXML private ImageView postImageView;
    @FXML private VBox commentsContainer;
    @FXML private ImageView reactionIcon;
    @FXML private Label likeCountLabel;
    @FXML private Label commentCountLabel;
    @FXML private Label shareCountLabel;
    @FXML private Button likeButton;
    @FXML private Button commentButton;
    @FXML private Button shareButton;
    @FXML private Button menuButton;

    private Post post;
    private PostService postService;
    private CommentService commentService;
    private ReactionService reactionService;
    private ShareService shareService;

    public void initializeData(Post post, PostService postService, CommentService commentService,
                               ReactionService reactionService, ShareService shareService) {
        this.post = post;
        this.postService = postService;
        this.commentService = commentService;
        this.reactionService = reactionService;
        this.shareService = shareService;
        updateUI();
        loadComments();
        loadReactions();
    }

    private void updateUI() {
        authorLabel.setText("Utilisateur #" + (post.getUserId() != null ? post.getUserId() : "Anonyme"));
        timestampLabel.setText(post.getCreatedAt().format(DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm")));
        contentLabel.setText(post.getTitre() + "\n" + post.getContent());

        if (post.getFile() != null && !post.getFile().isEmpty()) {
            try {
                postImageView.setImage(new Image("file:" + post.getFile()));
                postImageView.setVisible(true);
            } catch (Exception e) {
                logger.error("Erreur lors du chargement de l'image pour le post {}", post.getId(), e);
            }
        }

        shareCountLabel.setText("0 Partages");
    }

    @FXML
    private void handleLike() {
        Task<Void> task = new Task<>() {
            @Override
            protected Void call() throws SQLException {
                reactionService.toggleReaction(post.getId(), SessionManager.getCurrentUserId(), "like");
                return null;
            }
        };

        task.setOnSucceeded(event -> Platform.runLater(this::loadReactions));
        task.setOnFailed(event -> Platform.runLater(() -> {
            logger.error("Erreur lors de l'enregistrement de la réaction", task.getException());
            showAlert("Erreur", "Impossible d'enregistrer la réaction", task.getException().getMessage(), Alert.AlertType.ERROR);
        }));
        new Thread(task).start();
    }

    @FXML
    private void handleShare() {
        if (SessionManager.getCurrentUserId() == null) {
            showAlert("Erreur", "Connexion requise", "Vous devez être connecté pour partager un post.", Alert.AlertType.WARNING);
            return;
        }

        Task<Void> task = new Task<>() {
            @Override
            protected Void call() throws SQLException {
                shareService.sharePost(post.getId(), SessionManager.getCurrentUserId(), null);
                return null;
            }
        };

        task.setOnSucceeded(event -> Platform.runLater(() -> {
            showAlert("Succès", "Post partagé", "Le post a été partagé avec succès.", Alert.AlertType.INFORMATION);
            loadReactions();
        }));
        task.setOnFailed(event -> Platform.runLater(() -> {
            logger.error("Erreur lors du partage du post {}", post.getId(), task.getException());
            showAlert("Erreur", "Impossible de partager le post", task.getException().getMessage(), Alert.AlertType.ERROR);
        }));
        new Thread(task).start();
    }

    @FXML
    private void showCommentDialog() {
        TextInputDialog dialog = new TextInputDialog();
        dialog.setTitle("Nouveau Commentaire");
        dialog.setHeaderText("Ajouter un commentaire");
        dialog.setContentText("Votre commentaire:");

        dialog.showAndWait().ifPresent(commentText -> {
            Task<Void> task = new Task<>() {
                @Override
                protected Void call() throws SQLException {
                    commentService.createComment(post.getId(), SessionManager.getCurrentUserId(), commentText);
                    return null;
                }
            };

            task.setOnSucceeded(event -> Platform.runLater(() -> {
                showAlert("Succès", "Commentaire ajouté", "Votre commentaire a été publié.", Alert.AlertType.INFORMATION);
                loadComments();
            }));
            task.setOnFailed(event -> Platform.runLater(() -> {
                logger.error("Erreur lors de l'ajout du commentaire", task.getException());
                String errorMessage = task.getException() instanceof IllegalArgumentException
                        ? task.getException().getMessage()
                        : "Une erreur est survenue lors de l'ajout du commentaire.";
                showAlert("Erreur", "Impossible d'ajouter le commentaire", errorMessage, Alert.AlertType.ERROR);
            }));
            new Thread(task).start();
        });
    }

    private void loadComments() {
        Task<List<Comment>> task = new Task<>() {
            @Override
            protected List<Comment> call() throws SQLException {
                return commentService.getCommentsForPost(post.getId());
            }
        };

        task.setOnSucceeded(event -> {
            Platform.runLater(() -> {
                commentsContainer.getChildren().clear();
                List<Comment> comments = task.getValue();
                for (Comment comment : comments) {
                    try {
                        FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/syncylinky/views/components/comment-component.fxml"));
                        HBox commentNode = loader.load();
                        CommentController controller = loader.getController();
                        controller.initializeData(comment, commentService, this::loadComments);
                        commentsContainer.getChildren().add(commentNode);
                    } catch (IOException e) {
                        logger.error("Erreur lors du chargement du commentaire {}", comment.getId(), e);
                        showAlert("Erreur", "Impossible de charger le commentaire", e.getMessage(), Alert.AlertType.ERROR);
                    }
                }
            });
        });

        task.setOnFailed(event -> {
            logger.error("Erreur lors du chargement des commentaires", task.getException());
            Platform.runLater(() -> showAlert("Erreur", "Impossible de charger les commentaires", task.getException().getMessage(), Alert.AlertType.ERROR));
        });

        new Thread(task).start();
    }

    private void loadReactions() {
        Task<ReactionData> task = new Task<>() {
            @Override
            protected ReactionData call() throws SQLException {
                int likeCount = reactionService.getReactionCount(post.getId(), "like");
                boolean userLiked = reactionService.hasUserReacted(post.getId(), SessionManager.getCurrentUserId() != null ? SessionManager.getCurrentUserId() : 0);
                int commentCount = commentService.getCommentCount(post.getId());
                int shareCount = shareService.getShareCount(post.getId());
                return new ReactionData(likeCount, userLiked, commentCount, shareCount);
            }
        };

        task.setOnSucceeded(event -> {
            Platform.runLater(() -> {
                ReactionData data = task.getValue();
                likeCountLabel.setText(data.likeCount + " J'aime");
                commentCountLabel.setText(data.commentCount + " Commentaires");
                shareCountLabel.setText(data.shareCount + " Partages");
                likeButton.setStyle(data.userLiked ? "-fx-text-fill: #1877f2;" : "");
                updateReactionIcon(data.likeCount > 0);
            });
        });

        task.setOnFailed(event -> {
            logger.error("Erreur lors du chargement des réactions", task.getException());
            Platform.runLater(() -> showAlert("Erreur", "Impossible de charger les réactions", task.getException().getMessage(), Alert.AlertType.ERROR));
        });

        new Thread(task).start();
    }

    @FXML
    private void showPostOptionsMenu() {
        ContextMenu menu = new ContextMenu();
        if (post.getUserId() != null && post.getUserId().equals(SessionManager.getCurrentUserId())) {
            MenuItem editItem = new MenuItem("Modifier");
            editItem.setOnAction(e -> editPost());

            MenuItem deleteItem = new MenuItem("Supprimer");
            deleteItem.setOnAction(e -> deletePost());

            menu.getItems().addAll(editItem, deleteItem);
        }

        MenuItem viewItem = new MenuItem("Afficher");
        viewItem.setOnAction(e -> showAlert("Info", "Détails du post", "Titre: " + post.getTitre() + "\nContenu: " + post.getContent(), Alert.AlertType.INFORMATION));

        menu.getItems().add(viewItem);
        menu.show(menuButton, javafx.geometry.Side.BOTTOM, 0, 0);
    }

    private void editPost() {
        TextInputDialog dialog = new TextInputDialog(post.getContent());
        dialog.setTitle("Modifier le post");
        dialog.setHeaderText("Modifiez votre publication");

        dialog.showAndWait().ifPresent(newContent -> {
            Task<Void> task = new Task<>() {
                @Override
                protected Void call() throws SQLException {
                    post.setContent(newContent);
                    postService.updatePost(post);
                    return null;
                }
            };

            task.setOnSucceeded(event -> Platform.runLater(() -> {
                showAlert("Succès", "Post modifié", "Votre publication a été mise à jour.", Alert.AlertType.INFORMATION);
                updateUI();
            }));
            task.setOnFailed(event -> Platform.runLater(() -> {
                logger.error("Erreur lors de la modification du post", task.getException());
                String errorMessage = task.getException() instanceof IllegalArgumentException
                        ? task.getException().getMessage()
                        : "Une erreur est survenue lors de la modification du post.";
                showAlert("Erreur", "Impossible de modifier le post", errorMessage, Alert.AlertType.ERROR);
            }));
            new Thread(task).start();
        });
    }

    private void deletePost() {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation");
        alert.setHeaderText("Supprimer ce post ?");
        alert.setContentText("Cette action est irréversible.");

        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                Task<Void> task = new Task<>() {
                    @Override
                    protected Void call() throws SQLException {
                        postService.deletePost(post.getId(), SessionManager.getCurrentUserId() != null ? SessionManager.getCurrentUserId() : 0);
                        return null;
                    }
                };

                task.setOnSucceeded(event -> Platform.runLater(() -> {
                    showAlert("Succès", "Post supprimé", "La publication a été supprimée.", Alert.AlertType.INFORMATION);
                    VBox parent = (VBox) authorLabel.getParent().getParent();
                    parent.getChildren().remove(authorLabel.getParent());
                }));
                task.setOnFailed(event -> Platform.runLater(() -> {
                    logger.error("Erreur lors de la suppression du post", task.getException());
                    showAlert("Erreur", "Impossible de supprimer le post", task.getException().getMessage(), Alert.AlertType.ERROR);
                }));
                new Thread(task).start();
            }
        });
    }

    private void updateReactionIcon(boolean hasLikes) {
        if (reactionIcon != null) {
            String iconPath = hasLikes ? "/com/syncylinky/images/like-filled.png" : "/com/syncylinky/images/like-empty.png";
            try {
                reactionIcon.setImage(new Image(getClass().getResourceAsStream(iconPath)));
            } catch (Exception e) {
                logger.error("Erreur lors du chargement de l'icône de réaction", e);
            }
        }
    }

    private void showAlert(String title, String header, String content, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(header);
        alert.setContentText(content);
        URL cssUrl = getClass().getResource("/com/syncylinky/views/main.css");
        if (cssUrl != null) {
            alert.getDialogPane().getStylesheets().add(cssUrl.toExternalForm());
        } else {
            logger.warn("Fichier main.css introuvable dans /com/syncylinky/views/");
        }
        alert.showAndWait();
    }

    private static class ReactionData {
        final int likeCount;
        final boolean userLiked;
        final int commentCount;
        final int shareCount;

        ReactionData(int likeCount, boolean userLiked, int commentCount, int shareCount) {
            this.likeCount = likeCount;
            this.userLiked = userLiked;
            this.commentCount = commentCount;
            this.shareCount = shareCount;
        }
    }
}