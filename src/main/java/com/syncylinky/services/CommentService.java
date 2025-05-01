package com.syncylinky.services;

import com.syncylinky.models.Comment;
import com.syncylinky.repositories.CommentRepository;
import com.syncylinky.utils.Validator;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.sql.SQLException;
import java.util.List;

public class CommentService {
    private static final Logger logger = LoggerFactory.getLogger(CommentService.class);
    private final CommentRepository commentRepository;

    public CommentService() {
        this.commentRepository = new CommentRepository();
    }

    public void createComment(int postId, Integer userId, String content) throws SQLException {
        try {
            Comment comment = new Comment();
            comment.setPostId(postId);
            comment.setUserId(userId);
            comment.setContent(content);
            Validator.validateComment(comment);
            commentRepository.create(comment);
        } catch (IllegalArgumentException e) {
            logger.error("Validation failed for comment creation: {}", e.getMessage());
            throw e;
        } catch (SQLException e) {
            logger.error("Database error during comment creation", e);
            throw e;
        }
    }

    public List<Comment> getCommentsForPost(int postId) throws SQLException {
        try {
            return commentRepository.findByPostId(postId);
        } catch (SQLException e) {
            logger.error("Database error during fetching comments for post: {}", postId, e);
            throw e;
        }
    }

    public void updateComment(Comment comment) throws SQLException {
        try {
            Validator.validateComment(comment);
            commentRepository.update(comment);
        } catch (IllegalArgumentException e) {
            logger.error("Validation failed for comment update: {}", e.getMessage());
            throw e;
        } catch (SQLException e) {
            logger.error("Database error during comment update", e);
            throw e;
        }
    }

    public void deleteComment(int commentId, Integer userId) throws SQLException {
        try {
            commentRepository.delete(commentId, userId);
        } catch (SQLException e) {
            logger.error("Database error during comment deletion: {}", commentId, e);
            throw e;
        }
    }
}