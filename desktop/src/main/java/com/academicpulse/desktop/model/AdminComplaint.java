package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

@JsonIgnoreProperties(ignoreUnknown = true)
public class AdminComplaint {
    public long id;
    public String reason;
    public String status;
    public String reporterName;
    public Long questionId;
    public String questionTitle;
    public String questionAuthor;
    public String createdAt;
}
