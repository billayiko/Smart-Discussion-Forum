package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

@JsonIgnoreProperties(ignoreUnknown = true)
public class QuizSummary {
    public long id;
    public String title;
    public String subject;
    public String stage;
    public long questionsCount;
    public long totalQuestions;
    public String scheduledAt;
    public int durationMinutes;
    public boolean hasStarted;
    public boolean isEditable;
    public boolean isFinalized;
    public boolean marksConfirmed;
}
