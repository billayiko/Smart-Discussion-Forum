package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.Collections;
import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class QuizTakeData {
    public long id;
    public String title;
    public String subject;
    public int durationMinutes;
    public boolean proctored;
    public String endsAt;
    public List<QuizQuestion> questions = Collections.emptyList();
}
