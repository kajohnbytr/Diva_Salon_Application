package com.example.divasalon.models

data class HomeHorModel(
    var image: Int,
    var name: String,
    var services: List<Pair<String, String>> // List of service types and prices
)
