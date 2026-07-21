package com.academicpulse.desktop;

import com.academicpulse.desktop.api.ApiClient;
import com.academicpulse.desktop.model.User;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;

import java.io.IOException;

/** Owns the primary stage, the shared {@link ApiClient}, and the logged-in user for the app's lifetime. */
public final class Router {
    private static final ApiClient API_CLIENT = new ApiClient();
    private static Stage stage;
    private static User currentUser;

    private Router() {
    }

    public static void init(Stage primaryStage) {
        stage = primaryStage;
    }

    public static ApiClient api() {
        return API_CLIENT;
    }

    public static User currentUser() {
        return currentUser;
    }

    public static void setCurrentUser(User user) {
        currentUser = user;
    }

    /** Loads the given FXML into the primary stage and returns its controller. */
    public static <T> T navigate(String fxmlPath, String title) throws IOException {
        FXMLLoader loader = new FXMLLoader(Router.class.getResource(fxmlPath));
        Parent root = loader.load();
        Scene scene = new Scene(root, 1080, 640);
        scene.getStylesheets().add(Router.class.getResource("/app.css").toExternalForm());
        stage.setScene(scene);
        stage.setTitle(title);
        return loader.getController();
    }
}
