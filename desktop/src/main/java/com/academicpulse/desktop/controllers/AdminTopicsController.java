package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.AdminTopic;
import com.academicpulse.desktop.model.AdminTopicsData;
import com.academicpulse.desktop.model.User;
import javafx.application.Platform;
import javafx.beans.property.SimpleObjectProperty;
import javafx.beans.property.SimpleStringProperty;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.ComboBox;
import javafx.scene.control.Label;
import javafx.scene.control.TableCell;
import javafx.scene.control.TableColumn;
import javafx.scene.control.TableView;
import javafx.scene.control.TextField;
import javafx.util.StringConverter;

import java.util.ArrayList;
import java.util.List;

/** Admin's Topics screen — create topics and assign/reassign a lecturer, mirrors Admin\TopicController. */
public class AdminTopicsController {
    @FXML private Label statusLabel;
    @FXML private TextField titleField;
    @FXML private TextField descriptionField;
    @FXML private ComboBox<User> newTopicLecturerCombo;
    @FXML private TableView<AdminTopic> topicsTable;
    @FXML private TableColumn<AdminTopic, String> titleColumn;
    @FXML private TableColumn<AdminTopic, String> descriptionColumn;
    @FXML private TableColumn<AdminTopic, AdminTopic> lecturerColumn;
    @FXML private TableColumn<AdminTopic, String> subscribersColumn;
    @FXML private TableColumn<AdminTopic, AdminTopic> actionsColumn;

    private List<User> lecturers = List.of();

    private static final StringConverter<User> LECTURER_CONVERTER = new StringConverter<>() {
        @Override
        public String toString(User user) {
            return user == null ? "Unassigned" : user.name;
        }

        @Override
        public User fromString(String string) {
            return null;
        }
    };

    @FXML
    public void initialize() {
        titleColumn.setCellValueFactory(row -> new SimpleStringProperty(row.getValue().title));
        descriptionColumn.setCellValueFactory(row -> new SimpleStringProperty(
                row.getValue().description == null ? "" : row.getValue().description));
        subscribersColumn.setCellValueFactory(row -> new SimpleStringProperty(String.valueOf(row.getValue().subscribersCount)));

        newTopicLecturerCombo.setConverter(LECTURER_CONVERTER);

        lecturerColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        lecturerColumn.setCellFactory(col -> new TableCell<>() {
            private final ComboBox<User> combo = new ComboBox<>();

            {
                combo.setConverter(LECTURER_CONVERTER);
                combo.setOnAction(e -> {
                    AdminTopic topic = getTableRow() == null ? null : getTableRow().getItem();
                    if (topic != null) {
                        assignLecturer(topic, combo.getValue());
                    }
                });
            }

            @Override
            protected void updateItem(AdminTopic topic, boolean empty) {
                super.updateItem(topic, empty);
                if (empty || topic == null) {
                    setGraphic(null);
                    return;
                }
                List<User> options = new ArrayList<>();
                options.add(null);
                options.addAll(lecturers);
                combo.getItems().setAll(options);
                combo.setValue(lecturers.stream()
                        .filter(l -> topic.lecturerId != null && l.id == topic.lecturerId)
                        .findFirst().orElse(null));
                setGraphic(combo);
            }
        });

        actionsColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        actionsColumn.setCellFactory(col -> new TableCell<>() {
            private final Button deleteButton = new Button("Delete");

            {
                deleteButton.setOnAction(e -> {
                    AdminTopic topic = getTableRow() == null ? null : getTableRow().getItem();
                    if (topic != null) {
                        deleteTopic(topic);
                    }
                });
            }

            @Override
            protected void updateItem(AdminTopic topic, boolean empty) {
                super.updateItem(topic, empty);
                setGraphic(empty || topic == null ? null : deleteButton);
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
                AdminTopicsData data = Router.api().getAdminTopics();
                Platform.runLater(() -> render(data));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load topics: " + describe(e)));
            }
        }).start();
    }

    private void render(AdminTopicsData data) {
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data." : "");
        lecturers = data.lecturers == null ? List.of() : data.lecturers;
        newTopicLecturerCombo.getItems().setAll(lecturers);
        topicsTable.getItems().setAll(data.topics == null ? List.of() : data.topics);
        topicsTable.refresh();
    }

    @FXML
    private void handleCreate() {
        String title = titleField.getText() == null ? "" : titleField.getText().trim();
        String description = descriptionField.getText() == null ? "" : descriptionField.getText().trim();
        User lecturer = newTopicLecturerCombo.getValue();

        if (title.isEmpty()) {
            statusLabel.setText("Enter a title before creating a topic.");
            return;
        }

        statusLabel.setText("Creating...");
        new Thread(() -> {
            try {
                Router.api().createTopic(title, description.isEmpty() ? null : description,
                        lecturer == null ? null : lecturer.id);
                Platform.runLater(() -> {
                    titleField.clear();
                    descriptionField.clear();
                    newTopicLecturerCombo.setValue(null);
                    load();
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to create topic: " + describe(e)));
            }
        }).start();
    }

    private void assignLecturer(AdminTopic topic, User lecturer) {
        new Thread(() -> {
            try {
                Router.api().assignTopicLecturer(topic.id, lecturer == null ? null : lecturer.id);
                Platform.runLater(this::load);
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to reassign topic: " + describe(e)));
            }
        }).start();
    }

    private void deleteTopic(AdminTopic topic) {
        statusLabel.setText("Removing...");
        new Thread(() -> {
            try {
                Router.api().deleteTopic(topic.id);
                Platform.runLater(this::load);
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to remove topic: " + describe(e)));
            }
        }).start();
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
