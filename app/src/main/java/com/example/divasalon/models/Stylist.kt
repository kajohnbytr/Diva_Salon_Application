package com.example.divasalon.models

import com.google.gson.annotations.SerializedName

data class  Stylist(
    @SerializedName("id") val id: String,
    @SerializedName("name") val name: String,
    @SerializedName("specialty") val specialty: String = "",
    @SerializedName("imageUrl") val imageUrl: String?,
    @SerializedName("isSelected") val isSelected: Boolean = false
)
