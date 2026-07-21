package com.academicpulse.desktop;

import com.academicpulse.desktop.api.ApiClient;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;

import java.io.IOException;

/** Owns the primary stage and the single shared {@link ApiClient} for the app's lifetime. */
public final class Router {
    private static final ApiClient API_CLIENT = new ApiClient();
    private static Stage stage;

    private Router() {
    }

    public static void init(Stage primaryStage) {
        stage = primaryStage;
    }

    public static ApiClient api() {
        return API_CLIENT;
    }

    /** Loads the given FXML into the primary stage and returns its controller. */
    public static <T> T navigate(String fxmlPath, String title) throws IOException {
        FXMLLoader loader = new FXMLLoader(Router.class.getResource(fxmlPath));
        Parent root = loader.load();
        stage.setScene(new Scene(root, 960, 640));
        stage.setTitle(title);
        return loader.getController();
    }
}
