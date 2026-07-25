package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class AdminMembersData {
    public ModerationSettings settings;
    public List<AdminMember> members;

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class ModerationSettings {
        public int inactivityThresholdDays;
        public int complianceDays;
        public int blacklistDurationDays;
    }
}
