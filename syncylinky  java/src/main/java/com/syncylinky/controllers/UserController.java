package com.syncylinky.controllers;

import com.syncylinky.models.User;
import com.syncylinky.services.UserService;
import javafx.beans.property.SimpleStringProperty;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.HBox;
import javafx.scene.layout.StackPane;
import javafx.stage.Stage;
import javafx.util.Callback;

import java.io.IOException;
import java.net.URL;
import java.time.LocalDate;
import java.util.Collections;
import java.util.List;
import javafx.stage.FileChooser;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;

public class UserController {
    @FXML private TableView<User> usersTable;
    @FXML private TableColumn<User, String> statusColumn;
    @FXML private TableColumn<User, Void> actionsColumn;
    @FXML private StackPane contentPane;

    private final UserService userService = new UserService();
    private final ObservableList<User> users = FXCollections.observableArrayList();
    private final Button banBtn = new Button();

    @FXML
    public void initialize() {
        setupTableColumns();
        loadUsers();
    }


    private void setupTableColumns() {
        // Colonne Status
        statusColumn.setCellValueFactory(cellData -> {
            User user = cellData.getValue();
            return new SimpleStringProperty(user.isBanned() ? "Banni" : "Actif");
        });

        // Colonne Actions
        actionsColumn.setCellFactory(param -> new TableCell<>() {
            private final Button editBtn = new Button("Edit");
            private final Button deleteBtn = new Button("Delete");
            private final Button banBtn = new Button();

            {
                // Style de base
                editBtn.getStyleClass().add("button");
                deleteBtn.getStyleClass().add("button");
                deleteBtn.setStyle("-fx-background-color: #dc3545; -fx-text-fill: white;");
                banBtn.getStyleClass().add("button");

                // Actions
                editBtn.setOnAction(event -> handleEditUser(getTableView().getItems().get(getIndex())));
                deleteBtn.setOnAction(event -> handleDeleteUser(getTableView().getItems().get(getIndex())));
                banBtn.setOnAction(event -> handleToggleBan(getTableView().getItems().get(getIndex())));
            }

            @Override
            protected void updateItem(Void item, boolean empty) {
                super.updateItem(item, empty);
                if (empty) {
                    setGraphic(null);
                } else {
                    User user = getTableView().getItems().get(getIndex());
                    if (user.isBanned()) {
                        banBtn.setText("Unban");
                        banBtn.setStyle("-fx-background-color: #28a745; -fx-text-fill: white;");
                    } else {
                        banBtn.setText("Ban");
                        banBtn.setStyle("-fx-background-color: #dc3545; -fx-text-fill: white;");
                    }

                    HBox buttons = new HBox(5, editBtn, deleteBtn, banBtn);
                    buttons.setAlignment(Pos.CENTER);
                    setGraphic(buttons);
                }
            }
        });
    }

    private void loadUsers() {
        users.setAll(userService.getAllUsers());
        usersTable.setItems(users);
    }




    private void loadForm(User user) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/syncylinky/views/user/form.fxml"));

            // Créez explicitement le contrôleur
            UserFormController formController = new UserFormController(this, user);
            loader.setController(formController);

            Parent formView = loader.load();
            // Appliquer le CSS
            Scene scene = new Scene(formView);
            scene.getStylesheets().add(getClass().getResource("/com/syncylinky/css/styles.css").toExternalForm());

            // Configurez manuellement les actions des boutons
            Button cancelButton = (Button) formView.lookup(".button:textEquals('Cancel')");
            Button saveButton = (Button) formView.lookup(".button:textEquals('Save')");

            cancelButton.setOnAction(e -> formController.handleCancel());
            saveButton.setOnAction(e -> formController.handleSave());

