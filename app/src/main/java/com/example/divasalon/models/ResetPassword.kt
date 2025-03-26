package com.example.divasalon.models

data class VerifyOtpRequest(
    val email: String,
    val otp: String
)

data class PasswordResetRequest(
    val email: String,
    val token: String,
    val new_password: String
)