package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.AdminComplaint;
import javafx.application.Platform;
import javafx.beans.property.SimpleObjectProperty;
import javafx.beans.property.SimpleStringProperty;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.TableCell;
import javafx.scene.control.TableColumn;
import javafx.scene.control.TableView;
import javafx.scene.layout.HBox;

import java.util.List;

/** Admin's Complaints screen — dismiss or resolve reported questions, mirrors Admin\ComplaintController. */
public class AdminComplaintsController {
    @FXML private Label statusLabel;
    @FXML private TableView<AdminComplaint> complaintsTable;
    @FXML private TableColumn<AdminComplaint, String> questionColumn;
    @FXML private TableColumn<AdminComplaint, String> authorColumn;
    @FXML private TableColumn<AdminComplaint, String> reasonColumn;
    @FXML private TableColumn<AdminComplaint, String> reporterColumn;
    @FXML private TableColumn<AdminComplaint, AdminComplaint> statusColumn;
    @FXML private TableColumn<AdminComplaint, AdminComplaint> actionsColumn;

    @FXML
    public void initialize() {
        questionColumn.setCellValueFactory(row -> new SimpleStringProperty(
                row.getValue().questionTitle == null ? "(deleted question)" : row.getValue().questionTitle));
        authorColumn.setCellValueFactory(row -> new SimpleStringProperty(
                row.getValue().questionAuthor == null ? "" : row.getValue().questionAuthor));
        reasonColumn.setCellValueFactory(row -> new SimpleStringProperty(row.getValue().reason));
        reporterColumn.setCellValueFactory(row -> new SimpleStringProperty(row.getValue().reporterName));

        statusColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        statusColumn.setCellFactory(col -> new TableCell<>() {
            @Override
            protected void updateItem(AdminComplaint complaint, boolean empty) {
                super.updateItem(complaint, empty);
                if (empty || complaint == null) {
                    setGraphic(null);
                    return;
                }
                Label tag = new Label(capitalize(complaint.status));
                tag.getStyleClass().add("app-tag");
                tag.getStyleClass().add("pending".equals(complaint.status) ? "app-tag-orange" : "app-tag-gray");
                setGraphic(tag);
            }
        });

        actionsColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        actionsColumn.setCellFactory(col -> new TableCell<>() {
            private final Button dismissButton = new Button("Dismiss");
            private final Button deleteButton = new Button("Delete question");
            private final HBox box = new HBox(6, dismissButton, deleteButton);

            {
                dismissButton.getStyleClass().add("app-btn-light");
                deleteButton.getStyleClass().add("app-btn-light");
                dismissButton.setOnAction(e -> resolve(rowItem(), "dismiss"));
                deleteButton.setOnAction(e -> resolve(rowItem(), "delete_question"));
            }

            private AdminComplaint rowItem() {
                return getTableRow() == null ? null : getTableRow().getItem();
            }

            @Override
            protected void updateItem(AdminComplaint complaint, boolean empty) {
                super.updateItem(complaint, empty);
                if (empty || complaint == null) {
                    setGraphic(null);
                    return;
                }
                boolean pending = "pending".equals(complaint.status);
                dismissButton.setDisable(!pending);
                deleteButton.setDisable(!pending);
                setGraphic(box);
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
                List<AdminComplaint> complaints = Router.api().getAdminComplaints();
                Platform.runLater(() -> render(complaints));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load complaints: " + describe(e)));
            }
        }).start();
    }

    private void render(List<AdminComplaint> complaints) {
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data." : "");
        complaintsTable.getItems().setAll(complaints == null ? List.of() : complaints);
    }

    private void resolve(AdminComplaint complaint, String action) {
        if (complaint == null) {
            return;
        }
        statusLabel.setText("Updating...");
        new Thread(() -> {
            try {
                Router.api().resolveComplaint(complaint.id, action);
                Platform.runLater(this::load);
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to resolve complaint: " + describe(e)));
            }
        }).start();
    }

    private String capitalize(String value) {
        if (value == null || value.isEmpty()) {
            return "";
        }
        return Character.toUpperCase(value.charAt(0)) + value.substring(1);
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
