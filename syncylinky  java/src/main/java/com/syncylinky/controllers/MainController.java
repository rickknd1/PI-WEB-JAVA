package com.syncylinky.controllers;

import com.syncylinky.models.User;
import com.syncylinky.utils.AlertUtils;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.control.Alert;
import javafx.scene.layout.StackPane;

import java.awt.event.ActionEvent;
import java.io.IOException;

public class MainController {
    @FXML
    private StackPane contentPane;
    private User currentUser;

    public void setCurrentUser(User user) {
        this.currentUser = user;
        // Vous pouvez ajouter des vérifications de rôle ici
        if ("ROLE_USER".equals(user.getRole())) {
            showAlert("Accès refusé", "Vous n'avez pas les droits nécessaires", Alert.AlertType.ERROR);
            // Fermer l'application ou revenir à la page de login
        }
    }

    @FXML
    private void handleUsersMenuClick() {
        try {
            // Correction du chemin avec le slash initial
            Parent usersView = FXMLLoader.load(getClass().getResource("/com/syncylinky/views/user/list.fxml"));
            contentPane.getChildren().clear();
            contentPane.getChildren().add(usersView);
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Error", "Cannot load users view: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    private void showAlert(String title, String message, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }





}