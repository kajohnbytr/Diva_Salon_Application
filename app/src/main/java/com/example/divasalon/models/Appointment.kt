package com.example.divasalon.models

import com.google.gson.annotations.SerializedName

data class Appointment(
    @SerializedName("id") val id: Int = 0,
    @SerializedName("customer_id") val customerId: Int = 0,
    @SerializedName("stylist_id") val stylistId: String = "", // Changed to String to match Stylist model
    @SerializedName("stylist_name") val stylistName: String = "",
    @SerializedName("stylist_specialty") val stylistSpecialty: String = "",
    @SerializedName("appointment_date") val appointmentDate: String = "",
    @SerializedName("appointment_time") val appointmentTime: String = "",
    @SerializedName("service") val service: String = "",
    @SerializedName("status") val status: String = "Pending",
    @SerializedName("created_at") val createdAt: String = ""
)

data class AppointmentRequest(
    @SerializedName("customer_id") val customerId: Int,
    @SerializedName("stylist_id") val stylistId: String,
    @SerializedName("appointment_date") val appointmentDate: String,
    @SerializedName("appointment_time") val appointmentTime: String,
    @SerializedName("service") val service: String
)

data class AppointmentResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String,
    @SerializedName("appointment") val appointment: Appointment? = null
)

data class AppointmentsResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String,
    @SerializedName("appointments") val appointments: List<Appointment> = emptyList()
)