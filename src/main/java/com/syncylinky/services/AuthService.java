package com.syncylinky.services;

import com.syncylinky.models.User;
import com.syncylinky.repositories.UserDAO;
import com.syncylinky.utils.PasswordUtils;

public class AuthService {
    private final UserDAO userDAO = new UserDAO();

    public boolean authenticate(String email, String password) {
        User user = userDAO.getUserByEmail(email);
        return user != null && PasswordUtils.verifyPassword(password, user.getPassword());
    }

    public User getCurrentUser(String email) {
        return userDAO.getUserByEmail(email);
    }

    public String validateLoginConditions(User user) {
        if (user == null) {
            return "Identifiants invalides";
        }
        if (user.isBanned()) {
            return "Votre compte a été banni. Contactez un administrateur.";
        }
        if (!user.isActive()) {
            return "Votre compte est désactivé.";
        }
        // Retirer la restriction pour ROLE_USER
        return null; // null signifie aucune erreur
    }

    public boolean register(User user) {
        // Vérifier d'abord si l'email existe déjà
        if (userDAO.getUserByEmail(user.getEmail()) != null) {
            return false;
        }
        return userDAO.addUser(user);
    }

    public boolean emailExists(String email) {
        return userDAO.getUserByEmail(email) != null;
    }
}