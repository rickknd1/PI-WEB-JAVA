package com.syncylinky.utils;

import com.syncylinky.models.User;
import com.syncylinky.services.UserService;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

public class SessionManager {
    private static final Logger logger = LoggerFactory.getLogger(SessionManager.class);
    private static volatile SessionManager instance;
    private static User currentUser;
    private final UserService userService;

    private SessionManager() {
        userService = new UserService();
    }

    public static SessionManager getInstance() {
        if (instance == null) {
            synchronized (SessionManager.class) {
                if (instance == null) {
                    instance = new SessionManager();
                }
            }
        }
        return instance;
    }

    public synchronized void setCurrentUser(User user) {
        if (user == null) {
            logger.warn("Attempt to set null user in session");
        }
        this.currentUser = user;
    }

    public synchronized User getCurrentUser() {
        if (currentUser == null) {
            logger.warn("No user is currently logged in");
            throw new IllegalStateException("No user is currently logged in");
        }
        return currentUser;
    }

    public static synchronized int getCurrentUserId() {
        if (currentUser == null) {
            logger.warn("No user is currently logged in");
            throw new IllegalStateException("No user is currently logged in");
        }
        return currentUser.getId();
    }

    public synchronized void setCurrentUserId(int userId) {
        try {
            User user = userService.getUserById(userId);
            if (user == null) {
                logger.error("No user found with ID: {}", userId);
                throw new IllegalArgumentException("Invalid user ID: " + userId);
            }
            this.currentUser = user;
            logger.info("User with ID {} set in session", userId);
        } catch (Exception e) {
            logger.error("Error setting user by ID: {}", userId, e);
            throw new RuntimeException("Failed to set user by ID: " + e.getMessage(), e);
        }
    }

    public synchronized void updateUser(User updatedUser) {
        if (currentUser == null) {
            logger.warn("No user is currently logged in");
            throw new IllegalStateException("No user is currently logged in");
        }
        if (updatedUser == null || updatedUser.getId() != currentUser.getId()) {
            logger.error("Invalid user update attempt: {}", updatedUser);
            throw new IllegalArgumentException("Cannot update user: invalid user or ID mismatch");
        }
        try {
            userService.updateUser(updatedUser);
            this.currentUser = updatedUser;
            logger.info("User with ID {} updated in session", updatedUser.getId());
        } catch (Exception e) {
            logger.error("Error updating user with ID: {}", updatedUser.getId(), e);
            throw new RuntimeException("Failed to update user: " + e.getMessage(), e);
        }
    }

    public synchronized void logout() {
        if (currentUser != null) {
            logger.info("User with ID {} logged out", currentUser.getId());
            this.currentUser = null;
        }
    }
}