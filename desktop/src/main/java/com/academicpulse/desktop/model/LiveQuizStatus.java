package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

@JsonIgnoreProperties(ignoreUnknown = true)
public class LiveQuizStatus {
    public LiveQuizRef quiz;

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class LiveQuizRef {
        public long id;
        public String title;
    }
}
