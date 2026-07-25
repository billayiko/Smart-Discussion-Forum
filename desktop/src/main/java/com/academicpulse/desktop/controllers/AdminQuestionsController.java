package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.AdminQuestionsData;
import javafx.application.Platform;
import javafx.beans.property.SimpleObjectProperty;
import javafx.beans.property.SimpleStringProperty;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.TableCell;
import javafx.scene.control.TableColumn;
import javafx.scene.control.TableView;

import java.util.List;

/** Admin's real Discussion Forum — a flat, all-questions moderation list, mirrors admin/questions/index.blade.php + _list.blade.php. */
public class AdminQuestionsController {
    @FXML private Label statusLabel;
    @FXML private TableView<AdminQuestionsData.Item> questionsTable;
    @FXML private TableColumn<AdminQuestionsData.Item, String> titleColumn;
    @FXML private TableColumn<AdminQuestionsData.Item, String> topicColumn;
    @FXML private TableColumn<AdminQuestionsData.Item, String> authorColumn;
    @FXML private TableColumn<AdminQuestionsData.Item, AdminQuestionsData.Item> statusColumn;
    @FXML private TableColumn<AdminQuestionsData.Item, AdminQuestionsData.Item> actionsColumn;

    @FXML
    public void initialize() {
        titleColumn.setCellValueFactory(row -> new SimpleStringProperty(row.getValue().title
                + (row.getValue().flaggedOffTopic ? "  ⚠ possibly off-topic" : "")));
        topicColumn.setCellValueFactory(row -> new SimpleStringProperty(row.getValue().topicTitle));
        authorColumn.setCellValueFactory(row -> new SimpleStringProperty(row.getValue().userName));

        statusColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        statusColumn.setCellFactory(col -> new TableCell<>() {
            @Override
            protected void updateItem(AdminQuestionsData.Item question, boolean empty) {
                super.updateItem(question, empty);
                if (empty || question == null) {
                    setGraphic(null);
                    return;
                }
                Label tag = new Label(question.answersCount > 0 ? "Answered" : "Not answered");
                tag.getStyleClass().add("app-tag");
                tag.getStyleClass().add(question.answersCount > 0 ? "app-tag-green" : "app-tag-orange");
                setGraphic(tag);
            }
        });

        actionsColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        actionsColumn.setCellFactory(col -> new TableCell<>() {
            private final Button viewButton = new Button("View");
            private final Button deleteButton = new Button("Delete");
            private final javafx.scene.layout.HBox box = new javafx.scene.layout.HBox(6, viewButton, deleteButton);

            {
                viewButton.getStyleClass().add("app-btn-light");
                deleteButton.getStyleClass().add("app-btn-danger");
                viewButton.setOnAction(e -> openQuestion(rowItem()));
                deleteButton.setOnAction(e -> deleteQuestion(rowItem()));
            }

            private AdminQuestionsData.Item rowItem() {
                return getTableRow() == null ? null : getTableRow().getItem();
            }

            @Override
            protected void updateItem(AdminQuestionsData.Item question, boolean empty) {
                super.updateItem(question, empty);
                setGraphic(empty || question == null ? null : box);
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
                AdminQuestionsData data = Router.api().getAdminQuestions();
                Platform.runLater(() -> render(data));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load questions: " + describe(e)));
            }
        }).start();
    }

    private void render(AdminQuestionsData data) {
        String base = data.unansweredCount > 0
                ? data.unansweredCount + " question(s) still awaiting an answer."
                : "";
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data. " + base : base);
        questionsTable.getItems().setAll(data.questions == null ? List.of() : data.questions);
    }

    private void openQuestion(AdminQuestionsData.Item question) {
        if (question == null) {
            return;
        }
        try {
            ThreadDetailController controller = Router.navigate("/thread-detail.fxml", "Academic Pulse - " + question.title);
            controller.setQuestionIdForAdmin(question.id);
        } catch (Exception e) {
            statusLabel.setText("Failed to open question: " + describe(e));
        }
    }

    private void deleteQuestion(AdminQuestionsData.Item question) {
        if (question == null) {
            return;
        }
        statusLabel.setText("Deleting...");
        new Thread(() -> {
            try {
                Router.api().deleteQuestion(question.id);
                Platform.runLater(this::load);
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to delete question: " + describe(e)));
            }
        }).start();
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
