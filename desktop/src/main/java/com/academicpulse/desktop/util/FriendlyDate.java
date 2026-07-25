package com.academicpulse.desktop.util;

import java.time.OffsetDateTime;
import java.time.format.DateTimeFormatter;
import java.time.format.DateTimeParseException;

/** Formats an ISO-8601 timestamp the way the web app displays it (e.g. "MMM d, yyyy h:mm a"). */
public final class FriendlyDate {
    private static final DateTimeFormatter FORMATTER = DateTimeFormatter.ofPattern("MMM d, yyyy h:mm a");

    private FriendlyDate() {
    }

    public static String format(String iso) {
        if (iso == null || iso.isBlank()) {
            return "—";
        }
        try {
            return OffsetDateTime.parse(iso).format(FORMATTER);
        } catch (DateTimeParseException e) {
            return iso;
        }
    }
}
