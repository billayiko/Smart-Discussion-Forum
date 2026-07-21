package com.academicpulse.desktop.api;

/** Thrown when the API responds with a non-2xx status. */
public class ApiException extends Exception {
    public final int statusCode;

    public ApiException(String message, int statusCode) {
        super(message);
        this.statusCode = statusCode;
    }
}
