package com.syncylinky.controllers;

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

    @FXML
    private void handleUsersMenuClick(ActionEvent event) {
        try {
            System.out.println("Attempting to load users view...");
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




    public void handleUsersMenuClick(javafx.event.ActionEvent actionEvent) {
        {
            try {
                System.out.println("Loading users view..."); // Debug
                Parent usersView = FXMLLoader.load(getClass().getResource("/com/syncylinky/views/user/list.fxml"));
                contentPane.getChildren().clear();
                contentPane.getChildren().add(usersView);
            } catch (IOException e) {
                System.err.println("Error loading users view:");
                e.printStackTrace();
            }
        }
    }
}