package com.syncylinky.services;

import com.syncylinky.models.Share;
import com.syncylinky.repositories.ShareRepository;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.sql.SQLException;
import java.util.List;

public class ShareService {
    private static final Logger logger = LoggerFactory.getLogger(ShareService.class);
    private final ShareRepository shareRepository;

    public ShareService() {
        this.shareRepository = new ShareRepository();
    }

    public void sharePost(int postId, Integer userId, Integer sharedFromId) throws SQLException {
        try {
            Share share = new Share();
            share.setPostId(postId);
            share.setUserId(userId);
            share.setSharedFromId(sharedFromId);
            shareRepository.sharePost(share);
        } catch (SQLException e) {
            logger.error("Database error during post sharing: {}", postId, e);
            throw e;
        }
    }

    public List<Share> getSharesForPost(int postId) throws SQLException {
        try {
            return shareRepository.findByPostId(postId);
        } catch (SQLException e) {
            logger.error("Database error during fetching shares for post: {}", postId, e);
            throw e;
        }
    }

    public void unshare(int shareId, Integer userId) throws SQLException {
        try {
            shareRepository.unshare(shareId, userId);
        } catch (SQLException e) {
            logger.error("Database error during unsharing: {}", shareId, e);
            throw e;
        }
    }
}