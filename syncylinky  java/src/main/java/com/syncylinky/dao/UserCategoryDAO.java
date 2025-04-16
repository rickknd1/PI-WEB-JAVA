package com.syncylinky.dao;

import com.syncylinky.models.UserCategory;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class UserCategoryDAO {
    private final Connection connection;

    public UserCategoryDAO(Connection connection) {
        this.connection = connection;
    }

    public boolean addUserCategory(UserCategory userCategory) throws SQLException {
        String sql = "INSERT INTO user_categories (user_id, category_id) VALUES (?, ?)";

        try (PreparedStatement stmt = connection.prepareStatement(sql)) {
            stmt.setInt(1, userCategory.getUserId());
            stmt.setInt(2, userCategory.getCategoryId());
            return stmt.executeUpdate() > 0;
        }
    }

    public List<UserCategory> findByUserId(int userId) throws SQLException {
        List<UserCategory> results = new ArrayList<>();
        String sql = "SELECT * FROM user_categories WHERE user_id = ?";

        try (PreparedStatement stmt = connection.prepareStatement(sql)) {
            stmt.setInt(1, userId);
            ResultSet rs = stmt.executeQuery();

            while (rs.next()) {
                results.add(new UserCategory(
                        rs.getInt("user_id"),
                        rs.getInt("category_id")
                ));
            }
        }
        return results;
    }

    public boolean deleteByUser(int userId) throws SQLException {
        String sql = "DELETE FROM user_categories WHERE user_id = ?";
        try (PreparedStatement stmt = connection.prepareStatement(sql)) {
            stmt.setInt(1, userId);
            return stmt.executeUpdate() > 0;
        }
    }
}