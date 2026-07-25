package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class AdminTopicsData {
    public List<AdminTopic> topics;
    public List<User> lecturers;
}
