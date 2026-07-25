package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import javafx.fxml.FXML;

/** Landing screen shown on launch, mirroring the web app's welcome page (same branding/copy/feature grid). */
public class WelcomeController {
    @FXML
    private void handleLogin() {
        try {
            Router.navigate("/login.fxml", "Academic Pulse - Login");
        } catch (Exception ignored) {
            // the login screen itself will report its own load errors
        }
    }

    @FXML
    private void handleGetStarted() {
        try {
            Router.navigate("/register.fxml", "Academic Pulse - Create Account");
        } catch (Exception ignored) {
            // the register screen itself will report its own load errors
        }
    }
}
