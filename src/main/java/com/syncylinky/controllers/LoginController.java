package com.syncylinky.controllers;

import com.syncylinky.models.User;
import com.syncylinky.services.AuthService;
import com.syncylinky.services.FacialRecognitionService;
import com.syncylinky.utils.SessionManager;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Pane;
import javafx.stage.Stage;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.IOException;
import java.net.URL;
import java.util.concurrent.CompletableFuture;

public class LoginController {
    private static final Logger logger = LoggerFactory.getLogger(LoginController.class);

    @FXML private TextField emailField;
    @FXML private PasswordField passwordField;
    @FXML private TextField visiblePasswordField;
    @FXML private Button togglePasswordBtn;
    @FXML private Button faceRecognitionBtn;  // Nouveau bouton pour la reconnaissance faciale

    private boolean passwordVisible = false;
    private final AuthService authService = new AuthService();
    private final FacialRecognitionService facialRecognitionService = new FacialRecognitionService();

    @FXML
    public void initialize() {
        // Code d'initialisation existant
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
        // Code existant inchangé
        passwordVisible = !passwordVisible;

        if (passwordVisible) {
            visiblePasswordField.setText(passwordField.getText());
            visiblePasswordField.setVisible(true);
            visiblePasswordField.setManaged(true);
            passwordField.setVisible(false);
            passwordField.setManaged(false);
            togglePasswordBtn.setText("🙈");
            visiblePasswordField.requestFocus();
        } else {
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
            if (authService.authenticate(email, password)) {
                User user = authService.getCurrentUser(email);
                processSuccessfulLogin(user);
            } else {
                showAlert("Échec de connexion", "Email ou mot de passe incorrect", Alert.AlertType.ERROR);
            }
        } catch (Exception e) {
            showAlert("Erreur", "Une erreur est survenue : " + e.getMessage(), Alert.AlertType.ERROR);
            e.printStackTrace();
        }
    }

    @FXML
    private void handleFaceLogin() {
        showAlert("Information", "Démarrage de la reconnaissance faciale...", Alert.AlertType.INFORMATION);

        // Utiliser CompletableFuture pour exécuter la reconnaissance faciale dans un thread séparé
        CompletableFuture.supplyAsync(() -> {
            try {
                return facialRecognitionService.recognizeFace();
            } catch (Exception e) {
                e.printStackTrace();
                return null;
            }
        }).thenAccept(user -> {
            // Revenir au thread JavaFX pour mettre à jour l'interface utilisateur
            Platform.runLater(() -> {
                if (user != null) {
                    processSuccessfulLogin(user);
                } else {
                    showAlert("Échec de la reconnaissance", "Impossible de vous reconnaître. Veuillez réessayer ou utiliser la connexion traditionnelle.", Alert.AlertType.ERROR);
                }
            });
        });
    }

