package com.academicpulse.desktop.util;

import java.time.Duration;
import java.time.OffsetDateTime;
import java.time.format.DateTimeParseException;

/** Mirrors Carbon's diffForHumans() closely enough for display purposes. */
public final class RelativeTime {
    private RelativeTime() {
    }

    public static String ago(String isoTimestamp) {
        if (isoTimestamp == null || isoTimestamp.isBlank()) {
            return "";
        }
        try {
            OffsetDateTime then = OffsetDateTime.parse(isoTimestamp);
            Duration diff = Duration.between(then, OffsetDateTime.now());
            long seconds = Math.abs(diff.getSeconds());
            boolean future = diff.isNegative();

            String phrase;
            if (seconds < 45) {
                phrase = "a few seconds";
            } else if (seconds < 90) {
                phrase = "a minute";
            } else if (seconds < 45 * 60) {
                phrase = (seconds / 60) + " minutes";
            } else if (seconds < 90 * 60) {
                phrase = "an hour";
            } else if (seconds < 22 * 3600) {
                phrase = (seconds / 3600) + " hours";
            } else if (seconds < 36 * 3600) {
                phrase = "a day";
            } else if (seconds < 25 * 24 * 3600) {
                phrase = (seconds / (24 * 3600)) + " days";
            } else if (seconds < 45 * 24 * 3600) {
                phrase = "a month";
            } else if (seconds < 320 * 24 * 3600) {
                phrase = (seconds / (30 * 24 * 3600)) + " months";
            } else if (seconds < 548 * 24 * 3600) {
                phrase = "a year";
            } else {
                phrase = (seconds / (365 * 24 * 3600)) + " years";
            }

            return future ? "in " + phrase : phrase + " ago";
        } catch (DateTimeParseException e) {
            return "";
        }
    }
}
