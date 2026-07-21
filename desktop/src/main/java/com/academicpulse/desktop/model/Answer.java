package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

@JsonIgnoreProperties(ignoreUnknown = true)
public class Answer {
    public long id;
    public String body;
    public String createdAt;
    public User user;
}
