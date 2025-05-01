package com.syncylinky.services;

import com.syncylinky.models.Post;
import com.syncylinky.repositories.PostRepository;
import com.syncylinky.utils.SessionManager;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.sql.SQLException;
import java.util.List;

public class NewsFeedService {
    private static final Logger logger = LoggerFactory.getLogger(NewsFeedService.class);
    private final PostRepository postRepository;

    public NewsFeedService() {
        this.postRepository = new PostRepository();
    }

    public List<Post> getNewsFeed() throws SQLException {
        try {
            return postRepository.findAllVisible(SessionManager.getCurrentUserId());
        } catch (SQLException e) {
            logger.error("Erreur lors du chargement du flux de nouvelles", e);
            throw e;
        }
    }
}