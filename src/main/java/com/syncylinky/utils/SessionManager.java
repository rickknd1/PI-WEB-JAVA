package com.syncylinky.utils;

/**
 * Gestionnaire de session pour stocker les informations de l'utilisateur connecté.
 */
public class SessionManager {
    private static Integer currentUserId;

    /**
     * Définit l'ID de l'utilisateur connecté.
     * @param userId L'ID de l'utilisateur, peut être null pour un utilisateur anonyme
     */
    public static void setCurrentUserId(Integer userId) {
        currentUserId = userId;
    }

    /**
     * Récupère l'ID de l'utilisateur connecté.
     * @return L'ID de l'utilisateur, ou null si aucun utilisateur n'est connecté
     */
    public static Integer getCurrentUserId() {
        return currentUserId;
    }
}