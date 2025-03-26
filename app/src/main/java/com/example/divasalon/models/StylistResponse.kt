package com.example.divasalon.models

import com.google.gson.annotations.SerializedName

data class StylistResponse(
    @SerializedName("status") val status: String,
    @SerializedName("Stylist") val Stylist: List<Stylist>
)

