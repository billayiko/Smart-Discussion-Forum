package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

/** An MCQ question. {@code correctOption} is null on the take-quiz screen (the API never sends it there). */
@JsonIgnoreProperties(ignoreUnknown = true)
public class QuizQuestion {
    public long id;
    public String question;
    public String optionA;
    public String optionB;
    public String optionC;
    public String optionD;
    public String correctOption;
}
