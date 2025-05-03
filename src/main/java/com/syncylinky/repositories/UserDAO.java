package com.syncylinky.repositories;

import com.syncylinky.config.DatabaseConfig;
import com.syncylinky.models.Category;
import com.syncylinky.models.User;
import com.syncylinky.utils.PasswordUtils;

import java.sql.*;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.List;

public class UserDAO {
    public List<User> getAllUsers() {
        List<User> users = new ArrayList<>();
        String sql = "SELECT * FROM user";

        try (Connection conn = DatabaseConfig.getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {

            while (rs.next()) {
                User user = new User(
                        rs.getInt("id"),
                        rs.getString("email"),
                        rs.getString("role"),
                        rs.getString("password"),
                        rs.getString("name"),
                        rs.getString("firstname"),
                        rs.getString("username"),
                        rs.getDate("date_ob").toLocalDate(),
                        rs.getString("gender"),
                        rs.getBoolean("banned"),
                        rs.getBoolean("is_verified")
                );

                // Load user interests
                loadUserInterests(conn, user);

                users.add(user);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }

        return users;
    }

    private void loadUserInterests(Connection conn, User user) throws SQLException {
        String sql = "SELECT c.id, c.nom, c.description, c.cover " +
                "FROM user_categories uc " +
                "JOIN categories c ON uc.categories_id = c.id " +
                "WHERE uc.user_id = ?";

        try (PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setInt(1, user.getId());
            ResultSet rs = pstmt.executeQuery();

            while (rs.next()) {
                Category category = new Category(
                        rs.getInt("id"),
                        rs.getString("nom"),
                        rs.getString("description"),
                        rs.getString("cover")
                );
                user.addInterest(category);
            }
        }
    }

    public User getUserById(int id) {
        String sql = "SELECT * FROM user WHERE id = ?";
        User user = null;

        try (Connection conn = DatabaseConfig.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setInt(1, id);
            ResultSet rs = pstmt.executeQuery();

            if (rs.next()) {
                user = new User(
                        rs.getInt("id"),
                        rs.getString("email"),
                        rs.getString("role"),
                        rs.getString("password"),
                        rs.getString("name"),
                        rs.getString("firstname"),
                        rs.getString("username"),
                        rs.getDate("date_ob").toLocalDate(),
                        rs.getString("gender"),
                        rs.getBoolean("banned"),
                        rs.getBoolean("is_verified")
                );

                loadUserInterests(conn, user);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }

        return user;
    }

    public boolean addUser(User user) {
        Connection conn = null;
        try {
            conn = DatabaseConfig.getConnection();
            conn.setAutoCommit(false); // Démarrer une transaction

            // 1. D'abord insérer l'utilisateur
            String sql = "INSERT INTO user (email, role, password, name, firstname, username, date_ob, gender, banned, is_verified, is_google_authenticator_enabled, is_active) " +
                    "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            try (PreparedStatement pstmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
                pstmt.setString(1, user.getEmail());
                pstmt.setString(2, user.getRole());
                pstmt.setString(3, PasswordUtils.hashPassword(user.getPassword()));
                pstmt.setString(4, user.getName());
                pstmt.setString(5, user.getFirstname());
                pstmt.setString(6, user.getUsername());
                pstmt.setDate(7, Date.valueOf(user.getDateOB()));
                pstmt.setString(8, user.getGender());
                pstmt.setBoolean(9, user.isBanned());
                pstmt.setBoolean(10, user.isVerified());
                pstmt.setBoolean(11, user.isGoogleAuthenticatorEnabled());
                pstmt.setBoolean(12, true);

                int affectedRows = pstmt.executeUpdate();

                if (affectedRows == 0) {
                    conn.rollback();
                    return false;
                }

                // Récupérer l'ID généré
                try (ResultSet generatedKeys = pstmt.getGeneratedKeys()) {
                    if (generatedKeys.next()) {
                        user.setId(generatedKeys.getInt(1));
                    } else {
                        conn.rollback();
                        return false;
                    }
                }
            }

            // 2. Ensuite insérer les catégories si elles existent
            if (user.getInterests() != null && !user.getInterests().isEmpty()) {
                // Vérifier d'abord que toutes les catégories existent
                for (Category category : user.getInterests()) {
                    if (!categoryExists(conn, category.getId())) {
                        conn.rollback();
                        return false;
                    }
                }

                // Insérer les associations
                saveUserInterests(conn, user);
            }

            conn.commit();
            return true;
        } catch (SQLException e) {
            if (conn != null) {
                try {
                    conn.rollback();
                } catch (SQLException ex) {
                    ex.printStackTrace();
                }
            }
            System.err.println("Erreur lors de l'ajout de l'utilisateur: " + e.getMessage());
            e.printStackTrace();
            return false;
        } catch (Exception e) {
            if (conn != null) {
                try {
                    conn.rollback();
                } catch (SQLException ex) {
                    ex.printStackTrace();
                }
            }
            System.err.println("Erreur inattendue: " + e.getMessage());
            e.printStackTrace();
            return false;
        } finally {
            if (conn != null) {
                try {
                    conn.setAutoCommit(true);
                    conn.close();
                } catch (SQLException e) {
                    e.printStackTrace();
                }
            }
        }
    }

    // Méthode pour vérifier si une catégorie existe
    private boolean categoryExists(Connection conn, int categoryId) throws SQLException {
        String sql = "SELECT id FROM categories WHERE id = ?";
        try (PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setInt(1, categoryId);
            try (ResultSet rs = pstmt.executeQuery()) {
                return rs.next();
            }
        }
    }
    public boolean updateUser(User user) {
        String sql = "UPDATE user SET email = ?, role = ?, password = ?, name = ?, firstname = ?, " +
                "username = ?, date_ob = ?, gender = ?, banned = ?, is_verified = ?, " +
                "is_google_authenticator_enabled = ?, is_active = ? WHERE id = ?";

        try (Connection conn = DatabaseConfig.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setString(1, user.getEmail());
            pstmt.setString(2, user.getRole());
            pstmt.setString(3, user.getPassword());
            pstmt.setString(4, user.getName());
            pstmt.setString(5, user.getFirstname());
            pstmt.setString(6, user.getUsername());
            pstmt.setDate(7, Date.valueOf(user.getDateOB()));  // Date de naissance
            pstmt.setString(8, user.getGender());             // Genre
            pstmt.setBoolean(9, user.isBanned());
            pstmt.setBoolean(10, user.isVerified());
            pstmt.setBoolean(11, user.isGoogleAuthenticatorEnabled());
            pstmt.setBoolean(12, user.isActive());
            pstmt.setInt(13, user.getId());

            int affectedRows = pstmt.executeUpdate();

            if (affectedRows > 0) {
                deleteUserInterests(conn, user.getId());
                saveUserInterests(conn, user);
                return true;
            }
        } catch (SQLException e) {
            System.err.println("Erreur lors de la mise à jour de l'utilisateur: " + e.getMessage());
            e.printStackTrace();
        }
        return false;
    }

    public boolean deleteUser(int id) {
        Connection conn = null;
        try {
            conn = DatabaseConfig.getConnection();
            conn.setAutoCommit(false); // Désactive l'autocommit

            // 1. D'abord supprimer les dépendances
            String deleteDependenciesSQL = "DELETE FROM membre_comunity WHERE id_user_id = ?";
            try (PreparedStatement pstmt = conn.prepareStatement(deleteDependenciesSQL)) {
                pstmt.setInt(1, id);
                pstmt.executeUpdate();
            }

            // 2. Ensuite supprimer l'utilisateur
            String deleteUserSQL = "DELETE FROM user WHERE id = ?";
            try (PreparedStatement pstmt = conn.prepareStatement(deleteUserSQL)) {
                pstmt.setInt(1, id);
                int affectedRows = pstmt.executeUpdate();
                conn.commit(); // Valide la transaction
                return affectedRows > 0;
            }
        } catch (SQLException e) {
            try {
                if (conn != null) conn.rollback(); // Annule en cas d'erreur
            } catch (SQLException ex) {
                ex.printStackTrace();
            }
            e.printStackTrace();
            return false;
        } finally {
            try {
                if (conn != null) conn.setAutoCommit(true);
            } catch (SQLException e) {
                e.printStackTrace();
            }
        }
    }

    private void saveUserInterests(Connection conn, User user) throws SQLException {
        String sql = "INSERT INTO user_categories (user_id, categories_id) VALUES (?, ?)";

        try (PreparedStatement pstmt = conn.prepareStatement(sql)) {
            for (Category category : user.getInterests()) {
                pstmt.setInt(1, user.getId());
                pstmt.setInt(2, category.getId());
                pstmt.addBatch();
            }
            pstmt.executeBatch();
        }
    }

    private void deleteUserInterests(Connection conn, int userId) throws SQLException {
        String sql = "DELETE FROM user_categories WHERE user_id = ?";

        try (PreparedStatement pstmt = conn.prepareStatement(sql)) {
            pstmt.setInt(1, userId);
            pstmt.executeUpdate();
        }
    }

    public User getUserByEmail(String email) {
        String sql = "SELECT * FROM user WHERE email = ?";
        User user = null;

        try (Connection conn = DatabaseConfig.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setString(1, email);
            ResultSet rs = pstmt.executeQuery();

            if (rs.next()) {
                user = new User(
                        rs.getInt("id"),
                        rs.getString("email"),
                        rs.getString("role"),
                        rs.getString("password"),
                        rs.getString("name"),
                        rs.getString("firstname"),
                        rs.getString("username"),
                        rs.getDate("date_ob").toLocalDate(),
                        rs.getString("gender"),
                        rs.getBoolean("banned"),
                        rs.getBoolean("is_verified")
                );

                loadUserInterests(conn, user);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }

        return user;
    }
}