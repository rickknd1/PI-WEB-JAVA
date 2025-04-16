package com.syncylinky.test;

import com.syncylinky.controllers.MainController;
import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;

import java.io.IOException;

public class Main extends Application {
    @Override
    public void start(Stage primaryStage) throws Exception {
        FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/syncylinky/views/main.fxml"));
        Parent root = loader.load();

        // Pour vérifier que le contrôleur est bien chargé
        System.out.println("Controller loaded: " + loader.getController());

        Scene scene = new Scene(root, 1200, 800);
        primaryStage.setTitle("SyncYLinkY Admin");
        primaryStage.setScene(scene);
        primaryStage.show();
    }

    public static void main(String[] args) {
        launch(args);
    }

}