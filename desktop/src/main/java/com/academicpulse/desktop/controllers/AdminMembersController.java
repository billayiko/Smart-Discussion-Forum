package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.AdminMember;
import com.academicpulse.desktop.model.AdminMembersData;
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
import javafx.scene.layout.HBox;

import java.util.List;

/** Admin's Members screen — warnings, blacklisting, and role changes, mirrors Admin\MemberController. */
public class AdminMembersController {
    @FXML private Label statusLabel;
    @FXML private TextField thresholdField;
    @FXML private TextField complianceField;
    @FXML private TextField blacklistDurationField;
    @FXML private TableView<AdminMember> membersTable;
    @FXML private TableColumn<AdminMember, String> nameColumn;
    @FXML private TableColumn<AdminMember, String> emailColumn;
    @FXML private TableColumn<AdminMember, String> roleColumn;
    @FXML private TableColumn<AdminMember, AdminMember> warningsColumn;
    @FXML private TableColumn<AdminMember, AdminMember> statusColumn;
    @FXML private TableColumn<AdminMember, AdminMember> actionsColumn;

    @FXML
    public void initialize() {
        nameColumn.setCellValueFactory(row -> new SimpleStringProperty(row.getValue().name));
        emailColumn.setCellValueFactory(row -> new SimpleStringProperty(row.getValue().email));
        roleColumn.setCellValueFactory(row -> new SimpleStringProperty(capitalize(row.getValue().role)));

        warningsColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        warningsColumn.setCellFactory(col -> new TableCell<>() {
            @Override
            protected void updateItem(AdminMember member, boolean empty) {
                super.updateItem(member, empty);
                if (empty || member == null) {
                    setGraphic(null);
                    return;
                }
                Label tag = new Label(member.warningCount + " / 2");
                tag.getStyleClass().add("app-tag");
                tag.getStyleClass().add(member.warningCount == 0 ? "app-tag-gray"
                        : member.warningCount == 1 ? "app-tag-orange" : "app-tag-red");
                setGraphic(tag);
            }
        });

        statusColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        statusColumn.setCellFactory(col -> new TableCell<>() {
            @Override
            protected void updateItem(AdminMember member, boolean empty) {
                super.updateItem(member, empty);
                if (empty || member == null) {
                    setGraphic(null);
                    return;
                }
                Label tag = new Label(member.blacklisted ? "Blacklisted" : "Active");
                tag.getStyleClass().add("app-tag");
                tag.getStyleClass().add(member.blacklisted ? "app-tag-red" : "app-tag-green");
                setGraphic(tag);
            }
        });

        actionsColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        actionsColumn.setCellFactory(col -> new TableCell<>() {
            private final Button warnButton = new Button("Warn");
            private final Button blacklistButton = new Button("Blacklist");
            private final ComboBox<String> roleCombo = new ComboBox<>(
                    javafx.collections.FXCollections.observableArrayList("student", "lecturer", "admin"));
            private final HBox box = new HBox(6, warnButton, blacklistButton, roleCombo);

            {
                warnButton.getStyleClass().add("app-btn-light");
                blacklistButton.getStyleClass().add("app-btn-light");
                warnButton.setOnAction(e -> warn(rowItem()));
                blacklistButton.setOnAction(e -> toggleBlacklist(rowItem()));
                roleCombo.setOnAction(e -> {
                    AdminMember member = rowItem();
                    if (member != null && roleCombo.getValue() != null && !roleCombo.getValue().equals(member.role)) {
                        changeRole(member, roleCombo.getValue());
                    }
                });
            }

            private AdminMember rowItem() {
                return getTableRow() == null ? null : getTableRow().getItem();
            }

            @Override
            protected void updateItem(AdminMember member, boolean empty) {
                super.updateItem(member, empty);
                if (empty || member == null) {
                    setGraphic(null);
                    return;
                }
                warnButton.setDisable(member.warningCount >= 2 || member.blacklisted);
                blacklistButton.setText(member.blacklisted ? "Reinstate" : "Blacklist");
                roleCombo.setValue(member.role);
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
                AdminMembersData data = Router.api().getAdminMembers();
                Platform.runLater(() -> render(data));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load members: " + describe(e)));
            }
        }).start();
    }

    private void render(AdminMembersData data) {
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data." : "");
        thresholdField.setText(String.valueOf(data.settings.inactivityThresholdDays));
        complianceField.setText(String.valueOf(data.settings.complianceDays));
        blacklistDurationField.setText(String.valueOf(data.settings.blacklistDurationDays));
        membersTable.getItems().setAll(data.members == null ? List.of() : data.members);
    }

    @FXML
    private void handleSaveSettings() {
        Integer threshold = parseIntOrNull(thresholdField.getText());
        Integer compliance = parseIntOrNull(complianceField.getText());
        Integer blacklistDuration = parseIntOrNull(blacklistDurationField.getText());

        if (threshold == null || compliance == null || blacklistDuration == null) {
            statusLabel.setText("All three policy fields must be whole numbers.");
            return;
        }

        statusLabel.setText("Saving...");
        new Thread(() -> {
            try {
                Router.api().updateModerationSettings(threshold, compliance, blacklistDuration);
                Platform.runLater(() -> statusLabel.setText("Moderation settings updated."));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to save settings: " + describe(e)));
            }
        }).start();
    }

    private void warn(AdminMember member) {
        if (member == null) {
            return;
        }
        statusLabel.setText("Warning...");
        new Thread(() -> {
            try {
                Router.api().warnMember(member.id);
                Platform.runLater(this::load);
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to warn member: " + describe(e)));
            }
        }).start();
    }

    private void toggleBlacklist(AdminMember member) {
        if (member == null) {
            return;
        }
        statusLabel.setText("Updating...");
        new Thread(() -> {
            try {
                if (member.blacklisted) {
                    Router.api().unblacklistMember(member.id);
                } else {
                    Router.api().blacklistMember(member.id);
                }
                Platform.runLater(this::load);
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to update member: " + describe(e)));
            }
        }).start();
    }

    private void changeRole(AdminMember member, String role) {
        statusLabel.setText("Updating role...");
        new Thread(() -> {
            try {
                Router.api().updateMemberRole(member.id, role);
                Platform.runLater(this::load);
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to change role: " + describe(e)));
            }
        }).start();
    }

    private Integer parseIntOrNull(String value) {
        try {
            return value == null ? null : Integer.valueOf(value.trim());
        } catch (NumberFormatException e) {
            return null;
        }
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
