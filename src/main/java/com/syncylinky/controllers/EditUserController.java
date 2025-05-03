package com.syncylinky.controllers;

import com.syncylinky.models.User;
import com.syncylinky.services.UserService;
import com.syncylinky.utils.SessionManager;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;
import javafx.stage.Stage;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.IOException;

public class EditUserController {
    private static final Logger logger = LoggerFactory.getLogger(EditUserController.class);

    @FXML private TextField firstNameField;
    @FXML private TextField emailField;
    @FXML private PasswordField currentPasswordField;
    @FXML private PasswordField newPasswordField;

    private User user;
    private UserService userService;
    private FeedController parentController;

    public EditUserController(FeedController parentController, User user) {
        this.parentController = parentController;
        this.user = user;
        this.userService = new UserService();
    }

    @FXML
    public void initialize() {
        try {
            logger.info("Initialisation de EditUserController pour userId={}", user != null ? user.getId() : null);
            if (user != null) {
                firstNameField.setText(user.getFirstname() != null ? user.getFirstname() : "");
                emailField.setText(user.getEmail() != null ? user.getEmail() : "");
            }
            logger.debug("Champs initialisés avec succès");
        } catch (Exception e) {
            logger.error("Erreur lors de l'initialisation de EditUserController: {}: {}", e.getClass().getName(), e.getMessage(), e);
            showAlert("Erreur", "Échec de l'initialisation", Alert.AlertType.ERROR);
        }
    }

    @FXML
    private void handleSave() {
        try {
            String firstName = firstNameField.getText().trim();
            String email = emailField.getText().trim();
            String currentPassword = currentPasswordField.getText();
            String newPassword = newPasswordField.getText();

            if (firstName.isEmpty() || email.isEmpty() || currentPassword.isEmpty()) {
                logger.warn("Champs requis manquants : firstName={}, email={}, currentPassword={}",
                        firstName.isEmpty() ? "vide" : "rempli",
                        email.isEmpty() ? "vide" : "rempli",
                        currentPassword.isEmpty() ? "vide" : "rempli");
                showAlert("Erreur", "Le prénom, l'email et le mot de passe actuel sont requis", Alert.AlertType.ERROR);
                return;
            }

            // Vérifier le mot de passe actuel
            if (!userService.verifyCurrentPassword(user.getId(), currentPassword)) {
                logger.warn("Mot de passe actuel incorrect pour userId={}", user.getId());
                showAlert("Erreur", "Le mot de passe actuel est incorrect", Alert.AlertType.ERROR);
                return;
            }

            // Mettre à jour les informations de base
            User updatedUser = new User();
            updatedUser.setId(user.getId());
            updatedUser.setFirstname(firstName);
            updatedUser.setEmail(email);
            updatedUser.setActive(user.isActive());
            updatedUser.setUsername(user.getUsername());
            updatedUser.setRole(user.getRole());
            userService.updateUser(updatedUser);
            logger.info("Informations de l'utilisateur mises à jour : userId={}, email={}", user.getId(), email);

            // Si un nouveau mot de passe est fourni, le changer
            if (!newPassword.isEmpty()) {
                boolean passwordChanged = userService.changePassword(user.getId(), currentPassword, newPassword);
                if (!passwordChanged) {
                    logger.error("Échec du changement de mot de passe pour userId={}", user.getId());
                    showAlert("Erreur", "Échec du changement de mot de passe", Alert.AlertType.ERROR);
                    return;
                }
                updatedUser.setPassword(newPassword);
                logger.info("Mot de passe changé avec succès pour userId={}", user.getId());
            }

            // Mettre à jour la session
            SessionManager.getInstance().updateUser(updatedUser);
            logger.debug("Session mise à jour pour userId={}", user.getId());

            // Notifier le parent (FeedController) pour mettre à jour l'affichage
            if (parentController != null) {
                parentController.updateUserProfile(updatedUser);
                logger.debug("FeedController notifié pour mise à jour du profil");
            }

            // Fermer la fenêtre
            Stage stage = (Stage) firstNameField.getScene().getWindow();
            stage.close();
            logger.info("Fenêtre d'édition fermée pour userId={}", user.getId());
        } catch (Exception e) {
            logger.error("Erreur lors de la sauvegarde des modifications pour userId={}: {}: {}", user.getId(), e.getClass().getName(), e.getMessage(), e);
            showAlert("Erreur", "Échec de la sauvegarde", Alert.AlertType.ERROR);
        }
    }

    @FXML
    private void handleCancel() {
        try {
            logger.debug("Annulation de l'édition pour userId={}", user != null ? user.getId() : null);
            Stage stage = (Stage) firstNameField.getScene().getWindow();
            stage.close();
            logger.info("Fenêtre d'édition fermée (annulation)");
        } catch (Exception e) {
            logger.error("Erreur lors de l'annulation: {}: {}", e.getClass().getName(), e.getMessage(), e);
            showAlert("Erreur", "Échec de la fermeture", Alert.AlertType.ERROR);
        }
    }

    @FXML
    private void handleDeactivate() {
        try {
            logger.debug("Demande de désactivation du compte pour userId={}", user.getId());
            boolean deactivated = userService.deactivateAccount(user.getId());
            if (deactivated) {
                logger.info("Compte désactivé avec succès pour userId={}", user.getId());
                showAlert("Succès", "Compte désactivé. Vous allez être déconnecté.", Alert.AlertType.INFORMATION);
                SessionManager.getInstance().logout();
                try {
                    FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/syncylinky/views/auth/Login.fxml"));
                    Parent root = loader.load();

                    Stage stage = (Stage) firstNameField.getScene().getWindow();
                    stage.setScene(new Scene(root));
                    stage.setTitle("Login");
                    logger.debug("Page de connexion chargée après désactivation");
                } catch (IOException e) {
                    logger.error("Erreur lors du chargement de la page de connexion: {}: {}", e.getClass().getName(), e.getMessage(), e);
                    showAlert("Erreur", "Impossible de charger la page de connexion", Alert.AlertType.ERROR);
                }
            } else {
                logger.error("Échec de la désactivation du compte pour userId={}", user.getId());
                showAlert("Erreur", "Échec de la désactivation du compte", Alert.AlertType.ERROR);
            }
        } catch (Exception e) {
            logger.error("Erreur lors de la désactivation du compte pour userId={}: {}: {}", user.getId(), e.getClass().getName(), e.getMessage(), e);
            showAlert("Erreur", "Échec de la désactivation", Alert.AlertType.ERROR);
        }
    }

    private void showAlert(String title, String message, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        try {
            alert.getDialogPane().getStylesheets().add(getClass().getResource("/com/syncylinky/views/main.css").toExternalForm());
            logger.debug("Style main.css appliqué à l'alerte");
        } catch (Exception e) {
            logger.warn("Impossible de charger main.css pour l'alerte: {}: {}", e.getClass().getName(), e.getMessage(), e);
        }
        alert.showAndWait();
    }
}