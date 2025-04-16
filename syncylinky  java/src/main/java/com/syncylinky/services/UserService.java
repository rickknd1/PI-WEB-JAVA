package com.syncylinky.services;

import com.syncylinky.dao.CategoryDAO;
import com.syncylinky.dao.UserDAO;
import com.syncylinky.models.Category;
import com.syncylinky.models.User;
import java.util.List;

public class UserService {
    private final UserDAO userDAO;

    public UserService() {
        this.userDAO = new UserDAO();
    }

    public List<Category> getAllCategories() {
        return new CategoryDAO().getAllCategories();
    }
    public List<User> getAllUsers() {
        return userDAO.getAllUsers();
    }

    public User getUserById(int id) {
        return userDAO.getUserById(id);
    }


    public boolean addUser(User user) {
        return userDAO.addUser(user);
    }

    public boolean updateUser(User user) {
        return userDAO.updateUser(user);
    }

    public boolean deleteUser(int id) {
        return userDAO.deleteUser(id);
    }



}