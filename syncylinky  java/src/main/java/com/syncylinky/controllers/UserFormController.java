package com.syncylinky.controllers;

import com.syncylinky.models.Category;
import com.syncylinky.models.User;
import com.syncylinky.services.UserService;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.stage.Stage;

import java.io.IOException;
import java.time.LocalDate;
import java.util.List;

import static com.syncylinky.utils.AlertUtils.showAlert;

public class UserFormController extends UserController {
    @FXML private TextField emailField;
    @FXML private PasswordField passwordField;
    @FXML private TextField nameField;
    @FXML private TextField firstnameField;
    @FXML private TextField usernameField;
    @FXML private DatePicker dateOBPicker;
    @FXML private ComboBox<String> genderComboBox;
    @FXML private ComboBox<String> roleComboBox;
    @FXML private ListView<Category> interestsListView;
    @FXML private CheckBox bannedCheckBox;
    @FXML private CheckBox verifiedCheckBox;
    @FXML private Button saveButton;
    @FXML private Button cancelButton;

    private final UserService userService = new UserService();
    private User user;
    private UserController parentController;
    private ObservableList<Category> allCategories = FXCollections.observableArrayList();

    public UserFormController() {
        // Constructeur par défaut nécessaire pour l'initialisation par JavaFX
    }

    public UserFormController(UserController parentController) {
        this.parentController = parentController;
    }

    public UserFormController(UserController parentController, User user) {
        this.parentController = parentController;
        this.user = user;
    }

    public void setParentController(UserController parentController) {
        this.parentController = parentController;
    }

    public void setUser(User user) {
        this.user = user;
        if (user != null) {
            populateForm();
        }
    }

    @FXML
    public void handleEditUser(User user) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/syncylinky/views/user/form.fxml"));
            UserFormController formController = new UserFormController(this, user);
            loader.setController(formController);

            Parent formView = loader.load();

