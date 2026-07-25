package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

@JsonIgnoreProperties(ignoreUnknown = true)
public class AdminTopic {
    public long id;
    public String title;
    public String description;
    public Long lecturerId;
    public String lecturerName;
    public long subscribersCount;

    @Override
    public String toString() {
        return title;
    }
}
