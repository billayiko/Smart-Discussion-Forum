package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.User;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.Label;

/** Controller for the shared left-hand navigation, included on every authenticated screen. */
public class SidebarController {
    @FXML private Label userNameLabel;
    @FXML private Label userRoleLabel;
    @FXML private Button analyticsButton;
    @FXML private Button adminDashboardButton;
    @FXML private Button topicsButton;
    @FXML private Button complaintsButton;
    @FXML private Button membersButton;
    @FXML private Button lecturerDashboardButton;
    @FXML private Button lecturerStudentsButton;
    @FXML private Button lecturerMarksButton;

    @FXML
    public void initialize() {
        User user = Router.currentUser();
        if (user != null) {
            userNameLabel.setText(user.name);
            userRoleLabel.setText(user.roleLabel());
        }

        boolean isAdmin = user != null && "admin".equals(user.role);
        for (Button adminOnlyButton : new Button[]{analyticsButton, adminDashboardButton, topicsButton, complaintsButton, membersButton}) {
            adminOnlyButton.setVisible(isAdmin);
            adminOnlyButton.setManaged(isAdmin);
        }

        boolean isLecturer = user != null && "lecturer".equals(user.role);
        for (Button lecturerOnlyButton : new Button[]{lecturerDashboardButton, lecturerStudentsButton, lecturerMarksButton}) {
            lecturerOnlyButton.setVisible(isLecturer);
            lecturerOnlyButton.setManaged(isLecturer);
        }
    }

    @FXML
    private void handleAdminDashboard() {
        try {
            Router.navigate("/admin-dashboard.fxml", "Academic Pulse - Admin Dashboard");
        } catch (Exception ignored) {
            // nothing sensible to show here; the target screen will report its own load errors
        }
    }

    @FXML
    private void handleTopics() {
        try {
            Router.navigate("/admin-topics.fxml", "Academic Pulse - Topics");
        } catch (Exception ignored) {
            // nothing sensible to show here; the target screen will report its own load errors
        }
    }

    @FXML
    private void handleComplaints() {
        try {
            Router.navigate("/admin-complaints.fxml", "Academic Pulse - Complaints");
        } catch (Exception ignored) {
            // nothing sensible to show here; the target screen will report its own load errors
        }
    }

    @FXML
    private void handleMembers() {
        try {
            Router.navigate("/admin-members.fxml", "Academic Pulse - Members");
        } catch (Exception ignored) {
            // nothing sensible to show here; the target screen will report its own load errors
        }
    }

    @FXML
    private void handleSettings() {
        try {
            Router.navigate("/settings.fxml", "Academic Pulse - Settings");
        } catch (Exception ignored) {
            // nothing sensible to show here; the target screen will report its own load errors
        }
    }

    @FXML
    private void handleLecturerDashboard() {
        try {
            Router.navigate("/lecturer-dashboard.fxml", "Academic Pulse - Lecturer Dashboard");
        } catch (Exception ignored) {
            // nothing sensible to show here; the target screen will report its own load errors
        }
    }

    @FXML
    private void handleLecturerStudents() {
        try {
            Router.navigate("/lecturer-students.fxml", "Academic Pulse - Students");
        } catch (Exception ignored) {
            // nothing sensible to show here; the target screen will report its own load errors
        }
    }

    @FXML
    private void handleLecturerMarks() {
        try {
            Router.navigate("/lecturer-marks.fxml", "Academic Pulse - Student Marks");
        } catch (Exception ignored) {
            // nothing sensible to show here; the target screen will report its own load errors
        }
    }

    @FXML
    private void handleDiscussionForum() {
        try {
            Router.navigate("/topics.fxml", "Academic Pulse - Discussion Forum");
        } catch (Exception ignored) {
            // nothing sensible to show here; the target screen will report its own load errors
        }
    }

    @FXML
    private void handleMessages() {
        try {
            Router.navigate("/messages.fxml", "Academic Pulse - Messages");
        } catch (Exception ignored) {
            // nothing sensible to show here; the target screen will report its own load errors
        }
    }

    @FXML
    private void handleAnalytics() {
        try {
            Router.navigate("/analytics.fxml", "Academic Pulse - Analytics");
        } catch (Exception ignored) {
            // nothing sensible to show here; the target screen will report its own load errors
        }
    }

    @FXML
    private void handleLogout() {
        new Thread(() -> {
            try {
                Router.api().logout();
            } catch (Exception ignored) {
                // token may already be invalid server-side; proceed to login regardless
            }
            Router.setCurrentUser(null);
            Platform.runLater(() -> {
                try {
                    Router.navigate("/login.fxml", "Academic Pulse - Login");
                } catch (Exception ignored) {
                }
            });
        }).start();
    }
}