            Stage stage = new Stage();
            stage.setScene(new Scene(formView));
            stage.setTitle("Edit User");
            stage.show();
        } catch (IOException e) {
            showAlert("Error", "Failed to load edit form:\n" + e.getMessage(), Alert.AlertType.ERROR);
            e.printStackTrace();
        }
    }

    private void populateForm() {
        if (emailField != null) emailField.setText(user.getEmail());
        if (nameField != null) nameField.setText(user.getName());
        if (firstnameField != null) firstnameField.setText(user.getFirstname());
        if (usernameField != null) usernameField.setText(user.getUsername());
        if (dateOBPicker != null) dateOBPicker.setValue(user.getDateOB());
        if (genderComboBox != null) genderComboBox.setValue(user.getGender());
        if (roleComboBox != null) roleComboBox.setValue(user.getRole());
        if (bannedCheckBox != null) bannedCheckBox.setSelected(user.isBanned());
        if (verifiedCheckBox != null) verifiedCheckBox.setSelected(user.isVerified());

        // Ensure categories are loaded before selecting user's interests
        loadCategories();

        // Select user's interests if both the user interests and list view are available
        if (user.getInterests() != null && interestsListView != null) {
            for (Category interest : user.getInterests()) {
                for (Category category : allCategories) {
                    if (category.getId() == interest.getId()) {
                        interestsListView.getSelectionModel().select(category);
                    }
                }
            }
        }
    }

    @FXML
    public void handleSave() {
        try {
            // Initialize user if null
            if (user == null) {
                user = new User();
            }

            if (passwordField.getText() == null || passwordField.getText().isEmpty()) {
                showAlert("Erreur", "Le mot de passe est obligatoire", Alert.AlertType.ERROR);
                return;
            }

            // Récupération des valeurs du formulaire
            user.setEmail(emailField.getText());
            user.setRole(roleComboBox.getValue());
            user.setPassword(passwordField.getText());
            user.setName(nameField.getText());
            user.setFirstname(firstnameField.getText());
            user.setUsername(usernameField.getText());

            // Add the date of birth
            user.setDateOB(dateOBPicker.getValue());

            // Récupération du genre
            user.setGender(genderComboBox.getValue());

            // Get selected interests from ListView
            List<Category> selectedInterests = interestsListView.getSelectionModel().getSelectedItems();
            user.setInterests(selectedInterests);

            // Enregistrement - determine if we're creating or updating
            boolean success;
            if (user.getId() == 0) { // Assuming 0 or null ID means new user
                success = userService.addUser(user);
            } else {
                success = userService.updateUser(user);
            }

            // Fermer la fenêtre et rafraîchir les utilisateurs en cas de succès
            if (success) {
                if (parentController != null) {
                    parentController.refreshUsers();
                }
                // Fermer la fenêtre
                Stage stage = (Stage) saveButton.getScene().getWindow();
                stage.close();
            } else {
                showAlert("Erreur", "Échec de l'enregistrement", Alert.AlertType.ERROR);
            }

        } catch (Exception e) {
            showAlert("Erreur", "Erreur lors de l'enregistrement: " + e.getMessage(), Alert.AlertType.ERROR);
            e.printStackTrace();
        }
    }



    @FXML
    public void handleCancel() {
        // Close the form window
        Stage stage = (Stage) cancelButton.getScene().getWindow();
        stage.close();

        if (parentController != null) {
            parentController.refreshUsers();
        }
    }

    @FXML
    public void initialize() {
        System.out.println("Initializing UserFormController...");

        // Set up button actions
        if (saveButton != null) {
            saveButton.setOnAction(event -> handleSave());
        }

        if (cancelButton != null) {
            cancelButton.setOnAction(event -> handleCancel());
        }

        // Initialize ComboBox for Gender
        System.out.println("Setting up gender ComboBox...");
        ObservableList<String> genderOptions = FXCollections.observableArrayList(
                "Homme", "Femme", "Autre"
        );
        genderComboBox.setItems(genderOptions);

        // Initialize ComboBox for Role
        System.out.println("Setting up role ComboBox...");
        ObservableList<String> roleOptions = FXCollections.observableArrayList(
                "ROLE_USER", "ROLE_ADMIN", "ROLE_SUPER_ADMIN"
        );
        roleComboBox.setItems(roleOptions);

        // Setup ListView for interests/categories
        System.out.println("Setting up interests ListView...");
        setupInterestsListView();

        // Load form data if editing existing user
        if (user != null) {
            System.out.println("Populating form with existing user data...");
            populateForm();
        }
    }

    private void setupInterestsListView() {
        interestsListView.getSelectionModel().setSelectionMode(SelectionMode.MULTIPLE);

        // CellFactory for display
        interestsListView.setCellFactory(lv -> new ListCell<Category>() {
            @Override
            protected void updateItem(Category item, boolean empty) {
                super.updateItem(item, empty);
                setText(empty || item == null ? null : item.getNom());
            }
        });

        // Load category data
        loadCategories();
    }

    private void loadCategories() {
        try {
            System.out.println("Loading categories from service...");
            List<Category> categories = userService.getAllCategories();

            // Debug: Afficher le contenu des catégories chargées
            System.out.println("Categories loaded (" + categories.size() + "):");
            for (Category cat : categories) {
                System.out.println(" - ID: " + cat.getId() +
                        ", Nom: " + cat.getNom() +
                        ", Description: " + cat.getDescription());
            }

            if (categories != null && !categories.isEmpty()) {
                allCategories.setAll(categories);
                interestsListView.setItems(allCategories);

                // Debug: Vérifier ce qui est effectivement dans la ListView
                System.out.println("ListView items count: " + interestsListView.getItems().size());
            } else {
                System.out.println("No categories found");
            }
        } catch (Exception e) {
            System.err.println("Error loading categories: " + e.getMessage());
            e.printStackTrace();
        }
    }
}