            Stage stage = new Stage();
            stage.setScene(new Scene(formView));
            stage.setTitle(user == null ? "Add New User" : "Edit User");
            stage.show();

        } catch (IOException e) {
            showAlert("Error", "Failed to load form: " + e.getMessage(), Alert.AlertType.ERROR);
            e.printStackTrace();
        }
    }

    private void loadView(String fxmlPath, Object controller) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            if (controller != null) {
                loader.setController(controller);
            }
            Parent view = loader.load();

            // Trouve la scène actuelle
            Scene currentScene = usersTable.getScene();
            if (currentScene != null) {
                // Si contentPane existe, l'utilise, sinon remplace la racine
                if (contentPane != null) {
                    contentPane.getChildren().setAll(view);
                } else {
                    currentScene.setRoot(view);
                }
            } else {
                // Crée une nouvelle scène si nécessaire
                Stage newStage = new Stage();
                newStage.setScene(new Scene(view));
                newStage.show();
            }
        } catch (IOException e) {
            showAlert("Error", "Failed to load view: " + e.getMessage(), Alert.AlertType.ERROR);
            e.printStackTrace();
        }
    }

    @FXML
    private void handleViewUser(User user) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/syncylinky/views/user/details.fxml"));
            loader.setController(new UserDetailsController(this, user));
            contentPane.getChildren().clear();
            contentPane.getChildren().add(loader.load());
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    @FXML
    public void handleEditUser(User user) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/syncylinky/views/user/form.fxml"));
            Parent formView = loader.load();

            UserFormController formController = loader.getController();
            formController.setParentController(this);
            formController.setUser(user); // Ajoutez cette méthode dans UserFormController

            Stage stage = new Stage();
            stage.setScene(new Scene(formView));
            stage.setTitle("Edit User");
            stage.show();
        } catch (IOException e) {
            showAlert("Error", "Failed to load edit form:\n" + e.getMessage(), Alert.AlertType.ERROR);
            e.printStackTrace();
        }
    }

    @FXML
    public void handleAddUser() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/syncylinky/views/user/form.fxml"));
            Parent formView = loader.load();

            Stage stage = new Stage();
            stage.setScene(new Scene(formView));
            stage.setTitle("Add New User");
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
            showAlert("Error", "Failed to load form", Alert.AlertType.ERROR);
        }
    }

    @FXML
    private void handleDeleteUser(User user) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirm Deletion");
        alert.setHeaderText("Delete User");
        alert.setContentText("Are you sure you want to delete this user?");

        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                if (userService.deleteUser(user.getId())) {
                    users.remove(user);
                    showAlert("Success", "User deleted successfully", Alert.AlertType.INFORMATION);
                } else {
                    showAlert("Error", "Failed to delete user", Alert.AlertType.ERROR);
                }
            }
        });
    }
    public boolean deleteUser(User user) {
        return userService.deleteUser(user.getId());
    }

    public void refreshUsers() {
        loadUsers();
    }

    public void showAlert(String title, String message, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    @FXML
    public void handleExportUsers() {
        try {
            // Create a file chooser
            javafx.stage.FileChooser fileChooser = new javafx.stage.FileChooser();
            fileChooser.setTitle("Export Users");
            fileChooser.getExtensionFilters().addAll(
                    new javafx.stage.FileChooser.ExtensionFilter("CSV Files", "*.csv"),
                    new javafx.stage.FileChooser.ExtensionFilter("All Files", "*.*")
            );

            // Show save file dialog
            java.io.File file = fileChooser.showSaveDialog(usersTable.getScene().getWindow());

            if (file != null) {
                // Write to CSV
                try (java.io.FileWriter writer = new java.io.FileWriter(file)) {
                    // Write header
                    writer.write("ID,Email,Name,Role,Status\n");

                    // Write data
                    for (User user : users) {
                        String status = user.isBanned() ? "Banned" : "Active";
                        writer.write(String.format("%d,%s,%s,%s,%s\n",
                                user.getId(),
                                escapeCSV(user.getEmail()),
                                escapeCSV(user.getName()),
                                escapeCSV(user.getRole()),
                                status));
                    }

                    showAlert("Success", "Users exported successfully to " + file.getName(),
                            Alert.AlertType.INFORMATION);
                }
            }
        } catch (Exception e) {
            showAlert("Error", "Failed to export users: " + e.getMessage(),
                    Alert.AlertType.ERROR);
            e.printStackTrace();
        }
    }

    // Helper method to properly escape CSV values
    private String escapeCSV(String value) {
        if (value == null) {
            return "";
        }
        // If value contains comma, quote, or newline, wrap in quotes and escape internal quotes
        if (value.contains(",") || value.contains("\"") || value.contains("\n")) {
            return "\"" + value.replace("\"", "\"\"") + "\"";
        }
        return value;
    }

    @FXML
    private void handleToggleBan(User user) {
        boolean newBanStatus = !user.isBanned();
        user.setBanned(newBanStatus);

        if (userService.updateUser(user)) {
            // Solution optimale: rafraîchir seulement la ligne modifiée
            int index = usersTable.getItems().indexOf(user);
            if (index >= 0) {
                usersTable.getItems().set(index, user); // Force la mise à jour
                usersTable.refresh(); // Rafraîchit la table
            }

            String message = newBanStatus
                    ? "L'utilisateur a été banni avec succès."
                    : "L'utilisateur a été débanni avec succès.";
            showAlert("Succès", message, Alert.AlertType.INFORMATION);
        } else {
            showAlert("Erreur", "Échec de la mise à jour du statut de l'utilisateur.", Alert.AlertType.ERROR);
            user.setBanned(!newBanStatus); // Annule le changement
        }
    }


}