package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.LecturerMark;
import javafx.application.Platform;
import javafx.beans.property.SimpleObjectProperty;
import javafx.beans.property.SimpleStringProperty;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.TableCell;
import javafx.scene.control.TableColumn;
import javafx.scene.control.TableView;
import javafx.scene.layout.VBox;

import java.util.List;

/** Lecturer's Student Marks screen — mirrors pages/dashboards/lecturer/marks.blade.php. */
public class LecturerMarksController {
    @FXML private Label statusLabel;
    @FXML private TableView<LecturerMark> marksTable;
    @FXML private TableColumn<LecturerMark, LecturerMark> studentColumn;
    @FXML private TableColumn<LecturerMark, String> postsColumn;
    @FXML private TableColumn<LecturerMark, String> participationColumn;
    @FXML private TableColumn<LecturerMark, String> attemptsColumn;
    @FXML private TableColumn<LecturerMark, String> quizAverageColumn;
    @FXML private TableColumn<LecturerMark, LecturerMark> combinedColumn;

    @FXML
    public void initialize() {
        studentColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        studentColumn.setCellFactory(col -> new TableCell<>() {
            @Override
            protected void updateItem(LecturerMark mark, boolean empty) {
                super.updateItem(mark, empty);
                if (empty || mark == null) {
                    setGraphic(null);
                    return;
                }
                Label name = new Label(mark.studentName);
                name.setStyle("-fx-font-weight: bold;");
                Label email = new Label(mark.studentEmail);
                email.setStyle("-fx-text-fill: #71717a; -fx-font-size: 11px;");
                setGraphic(new VBox(2, name, email));
            }
        });
        postsColumn.setCellValueFactory(row -> new SimpleStringProperty(String.valueOf(row.getValue().posts)));
        participationColumn.setCellValueFactory(row -> new SimpleStringProperty(row.getValue().participationScore + "%"));
        attemptsColumn.setCellValueFactory(row -> new SimpleStringProperty(String.valueOf(row.getValue().quizAttempts)));
        quizAverageColumn.setCellValueFactory(row -> new SimpleStringProperty(
                row.getValue().quizAverage != null ? row.getValue().quizAverage + "%" : "—"));

        combinedColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        combinedColumn.setCellFactory(col -> new TableCell<>() {
            @Override
            protected void updateItem(LecturerMark mark, boolean empty) {
                super.updateItem(mark, empty);
                if (empty || mark == null) {
                    setGraphic(null);
                    return;
                }
                Label tag = new Label(mark.combinedScore + "%");
                tag.getStyleClass().add("app-tag");
                tag.getStyleClass().add(mark.combinedScore >= 70 ? "app-tag-green"
                        : mark.combinedScore >= 40 ? "app-tag-orange" : "app-tag-gray");
                setGraphic(tag);
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
                List<LecturerMark> marks = Router.api().getLecturerMarks();
                Platform.runLater(() -> render(marks));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load marks: " + describe(e)));
            }
        }).start();
    }

    private void render(List<LecturerMark> marks) {
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data." : "");
        marksTable.getItems().setAll(marks == null ? List.of() : marks);
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
