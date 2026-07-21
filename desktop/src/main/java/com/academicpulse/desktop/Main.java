package com.academicpulse.desktop;

import javafx.application.Application;
import javafx.stage.Stage;

public class Main extends Application {
    @Override
    public void start(Stage primaryStage) throws Exception {
        Router.init(primaryStage);
        Router.navigate("/login.fxml", "Academic Pulse - Login");
        primaryStage.show();
    }

    public static void main(String[] args) {
        launch(args);
    }
}
