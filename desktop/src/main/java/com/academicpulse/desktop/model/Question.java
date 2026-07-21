package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.Collections;
import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class Question {
    public long id;
    public String title;
    public String body;
    public long views;
    public String createdAt;
    public String updatedAt;
    public long answersCount;
    public User user;
    public Topic topic;
    public List<Answer> answers = Collections.emptyList();

    @Override
    public String toString() {
        return title;
    }
}
