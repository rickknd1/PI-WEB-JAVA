package com.syncylinky.repositories;

import com.syncylinky.config.DatabaseConfig;
import com.syncylinky.models.Category;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

public class CategoryDAO {
    private Connection getConnection() throws SQLException {
        // Implémentation pour obtenir la connexion à la base de données
        // À adapter selon votre configuration (ex. DataSource)
        return DatabaseConfig.getConnection();
    }

    public List<Category> getAllCategories() {
        List<Category> categories = new ArrayList<>();
        String sql = "SELECT id, nom, description, cover, date_creation FROM categories";

        try (Connection conn = getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {
            while (rs.next()) {
                Category category = new Category();
                category.setId(rs.getInt("id"));
                category.setNom(rs.getString("nom"));
                category.setDescription(rs.getString("description"));
                category.setCover(rs.getString("cover"));
                category.setDateCreation(rs.getTimestamp("date_creation").toLocalDateTime());
                categories.add(category);
            }
        } catch (SQLException e) {
            throw new RuntimeException("Erreur lors de la récupération des catégories", e);
        }
        return categories;
    }

    // Autres méthodes (ajout, mise à jour, suppression) à implémenter si nécessaire
}