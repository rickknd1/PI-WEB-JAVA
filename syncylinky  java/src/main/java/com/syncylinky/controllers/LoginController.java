package com.syncylinky.controllers;

import com.syncylinky.models.User;
import com.syncylinky.services.AuthService;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Pane;
import javafx.stage.Stage;

import java.io.IOException;

public class LoginController {
    @FXML private TextField emailField;
    @FXML private PasswordField passwordField;
    @FXML private TextField visiblePasswordField;
    @FXML private Button togglePasswordBtn;

    private boolean passwordVisible = false;
    private final AuthService authService = new AuthService();

    @FXML
    public void initialize() {
        // Synchronisation manuelle sans bind pour éviter les conflits
        passwordField.textProperty().addListener((obs, oldVal, newVal) -> {
            if (!passwordVisible) {
                visiblePasswordField.setText(newVal);
            }
        });

        visiblePasswordField.textProperty().addListener((obs, oldVal, newVal) -> {
            if (passwordVisible) {
                passwordField.setText(newVal);
            }
        });
    }

    @FXML
    private void togglePasswordVisibility() {
        passwordVisible = !passwordVisible;

        if (passwordVisible) {
            // Passe en mode visible
            visiblePasswordField.setText(passwordField.getText());
            visiblePasswordField.setVisible(true);
            visiblePasswordField.setManaged(true);
            passwordField.setVisible(false);
            passwordField.setManaged(false);
            togglePasswordBtn.setText("🙈");
            visiblePasswordField.requestFocus();
        } else {
            // Passe en mode masqué
            passwordField.setText(visiblePasswordField.getText());
            passwordField.setVisible(true);
            passwordField.setManaged(true);
            visiblePasswordField.setVisible(false);
            visiblePasswordField.setManaged(false);
            togglePasswordBtn.setText("👁️");
            passwordField.requestFocus();
        }
    }


    @FXML
    private void handleLogin() {
        String email = emailField.getText();
        String password = passwordVisible ? visiblePasswordField.getText() : passwordField.getText();

        if (email.isEmpty() || password.isEmpty()) {
            showAlert("Erreur", "Veuillez remplir tous les champs", Alert.AlertType.ERROR);
            return;
        }

        try {
            // Vérifie d'abord si l'authentification réussit
            if (authService.authenticate(email, password)) {
                User user = authService.getCurrentUser(email);
                String errorMessage = authService.validateLoginConditions(user);

                if (errorMessage != null) {
                    // Affiche le message d'erreur directement dans l'interface plutôt qu'une alerte
                    displayErrorInUI(errorMessage);
                    return; // Reste sur la page de connexion
                }

                try {
                    FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/syncylinky/views/main.fxml"));
                    Parent root = loader.load();

                    MainController mainController = loader.getController();
                    mainController.setCurrentUser(user);

                    Stage stage = (Stage) emailField.getScene().getWindow();
                    stage.setScene(new Scene(root));
                    stage.setTitle("SyncYLinkY Admin");
                } catch (IOException e) {
                    showAlert("Erreur", "Erreur lors du chargement de l'interface: " + e.getMessage(), Alert.AlertType.ERROR);
                    e.printStackTrace();
                }
            } else {
                showAlert("Échec de connexion", "Email ou mot de passe incorrect", Alert.AlertType.ERROR);
            }
        } catch (Exception e) {
            showAlert("Erreur", "Une erreur est survenue: " + e.getMessage(), Alert.AlertType.ERROR);
            e.printStackTrace();
        }
    }

    // Nouvelle méthode pour afficher l'erreur dans l'interface utilisateur
    private void displayErrorInUI(String errorMessage) {
        // Créer une nouvelle HBox pour le message d'erreur
        HBox errorBox = new HBox();
        errorBox.setAlignment(javafx.geometry.Pos.CENTER);
        errorBox.setStyle("-fx-background-color: #1e2532; -fx-padding: 15px; -fx-background-radius: 5px;");
        errorBox.setPrefWidth(500);
        errorBox.setMaxWidth(Double.MAX_VALUE);

        // Créer le label pour le message
        Label errorLabel = new Label(errorMessage);
        errorLabel.setStyle("-fx-text-fill: white; -fx-font-weight: bold;");
        errorBox.getChildren().add(errorLabel);

        // Récupérer le parent de notre formulaire de connexion
        Pane parent = (Pane) emailField.getParent().getParent();

        // Supprimer tout message d'erreur existant
        parent.getChildren().removeIf(node ->
                node instanceof HBox &&
                        node.getStyle() != null &&
                        node.getStyle().contains("-fx-background-color: #1e2532")
        );

        // Ajouter le nouveau message après le titre et avant le formulaire
        // L'index exact dépend de la structure de votre interface
        parent.getChildren().add(2, errorBox);
    }

    private void showAlert(String title, String message, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

}