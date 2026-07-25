package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.Collections;
import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class QuizBuilderData {
    public long id;
    public String title;
    public String subject;
    public int durationMinutes;
    public String scheduledAt;
    public int totalQuestions;
    public String questionsFinalizedAt;
    public List<QuizQuestion> questions = Collections.emptyList();
}
