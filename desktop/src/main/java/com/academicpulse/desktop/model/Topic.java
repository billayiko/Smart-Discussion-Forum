package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

@JsonIgnoreProperties(ignoreUnknown = true)
public class Topic {
    public long id;
    public String title;
    public String description;
    public long questionsCount;
    public long subscribersCount;

    // Only populated on analytics responses.
    public User lecturer;

    @Override
    public String toString() {
        return title;
    }
}
