package com.example.divasalon.models

import com.google.gson.annotations.SerializedName
data class User(
    @SerializedName("customer_name") val fullName: String,
    @SerializedName("email") val email: String,
    @SerializedName("phone") val phone: Int,
    @SerializedName("password") val password: String
)



