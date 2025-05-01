package com.syncylinky.repositories;

import com.syncylinky.config.DatabaseConfig;
import com.syncylinky.models.Comment;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.sql.*;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

public class CommentRepository {
    private static final Logger logger = LoggerFactory.getLogger(CommentRepository.class);

    public void create(Comment comment) throws SQLException {
        String query = """
            INSERT INTO comment (post_id, user_id, content, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?)
            """;

        try (Connection conn = DatabaseConfig.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(query, Statement.RETURN_GENERATED_KEYS)) {
            pstmt.setInt(1, comment.getPostId());
            pstmt.setObject(2, comment.getUserId(), Types.INTEGER);
            pstmt.setString(3, comment.getContent());
            pstmt.setTimestamp(4, Timestamp.valueOf(LocalDateTime.now()));
            pstmt.setTimestamp(5, Timestamp.valueOf(LocalDateTime.now()));
            pstmt.executeUpdate();

            try (ResultSet rs = pstmt.getGeneratedKeys()) {
                if (rs.next()) {
                    comment.setId(rs.getInt(1));
                }
            }
        } catch (SQLException e) {
            logger.error("Error creating comment", e);
            throw e;
        }
    }

    public List<Comment> findByPostId(int postId) throws SQLException {
        List<Comment> comments = new ArrayList<>();
        String query = "SELECT * FROM comment WHERE post_id = ? ORDER BY created_at ASC";

        try (Connection conn = DatabaseConfig.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(query)) {
            pstmt.setInt(1, postId);
            try (ResultSet rs = pstmt.executeQuery()) {
                while (rs.next()) {
                    comments.add(mapResultSetToComment(rs));
                }
            }
        } catch (SQLException e) {
            logger.error("Error fetching comments for post: {}", postId, e);
            throw e;
        }
        return comments;
    }

    public void update(Comment comment) throws SQLException {
        String query = "UPDATE comment SET content = ?, updated_at = ? WHERE id = ? AND user_id = ?";

        try (Connection conn = DatabaseConfig.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(query)) {
            pstmt.setString(1, comment.getContent());
            pstmt.setTimestamp(2, Timestamp.valueOf(LocalDateTime.now()));
            pstmt.setInt(3, comment.getId());
            pstmt.setObject(4, comment.getUserId(), Types.INTEGER);
            pstmt.executeUpdate();
        } catch (SQLException e) {
            logger.error("Error updating comment: {}", comment.getId(), e);
            throw e;
        }
    }

    public void delete(int commentId, Integer userId) throws SQLException {
        String query = "DELETE FROM comment WHERE id = ? AND user_id = ?";

        try (Connection conn = DatabaseConfig.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(query)) {
            pstmt.setInt(1, commentId);
            pstmt.setObject(2, userId, Types.INTEGER);
            pstmt.executeUpdate();
        } catch (SQLException e) {
            logger.error("Error deleting comment: {}", commentId, e);
            throw e;
        }
    }

    private Comment mapResultSetToComment(ResultSet rs) throws SQLException {
        Comment comment = new Comment();
        comment.setId(rs.getInt("id"));
        comment.setPostId(rs.getInt("post_id"));
        comment.setUserId(rs.getObject("user_id", Integer.class));
        comment.setContent(rs.getString("content"));
        comment.setCreatedAt(rs.getTimestamp("created_at").toLocalDateTime());
        comment.setUpdatedAt(rs.getTimestamp("updated_at").toLocalDateTime());
        return comment;
    }
}