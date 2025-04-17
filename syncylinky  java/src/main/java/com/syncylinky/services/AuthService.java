package com.syncylinky.services;

import com.syncylinky.dao.UserDAO;
import com.syncylinky.models.User;
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
        if ("ROLE_USER".equals(user.getRole())) {
            return "Accès restreint aux administrateurs uniquement.";
        }

        return null; // null signifie aucune erreur
    }
}