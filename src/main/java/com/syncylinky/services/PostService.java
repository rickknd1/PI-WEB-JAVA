package com.syncylinky.services;

import com.syncylinky.models.Post;
import com.syncylinky.repositories.PostRepository;
import com.syncylinky.utils.Validator;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.sql.SQLException;
import java.util.List;

public class PostService {
    private static final Logger logger = LoggerFactory.getLogger(PostService.class);
    private final PostRepository postRepository;

    public PostService() {
        this.postRepository = new PostRepository();
    }

    public void createPost(Post post) throws SQLException {
        try {
            Validator.validatePost(post);
            postRepository.create(post);
        } catch (IllegalArgumentException e) {
            logger.error("Validation failed for post creation: {}", e.getMessage());
            throw e;
        } catch (SQLException e) {
            logger.error("Database error during post creation", e);
            throw e;
        }
    }

    public List<Post> getVisiblePosts(Integer currentUserId) throws SQLException {
        try {
            return postRepository.findAllVisible(currentUserId);
        } catch (SQLException e) {
            logger.error("Database error during fetching visible posts", e);
            throw e;
        }
    }

    public Post getPostById(int postId) throws SQLException {
        try {
            return postRepository.findById(postId);
        } catch (SQLException e) {
            logger.error("Database error during fetching post by ID: {}", postId, e);
            throw e;
        }
    }

    public void updatePost(Post post) throws SQLException {
        try {
            Validator.validatePost(post);
            postRepository.update(post);
        } catch (IllegalArgumentException e) {
            logger.error("Validation failed for post update: {}", e.getMessage());
            throw e;
        } catch (SQLException e) {
            logger.error("Database error during post update", e);
            throw e;
        }
    }

    public void deletePost(int postId, Integer userId) throws SQLException {
        try {
            postRepository.delete(postId, userId);
        } catch (SQLException e) {
            logger.error("Database error during post deletion: {}", postId, e);
            throw e;
        }
    }

    public List<Post> getUserPosts(Integer userId) throws SQLException {
        try {
            return postRepository.findByUserId(userId);
        } catch (SQLException e) {
            logger.error("Database error during fetching user posts: {}", userId, e);
            throw e;
        }
    }
}