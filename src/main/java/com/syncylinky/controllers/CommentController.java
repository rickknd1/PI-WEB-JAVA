package com.syncylinky.controllers;

import com.syncylinky.models.Comment;
import com.syncylinky.services.CommentService;
import com.syncylinky.utils.SessionManager;
import javafx.application.Platform;
import javafx.concurrent.Task;
import javafx.fxml.FXML;
import javafx.scene.control.Alert;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.TextInputDialog;

import java.sql.SQLException;

/**
 * Contrôleur pour le composant de commentaire.
 */
public class CommentController {
    @FXML private Label commentLabel;
    @FXML private Button editButton;
    @FXML private Button deleteButton;

    private Comment comment;
    private CommentService commentService;
    private Runnable onUpdate;

    public void initializeData(Comment comment, CommentService commentService, Runnable onUpdate) {
        this.comment = comment;
        this.commentService = commentService;
        this.onUpdate = onUpdate;
        commentLabel.setText(comment.getContent());
        if (comment.getUserId() != null && comment.getUserId().equals(SessionManager.getCurrentUserId())) {
            editButton.setVisible(true);
            deleteButton.setVisible(true);
        }
    }

    @FXML
    private void handleEdit() {
        TextInputDialog dialog = new TextInputDialog(comment.getContent());
        dialog.setTitle("Modifier le commentaire");
        dialog.setHeaderText("Modifiez votre commentaire");

        dialog.showAndWait().ifPresent(newContent -> {
            Task<Void> task = new Task<>() {
                @Override
                protected Void call() throws SQLException {
                    comment.setContent(newContent);
                    commentService.updateComment(comment);
                    return null;
                }
            };

            task.setOnSucceeded(event -> Platform.runLater(() -> {
                commentLabel.setText(newContent);
                showAlert("Succès", "Commentaire modifié", "Votre commentaire a été mis à jour.", Alert.AlertType.INFORMATION);
                onUpdate.run();
            }));
            task.setOnFailed(event -> Platform.runLater(() -> showAlert("Erreur", "Impossible de modifier le commentaire", task.getException().getMessage(), Alert.AlertType.ERROR)));
            new Thread(task).start();
        });
    }

    @FXML
    private void handleDelete() {
        Task<Void> task = new Task<>() {
            @Override
            protected Void call() throws SQLException {
                commentService.deleteComment(comment.getId(), SessionManager.getCurrentUserId());
                return null;
            }
        };

        task.setOnSucceeded(event -> Platform.runLater(() -> {
            showAlert("Succès", "Commentaire supprimé", "Le commentaire a été supprimé.", Alert.AlertType.INFORMATION);
            onUpdate.run();
        }));
        task.setOnFailed(event -> Platform.runLater(() -> showAlert("Erreur", "Impossible de supprimer le commentaire", task.getException().getMessage(), Alert.AlertType.ERROR)));
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