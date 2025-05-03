package com.syncylinky.models;

import java.time.LocalDate;
import java.util.ArrayList;
import java.util.List;

public class User {
    private int id;
    private String email;
    private String role;
    private String password;
    private String name;
    private String firstname;
    private String username;
    private LocalDate dateOB;
    private String gender;
    private List<Category> interests = new ArrayList<>();
    private boolean banned;
    private boolean isVerified;
    private boolean isActive ;
    private String googleId;
    private boolean googleAuthenticatorEnabled = false;
    private String verificationToken;
    private String pp; // Profile picture path

    private boolean hasFacialRecognition;  // Nouvel attribut



    // Constructors
    public User() {
    }

    public User(int id, String email, String role, String password, String name,
                String firstname, String username, LocalDate dateOB,
                String gender, boolean banned, boolean isVerified) {
        this.id = id;
        this.email = email;
        this.role = role;
        this.password = password;
        this.name = name;
        this.firstname = firstname;
        this.username = username;
        this.dateOB = dateOB;
        this.gender = gender;
        this.banned = banned;
        this.isVerified = true;
        this.interests = new ArrayList<>();
        this.isActive = true;
        this.googleAuthenticatorEnabled = false;
        this.hasFacialRecognition = false;  // Valeur par défaut
    }

    // Getters and Setters
    public boolean isGoogleAuthenticatorEnabled() {
        return googleAuthenticatorEnabled;
    }

    public void setGoogleAuthenticatorEnabled(boolean googleAuthenticatorEnabled) {
        this.googleAuthenticatorEnabled = googleAuthenticatorEnabled;
    }
    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getRole() {
        return role;
    }

    public void setRole(String role) {
        this.role = role;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getFirstname() {
        return firstname;
    }

    public void setFirstname(String firstname) {
        this.firstname = firstname;
    }

    public String getUsername() {
        return username;
    }

    public void setUsername(String username) {
        this.username = username;
    }

    public LocalDate getDateOB() {
        return dateOB;
    }

    public void setDateOB(LocalDate dateOB) {
        this.dateOB = dateOB;
    }

    public String getGender() {
        return gender;
    }

    public void setGender(String gender) {
        this.gender = gender;
    }

    public List<Category> getInterests() {
        return interests;
    }

    public void setInterests(List<Category> interests) {
        this.interests = interests;
    }

    public boolean isBanned() {
        return banned;
    }

    public void setBanned(boolean banned) {
        this.banned = banned;
    }

    public boolean isVerified() {
        return isVerified;
    }

    public void setVerified(boolean verified) {
        isVerified = verified;
    }

    public boolean isActive() {
        return isActive;
    }

    public void setActive(boolean active) {
        isActive = active;
    }

    public String getGoogleId() {
        return googleId;
    }

    public void setGoogleId(String googleId) {
        this.googleId = googleId;
    }

    public String getVerificationToken() {
        return verificationToken;
    }

    public void setVerificationToken(String verificationToken) {
        this.verificationToken = verificationToken;
    }

    public String getPp() {
        return pp;
    }

    public void setPp(String pp) {
        this.pp = pp;
    }

    // Utility methods
    public void addInterest(Category category) {
        if (!this.interests.contains(category)) {
            this.interests.add(category);
        }
    }

    public void removeInterest(Category category) {
        this.interests.remove(category);
    }

    public List<String> getRoles() {
        List<String> roles = new ArrayList<>();
        roles.add(this.role);
        return roles;
    }

    public boolean hasInterest(Category category) {
        return this.interests.contains(category);
    }

    public String getFullName() {
        return this.firstname + " " + this.name;
    }

    public int getAge() {
        return LocalDate.now().getYear() - this.dateOB.getYear();
    }

    @Override
    public String toString() {
        return "User{" +
                "id=" + id +
                ", email='" + email + '\'' +
                ", role='" + role + '\'' +
                ", name='" + name + '\'' +
                ", firstname='" + firstname + '\'' +
                ", username='" + username + '\'' +
                ", banned=" + banned +
                ", isVerified=" + isVerified +
                '}';
    }
    public boolean hasFacialRecognition() {
        return hasFacialRecognition;
    }

    public void setHasFacialRecognition(boolean hasFacialRecognition) {
        this.hasFacialRecognition = hasFacialRecognition;
    }

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;
        User user = (User) o;
        return id == user.id;
    }

    @Override
    public int hashCode() {
        return id;
    }
}