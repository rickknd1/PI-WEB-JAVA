package com.syncylinky.utils;

import com.syncylinky.models.Comment;
import com.syncylinky.models.Post;

/**
 * Classe utilitaire pour valider les entrées utilisateur.
 */
public class Validator {

    /**
     * Valide une publication avant création ou modification.
     * @param post La publication à valider
     * @throws IllegalArgumentException Si la publication est invalide
     */
    public static void validatePost(Post post) {
        if (post.getContent() == null || post.getContent().trim().isEmpty()) {
            throw new IllegalArgumentException("Le contenu du post ne peut pas être vide.");
        }
        if (post.getTitre() != null && post.getTitre().length() > 255) {
            throw new IllegalArgumentException("Le titre ne peut pas dépasser 255 caractères.");
        }
        if (post.getVisibility() == null || !post.getVisibility().matches("public|private")) {
            throw new IllegalArgumentException("La visibilité doit être 'public' ou 'private'.");
        }
    }

    /**
     * Valide un commentaire avant création ou modification.
     * @param comment Le commentaire à valider
     * @throws IllegalArgumentException Si le commentaire est invalide
     */
    public static void validateComment(Comment comment) {
        if (comment.getContent() == null || comment.getContent().trim().isEmpty()) {
            throw new IllegalArgumentException("Le commentaire ne peut pas être vide.");
        }
        if (comment.getPostId() <= 0) {
            throw new IllegalArgumentException("L'ID du post doit être valide.");
        }
    }
}