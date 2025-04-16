package com.syncylinky.controllers;

import com.syncylinky.models.User;
import com.syncylinky.services.UserService;
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

public class UserController {
    @FXML private TableView<User> usersTable;
    @FXML private TableColumn<User, String> statusColumn;
    @FXML private TableColumn<User, Void> actionsColumn;
    @FXML private StackPane contentPane;

    private final UserService userService = new UserService();
    private final ObservableList<User> users = FXCollections.observableArrayList();

    @FXML
    public void initialize() {
        setupTableColumns();
        loadUsers();
    }


    private void setupTableColumns() {
        // Colonne Status personnalisée
        statusColumn.setCellFactory(column -> new TableCell<>() {
            @Override
            protected void updateItem(String item, boolean empty) {
                super.updateItem(item, empty);
                if (empty || getTableRow() == null || getTableRow().getItem() == null) {
                    setText(null);
                    setStyle("");
                } else {
                    User user = getTableRow().getItem();
                    setText(user.isBanned() ? "Banned" : "Active");
                    setStyle(user.isBanned() ? "-fx-text-fill: red;" : "-fx-text-fill: green;");
                }
            }
        });

        // Colonne Actions personnalisée
        actionsColumn.setCellFactory(new Callback<>() {
            @Override
            public TableCell<User, Void> call(final TableColumn<User, Void> param) {
                return new TableCell<>() {
                    private final Button editBtn = new Button("Edit");
                    private final Button deleteBtn = new Button("Delete");

                    {
                        // Style des boutons
                        editBtn.getStyleClass().add("button");
                        deleteBtn.getStyleClass().add("button");
                        deleteBtn.setStyle("-fx-background-color: #dc3545; -fx-text-fill: white;");

                        // Actions des boutons
                        editBtn.setOnAction(event -> {
                            User user = getTableView().getItems().get(getIndex());
                            handleEditUser(user);
                        });

                        deleteBtn.setOnAction(event -> {
                            User user = getTableView().getItems().get(getIndex());
                            handleDeleteUser(user);
                        });
                    }

                    @Override
                    protected void updateItem(Void item, boolean empty) {
                        super.updateItem(item, empty);
                        if (empty) {
                            setGraphic(null);
                        } else {
                            HBox buttons = new HBox(5, editBtn, deleteBtn);
                            buttons.setAlignment(Pos.CENTER);
                            setGraphic(buttons);
                        }
                    }
                };
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


}