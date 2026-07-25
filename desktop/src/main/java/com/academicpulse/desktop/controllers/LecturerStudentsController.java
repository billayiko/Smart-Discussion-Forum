package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.LecturerStudentsData;
import javafx.application.Platform;
import javafx.beans.property.SimpleObjectProperty;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.TableCell;
import javafx.scene.control.TableColumn;
import javafx.scene.control.TableView;
import javafx.scene.layout.FlowPane;
import javafx.scene.layout.VBox;

import java.util.List;

/** Lecturer's Students screen — mirrors pages/dashboards/lecturer/students.blade.php. */
public class LecturerStudentsController {
    @FXML private Label statusLabel;
    @FXML private FlowPane statsBox;
    @FXML private TableView<LecturerStudentsData.StudentRow> studentsTable;
    @FXML private TableColumn<LecturerStudentsData.StudentRow, LecturerStudentsData.StudentRow> nameColumn;
    @FXML private TableColumn<LecturerStudentsData.StudentRow, LecturerStudentsData.StudentRow> statusColumn;
    @FXML private TableColumn<LecturerStudentsData.StudentRow, LecturerStudentsData.StudentRow> topicsColumn;

    @FXML
    public void initialize() {
        nameColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        nameColumn.setCellFactory(col -> new TableCell<>() {
            @Override
            protected void updateItem(LecturerStudentsData.StudentRow row, boolean empty) {
                super.updateItem(row, empty);
                if (empty || row == null) {
                    setGraphic(null);
                    return;
                }
                Label name = new Label(row.name);
                name.setStyle("-fx-font-weight: bold;");
                Label email = new Label(row.email);
                email.setStyle("-fx-text-fill: #71717a; -fx-font-size: 11px;");
                setGraphic(new VBox(2, name, email));
            }
        });

        statusColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        statusColumn.setCellFactory(col -> new TableCell<>() {
            @Override
            protected void updateItem(LecturerStudentsData.StudentRow row, boolean empty) {
                super.updateItem(row, empty);
                if (empty || row == null) {
                    setGraphic(null);
                    return;
                }
                Label tag = new Label(row.isOnline ? "Online" : "Offline");
                tag.getStyleClass().add("app-tag");
                tag.getStyleClass().add(row.isOnline ? "app-tag-green" : "app-tag-gray");
                setGraphic(tag);
            }
        });

        topicsColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        topicsColumn.setCellFactory(col -> new TableCell<>() {
            @Override
            protected void updateItem(LecturerStudentsData.StudentRow row, boolean empty) {
                super.updateItem(row, empty);
                if (empty || row == null) {
                    setGraphic(null);
                    return;
                }
                if (row.subscribedTopics == null || row.subscribedTopics.isEmpty()) {
                    Label tag = new Label("Not subscribed");
                    tag.getStyleClass().addAll("app-tag", "app-tag-gray");
                    setGraphic(tag);
                    return;
                }
                FlowPane tags = new FlowPane(6, 6);
                for (String topic : row.subscribedTopics) {
                    Label tag = new Label(topic);
                    tag.getStyleClass().addAll("app-tag", "app-tag-purple");
                    tags.getChildren().add(tag);
                }
                setGraphic(tags);
            }
        });

        load();
    }

    @FXML
    private void handleRefresh() {
        load();
    }

    private void load() {
        statusLabel.setText("Loading...");

        new Thread(() -> {
            try {
                LecturerStudentsData data = Router.api().getLecturerStudents();
                Platform.runLater(() -> render(data));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load students: " + describe(e)));
            }
        }).start();
    }

    private void render(LecturerStudentsData data) {
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data." : "");

        List<LecturerStudentsData.StudentRow> students = data.students == null ? List.of() : data.students;
        long onlineCount = students.stream().filter(s -> s.isOnline).count();
        long subscribedCount = students.stream().filter(s -> s.subscribedTopics != null && !s.subscribedTopics.isEmpty()).count();

        statsBox.getChildren().setAll(
                statCard("Your Topics", String.valueOf(data.topics == null ? 0 : data.topics.size())),
                statCard("Total Students", String.valueOf(students.size())),
                statCard("Online Now", String.valueOf(onlineCount)),
                statCard("Subscribed to Your Topics", String.valueOf(subscribedCount))
        );

        studentsTable.getItems().setAll(students);
    }

    private VBox statCard(String label, String value) {
        Label valueLabel = new Label(value);
        valueLabel.getStyleClass().add("app-stat-value");
        Label textLabel = new Label(label);
        textLabel.getStyleClass().add("app-stat-label");
        VBox card = new VBox(2, valueLabel, textLabel);
        card.getStyleClass().add("app-stat-card");
        return card;
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
