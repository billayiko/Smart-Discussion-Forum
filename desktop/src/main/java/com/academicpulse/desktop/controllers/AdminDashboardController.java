package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.AdminDashboard;
import javafx.application.Platform;
import javafx.beans.property.SimpleStringProperty;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.TableColumn;
import javafx.scene.control.TableView;
import javafx.scene.control.TextField;
import javafx.scene.layout.FlowPane;
import javafx.scene.layout.VBox;

import java.util.List;

/** Admin's main dashboard — mirrors pages/dashboards/admin.blade.php (stat bubbles + filterable/searchable quiz table). */
public class AdminDashboardController {
    @FXML private Label statusLabel;
    @FXML private FlowPane bubblesBox;
    @FXML private TextField searchField;
    @FXML private TableView<AdminDashboard.QuizRow> quizzesTable;
    @FXML private TableColumn<AdminDashboard.QuizRow, String> titleColumn;
    @FXML private TableColumn<AdminDashboard.QuizRow, String> subjectColumn;
    @FXML private TableColumn<AdminDashboard.QuizRow, String> questionsColumn;
    @FXML private TableColumn<AdminDashboard.QuizRow, String> durationColumn;
    @FXML private TableColumn<AdminDashboard.QuizRow, String> attemptsColumn;
    @FXML private TableColumn<AdminDashboard.QuizRow, String> avgScoreColumn;
    @FXML private TableColumn<AdminDashboard.QuizRow, String> statusColumn;

    private String currentStatusFilter;

    @FXML
    public void initialize() {
        titleColumn.setCellValueFactory(row -> new SimpleStringProperty(row.getValue().title));
        subjectColumn.setCellValueFactory(row -> new SimpleStringProperty(row.getValue().subject));
        questionsColumn.setCellValueFactory(row -> new SimpleStringProperty(String.valueOf(row.getValue().totalQuestions)));
        durationColumn.setCellValueFactory(row -> new SimpleStringProperty(row.getValue().durationMinutes + " mins"));
        attemptsColumn.setCellValueFactory(row -> new SimpleStringProperty(String.valueOf(row.getValue().attemptsCount)));
        avgScoreColumn.setCellValueFactory(row -> new SimpleStringProperty(
                row.getValue().averageScorePercent != null ? row.getValue().averageScorePercent + "%" : "—"));
        statusColumn.setCellValueFactory(row -> new SimpleStringProperty(capitalize(row.getValue().stage)));

        load();
    }

    @FXML
    private void handleFilterAll() {
        currentStatusFilter = null;
        load();
    }

    @FXML
    private void handleFilterPublished() {
        currentStatusFilter = "published";
        load();
    }

    @FXML
    private void handleFilterDraft() {
        currentStatusFilter = "draft";
        load();
    }

    @FXML
    private void handleFilterScheduled() {
        currentStatusFilter = "scheduled";
        load();
    }

    @FXML
    private void handleSearch() {
        load();
    }

    private void load() {
        statusLabel.setText("Loading...");
        String search = searchField.getText();
        String status = currentStatusFilter;

        new Thread(() -> {
            try {
                AdminDashboard data = Router.api().getAdminDashboard(status, search);
                Platform.runLater(() -> render(data));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load dashboard: " + describe(e)));
            }
        }).start();
    }

    private void render(AdminDashboard data) {
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data." : "");

        AdminDashboard.Bubbles b = data.bubbles;
        bubblesBox.getChildren().setAll(
                statCard("Topics", String.valueOf(b.topics), b.unassignedTopics + " unassigned"),
                statCard("Questions", String.valueOf(b.questions), b.unansweredQuestions + " unanswered"),
                statCard("Complaints", String.valueOf(b.pendingComplaints), "pending review"),
                statCard("Quizzes", String.valueOf(b.quizzes), b.publishedQuizzes + " published"),
                statCard("Students", String.valueOf(b.students), "enrolled"),
                statCard("Lecturers", String.valueOf(b.lecturers), "teaching")
        );

        List<AdminDashboard.QuizRow> quizzes = data.quizzes;
        quizzesTable.getItems().setAll(quizzes == null ? List.of() : quizzes);
    }

    private VBox statCard(String label, String value, String sub) {
        Label valueLabel = new Label(value);
        valueLabel.getStyleClass().add("app-stat-value");
        Label textLabel = new Label(label);
        textLabel.getStyleClass().add("app-stat-label");
        Label subLabel = new Label(sub);
        subLabel.getStyleClass().add("app-stat-label");
        VBox card = new VBox(2, valueLabel, textLabel, subLabel);
        card.getStyleClass().add("app-stat-card");
        return card;
    }

    private String capitalize(String value) {
        if (value == null || value.isEmpty()) {
            return "";
        }
        String withSpaces = value.replace("_", " ");
        return Character.toUpperCase(withSpaces.charAt(0)) + withSpaces.substring(1);
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