    private void processSuccessfulLogin(User user) {
        try {
            String errorMessage = authService.validateLoginConditions(user);
            if (errorMessage != null) {
                logger.warn("Conditions de connexion non remplies pour l'email {} : {}", user.getEmail(), errorMessage);
                displayErrorInUI(errorMessage);
                return;
            }

            if (user.getRole() == null) {
                logger.error("Rôle de l'utilisateur non défini pour l'email {}", user.getEmail());
                showAlert("Erreur", "Rôle de l'utilisateur non défini", Alert.AlertType.ERROR);
                return;
            }

            SessionManager.getInstance().setCurrentUser(user);
            logger.info("Utilisateur {} connecté avec le rôle {}", user.getEmail(), user.getRole());

            FXMLLoader loader = new FXMLLoader();
            String fxmlPath;
            String title;

            if ("ROLE_USER".equals(user.getRole())) {
                fxmlPath = "/com/syncylinky/views/user/FrontOffice.fxml";
                title = "SyncYLinkY Front Office";
            } else {
                fxmlPath = "/com/syncylinky/views/admin/main.fxml";
                title = "SyncYLinkY Admin";
            }

            URL fxmlUrl = getClass().getResource(fxmlPath);
            if (fxmlUrl == null) {
                logger.error("Fichier FXML introuvable : {}", fxmlPath);
                throw new IOException("Impossible de trouver le fichier FXML à : " + fxmlPath);
            }
            loader.setLocation(fxmlUrl);
            Parent root = loader.load();

            Stage stage = (Stage) emailField.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle(title);
            stage.show();
            logger.debug("Interface chargée : {}", fxmlPath);
        } catch (IOException e) {
            logger.error("Erreur lors du chargement de l'interface pour l'utilisateur {}", user.getEmail(), e);
            showAlert("Erreur", "Erreur lors du chargement de l'interface : " + e.getMessage(), Alert.AlertType.ERROR);
        } catch (Exception e) {
            logger.error("Erreur inattendue lors de la connexion pour l'utilisateur {}", user.getEmail(), e);
            showAlert("Erreur", "Une erreur inattendue est survenue : " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }


    @FXML
    private void registerFace() {
        String email = emailField.getText();
        String password = passwordVisible ? visiblePasswordField.getText() : passwordField.getText();

        if (email.isEmpty() || password.isEmpty()) {
            showAlert("Erreur", "Veuillez remplir email et mot de passe pour enregistrer votre visage", Alert.AlertType.ERROR);
            return;
        }

        try {
            if (authService.authenticate(email, password)) {
                User user = authService.getCurrentUser(email);
                showAlert("Information", "Préparation de l'enregistrement du visage. Veuillez regarder la caméra.", Alert.AlertType.INFORMATION);

                CompletableFuture.supplyAsync(() -> {
                    try {
                        return facialRecognitionService.registerFace(user);
                    } catch (Exception e) {
                        e.printStackTrace();
                        return false;
                    }
                }).thenAccept(success -> {
                    Platform.runLater(() -> {
                        if (success) {
                            showAlert("Succès", "Votre visage a été enregistré avec succès. Vous pouvez maintenant vous connecter par reconnaissance faciale.", Alert.AlertType.INFORMATION);
                        } else {
                            showAlert("Erreur", "Échec de l'enregistrement du visage. Veuillez réessayer.", Alert.AlertType.ERROR);
                        }
                    });
                });
            } else {
                showAlert("Erreur", "Email ou mot de passe incorrect. Authentification requise pour enregistrer votre visage.", Alert.AlertType.ERROR);
            }
        } catch (Exception e) {
            showAlert("Erreur", "Une erreur est survenue : " + e.getMessage(), Alert.AlertType.ERROR);
            e.printStackTrace();
        }
    }

    private void displayErrorInUI(String errorMessage) {
        // Code existant inchangé
        HBox errorBox = new HBox();
        errorBox.setAlignment(javafx.geometry.Pos.CENTER);
        errorBox.setStyle("-fx-background-color: #1e2532; -fx-padding: 15px; -fx-background-radius: 5px;");
        errorBox.setPrefWidth(500);
        errorBox.setMaxWidth(Double.MAX_VALUE);

        Label errorLabel = new Label(errorMessage);
        errorLabel.setStyle("-fx-text-fill: white; -fx-font-weight: bold;");
        errorBox.getChildren().add(errorLabel);

        Pane parent = (Pane) emailField.getParent().getParent();
        parent.getChildren().removeIf(node ->
                node instanceof HBox &&
                        node.getStyle() != null &&
                        node.getStyle().contains("-fx-background-color: #1e2532")
        );

        parent.getChildren().add(2, errorBox);
    }

    private void showAlert(String title, String message, Alert.AlertType type) {
        // Code existant inchangé
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    @FXML
    private void handleRegisterLink() {
        // Code existant inchangé
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/syncylinky/views/auth/Register.fxml"));
            Parent root = loader.load();

            Scene scene = new Scene(root);
            Stage stage = new Stage();
            stage.setScene(scene);
            stage.setTitle("Register");
            stage.show();

            ((Stage) emailField.getScene().getWindow()).close();
        } catch (IOException e) {
            showAlert("Error", "Could not load registration page: " + e.getMessage(), Alert.AlertType.ERROR);
            e.printStackTrace();
        }
    }
}