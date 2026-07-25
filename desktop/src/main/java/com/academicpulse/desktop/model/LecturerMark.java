package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

@JsonIgnoreProperties(ignoreUnknown = true)
public class LecturerMark {
    public String studentName;
    public String studentEmail;
    public long posts;
    public int participationScore;
    public long quizAttempts;
    public Integer quizAverage;
    public int combinedScore;
}
