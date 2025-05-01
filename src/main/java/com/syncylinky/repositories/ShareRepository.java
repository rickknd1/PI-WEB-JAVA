package com.syncylinky.repositories;

import com.syncylinky.config.DatabaseConfig;
import com.syncylinky.models.Share;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.sql.*;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

public class ShareRepository {
    private static final Logger logger = LoggerFactory.getLogger(ShareRepository.class);

    public void sharePost(Share share) throws SQLException {
        String query = """
            INSERT INTO share (post_id, user_id, shared_from_id, create_at) 
            VALUES (?, ?, ?, ?)
            """;

        try (Connection conn = DatabaseConfig.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(query)) {
            pstmt.setInt(1, share.getPostId());
            pstmt.setObject(2, share.getUserId(), Types.INTEGER);
            pstmt.setObject(3, share.getSharedFromId(), Types.INTEGER);
            pstmt.setTimestamp(4, Timestamp.valueOf(LocalDateTime.now()));
            pstmt.executeUpdate();
        } catch (SQLException e) {
            logger.error("Error sharing post", e);
            throw e;
        }
    }

    public List<Share> findByPostId(int postId) throws SQLException {
        List<Share> shares = new ArrayList<>();
        String query = "SELECT * FROM share WHERE post_id = ?";

        try (Connection conn = DatabaseConfig.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(query)) {
            pstmt.setInt(1, postId);
            try (ResultSet rs = pstmt.executeQuery()) {
                while (rs.next()) {
                    shares.add(mapResultSetToShare(rs));
                }
            }
        } catch (SQLException e) {
            logger.error("Error fetching shares for post: {}", postId, e);
            throw e;
        }
        return shares;
    }

    public void unshare(int shareId, Integer userId) throws SQLException {
        String query = "DELETE FROM share WHERE id = ? AND user_id = ?";

        try (Connection conn = DatabaseConfig.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(query)) {
            pstmt.setInt(1, shareId);
            pstmt.setObject(2, userId, Types.INTEGER);
            pstmt.executeUpdate();
        } catch (SQLException e) {
            logger.error("Error unsharing share: {}", shareId, e);
            throw e;
        }
    }

    private Share mapResultSetToShare(ResultSet rs) throws SQLException {
        Share share = new Share();
        share.setId(rs.getInt("id"));
        share.setPostId(rs.getInt("post_id"));
        share.setUserId(rs.getObject("user_id", Integer.class));
        share.setSharedFromId(rs.getObject("shared_from_id", Integer.class));
        share.setCreateAt(rs.getTimestamp("create_at").toLocalDateTime());
        return share;
    }
}