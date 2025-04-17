package com.syncylinky.utils;

import com.syncylinky.models.User;

/**
 * Gestionnaire de session singleton pour stocker l'utilisateur connecté
 * et d'autres informations de session dans l'application
 */
public class SessionManager {
    private static SessionManager instance;

    private User currentUser;
    private String authToken;

    // Constructeur privé pour empêcher l'instanciation directe
    private SessionManager() {}

    // Méthode pour obtenir l'instance singleton
    public static synchronized SessionManager getInstance() {
        if (instance == null) {
            instance = new SessionManager();
        }
        return instance;
    }

    // Méthodes pour la gestion de l'utilisateur connecté
    public User getCurrentUser() {
        return currentUser;
    }

    public void setCurrentUser(User user) {
        this.currentUser = user;
    }

    public boolean isLoggedIn() {
        return currentUser != null;
    }

    public boolean hasRole(String role) {
        return isLoggedIn() && currentUser.getRole() != null &&
                currentUser.getRole().equalsIgnoreCase(role);
    }

    public boolean isAdmin() {
        return hasRole("ADMIN");
    }

    // Méthodes pour la gestion du token d'authentification
    public String getAuthToken() {
        return authToken;
    }

    public void setAuthToken(String token) {
        this.authToken = token;
    }

    // Efface toutes les données de session
    public void clearSession() {
        currentUser = null;
        authToken = null;
    }
}