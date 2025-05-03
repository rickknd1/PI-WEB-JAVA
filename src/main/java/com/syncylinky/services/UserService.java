package com.syncylinky.services;


import com.syncylinky.models.Category;
import com.syncylinky.models.User;
import com.syncylinky.repositories.CategoryDAO;
import com.syncylinky.repositories.UserDAO;

import java.util.List;

public class UserService {
    private final UserDAO userDAO;

    public UserService() {
        this.userDAO = new UserDAO();
    }

    public List<Category> getAllCategories() {
        return new CategoryDAO().getAllCategories();
    }
    public List<User> getAllUsers() {
        return userDAO.getAllUsers();
    }

    public User getUserById(int id) {
        return userDAO.getUserById(id);
    }


    public boolean addUser(User user) {
        return userDAO.addUser(user);
    }

    public boolean updateUser(User user) {
        return userDAO.updateUser(user);
    }

    public boolean deleteUser(int id) {
        return userDAO.deleteUser(id);
    }

    // Méthodes à ajouter à UserService.java
    public boolean changePassword(int userId, String currentPassword, String newPassword) {
        // Vérifier d'abord si le mot de passe actuel est correct
        User user = userDAO.getUserById(userId);
        if (user == null) {
            return false;
        }

        // Dans un environnement réel, vous devriez vérifier le mot de passe hashé
        // Ceci est simplifié pour l'exemple
        if (!user.getPassword().equals(currentPassword)) {
            return false;
        }

        // Mettre à jour le mot de passe
        user.setPassword(newPassword); // En production, hasher le mot de passe ici
        return userDAO.updateUser(user);
    }

    public boolean deactivateAccount(int userId) {
        User user = userDAO.getUserById(userId);
        if (user == null) {
            return false;
        }

        user.setActive(false);
        return userDAO.updateUser(user);
    }

    public boolean verifyCurrentPassword(int userId, String password) {
        User user = userDAO.getUserById(userId);
        if (user == null) {
            return false;
        }

        // Dans un environnement réel, vous devriez comparer les mots de passe hashés
        return user.getPassword().equals(password);
    }




}