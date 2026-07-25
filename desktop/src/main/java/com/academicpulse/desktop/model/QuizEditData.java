package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

@JsonIgnoreProperties(ignoreUnknown = true)
public class QuizEditData {
    public long id;
    public String title;
    public String subject;
    public int totalQuestions;
    public String scheduledAt;
    public int durationMinutes;
    public String status;
    public Long courseTopicId;
    public boolean proctored;
    public boolean isFinalized;
}
