package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.QuizSummary;
import com.academicpulse.desktop.model.QuizzesData;
import com.academicpulse.desktop.util.FriendlyDate;
import javafx.application.Platform;
import javafx.beans.property.SimpleObjectProperty;
import javafx.beans.property.SimpleStringProperty;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.TableCell;
import javafx.scene.control.TableColumn;
import javafx.scene.control.TableView;
import javafx.scene.layout.FlowPane;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;
import javafx.stage.FileChooser;

import java.nio.file.Path;
import java.util.List;

/** Lecturer's quiz list — mirrors quizzes/index.blade.php. */
public class QuizzesController {
    @FXML private Label statusLabel;
    @FXML private FlowPane statsBox;
    @FXML private TableView<QuizSummary> quizzesTable;
    @FXML private TableColumn<QuizSummary, String> titleColumn;
    @FXML private TableColumn<QuizSummary, String> subjectColumn;
    @FXML private TableColumn<QuizSummary, QuizSummary> statusColumn;
    @FXML private TableColumn<QuizSummary, String> questionsColumn;
    @FXML private TableColumn<QuizSummary, String> scheduledColumn;
    @FXML private TableColumn<QuizSummary, QuizSummary> marksColumn;
    @FXML private TableColumn<QuizSummary, QuizSummary> actionsColumn;

    @FXML
    public void initialize() {
        titleColumn.setCellValueFactory(row -> new SimpleStringProperty(row.getValue().title));
        subjectColumn.setCellValueFactory(row -> new SimpleStringProperty(row.getValue().subject));
        questionsColumn.setCellValueFactory(row -> new SimpleStringProperty(row.getValue().questionsCount + " / " + row.getValue().totalQuestions));
        scheduledColumn.setCellValueFactory(row -> new SimpleStringProperty(FriendlyDate.format(row.getValue().scheduledAt)));

        statusColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        statusColumn.setCellFactory(col -> new TableCell<>() {
            @Override
            protected void updateItem(QuizSummary quiz, boolean empty) {
                super.updateItem(quiz, empty);
                if (empty || quiz == null) {
                    setGraphic(null);
                    return;
                }
                Label tag = new Label(capitalize(quiz.stage));
                tag.getStyleClass().add("app-tag");
                tag.getStyleClass().add(switch (quiz.stage == null ? "" : quiz.stage) {
                    case "active" -> "app-tag-green";
                    case "due_soon" -> "app-tag-orange";
                    case "draft" -> "app-tag-gray";
                    default -> "app-tag-purple";
                });
                setGraphic(tag);
            }
        });

        marksColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        marksColumn.setCellFactory(col -> new TableCell<>() {
            @Override
            protected void updateItem(QuizSummary quiz, boolean empty) {
                super.updateItem(quiz, empty);
                if (empty || quiz == null) {
                    setGraphic(null);
                    return;
                }
                if (!quiz.hasStarted) {
                    Label dash = new Label("—");
                    dash.setStyle("-fx-text-fill: #71717a;");
                    setGraphic(dash);
                    return;
                }
                Label tag = new Label(quiz.marksConfirmed ? "Confirmed" : "Confirm marks");
                tag.getStyleClass().add("app-tag");
                tag.getStyleClass().add(quiz.marksConfirmed ? "app-tag-green" : "app-tag-orange");
                tag.setOnMouseClicked(e -> openResult(quiz));
                tag.setStyle(tag.getStyle() + "-fx-cursor: hand;");
                setGraphic(tag);
            }
        });

        actionsColumn.setCellValueFactory(row -> new SimpleObjectProperty<>(row.getValue()));
        actionsColumn.setCellFactory(col -> new TableCell<>() {
            private final Button editButton = new Button("Edit");
            private final Button questionsButton = new Button("Questions");
            private final HBox box = new HBox(6, editButton, questionsButton);

            {
                editButton.getStyleClass().add("app-btn-light");
                questionsButton.getStyleClass().add("app-btn-light");
                editButton.setOnAction(e -> openEdit(rowItem()));
                questionsButton.setOnAction(e -> openBuilder(rowItem()));
            }

            private QuizSummary rowItem() {
                return getTableRow() == null ? null : getTableRow().getItem();
            }

            @Override
            protected void updateItem(QuizSummary quiz, boolean empty) {
                super.updateItem(quiz, empty);
                if (empty || quiz == null) {
                    setGraphic(null);
                    return;
                }
                editButton.setVisible(quiz.isEditable);
                editButton.setManaged(quiz.isEditable);
                questionsButton.setText(quiz.isFinalized ? "View questions" : "Add questions");
                setGraphic(box);
            }
        });

        load();
    }

    @FXML
    private void handleRefresh() {
        load();
    }

    @FXML
    private void handleCreateQuiz() {
        try {
            Router.navigate("/quiz-create.fxml", "Academic Pulse - Create Quiz");
        } catch (Exception e) {
            statusLabel.setText("Failed to open create quiz screen: " + describe(e));
        }
    }

    @FXML
    private void handleImportCsv() {
        FileChooser chooser = new FileChooser();
        chooser.setTitle("Choose a quiz CSV file");
        chooser.getExtensionFilters().add(new FileChooser.ExtensionFilter("CSV files", "*.csv", "*.txt", "*.xlsx", "*.xls"));
        java.io.File file = chooser.showOpenDialog(quizzesTable.getScene().getWindow());
        if (file == null) {
            return;
        }
        Path csvPath = file.toPath();
        statusLabel.setText("Importing...");
        new Thread(() -> {
            try {
                String message = Router.api().importQuizzesCsv(csvPath);
                Platform.runLater(() -> {
                    statusLabel.setText(message);
                    load();
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to import: " + describe(e)));
            }
        }).start();
    }

    private void load() {
        statusLabel.setText("Loading...");
        new Thread(() -> {
            try {
                QuizzesData data = Router.api().getQuizzes();
                Platform.runLater(() -> render(data));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load quizzes: " + describe(e)));
            }
        }).start();
    }

    private void render(QuizzesData data) {
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data." : "");

        statsBox.getChildren().setAll(
                statCard("Active quizzes", String.valueOf(data.stats.activeCount)),
                statCard("Published this week", String.valueOf(data.stats.publishedThisWeek))
        );

        List<QuizSummary> quizzes = data.quizzes;
        quizzesTable.getItems().setAll(quizzes == null ? List.of() : quizzes);
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

    private void openEdit(QuizSummary quiz) {
        if (quiz == null) {
            return;
        }
        try {
            QuizEditController controller = Router.navigate("/quiz-edit.fxml", "Academic Pulse - Edit Quiz");
            controller.setQuizId(quiz.id);
        } catch (Exception e) {
            statusLabel.setText("Failed to open edit screen: " + describe(e));
        }
    }

    private void openBuilder(QuizSummary quiz) {
        if (quiz == null) {
            return;
        }
        try {
            QuizBuilderController controller = Router.navigate("/quiz-builder.fxml", "Academic Pulse - " + quiz.title);
            controller.setQuizId(quiz.id);
        } catch (Exception e) {
            statusLabel.setText("Failed to open question builder: " + describe(e));
        }
    }

    private void openResult(QuizSummary quiz) {
        if (quiz == null) {
            return;
        }
        try {
            QuizResultController controller = Router.navigate("/quiz-result.fxml", "Academic Pulse - " + quiz.title + " Result");
            controller.setQuizId(quiz.id);
        } catch (Exception e) {
            statusLabel.setText("Failed to open result screen: " + describe(e));
        }
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
