package com.syncylinky.models;

public class Category {
    private int id;
    private String nom;
    private String description;
    private String cover;

    // Constructeurs

    public Category(int id, String nom, String description, String cover) {
        this.id = id;
        this.nom = nom;
        this.description = description;
        this.cover = cover;
    }

    // Getters et setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getNom() { return nom; }
    public void setNom(String nom) { this.nom = nom; }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }

    public String getCover() { return cover; }
    public void setCover(String cover) { this.cover = cover; }
}