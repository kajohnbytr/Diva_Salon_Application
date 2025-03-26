package com.example.divasalon

import android.os.Bundle
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.navigation.fragment.NavHostFragment
import androidx.navigation.ui.setupWithNavController
import com.example.divasalon.databinding.ActivityBottomNavigationBinding
import com.example.loginandregister.UserViewModel
import com.google.android.material.bottomnavigation.BottomNavigationView

class BottomNavigation : AppCompatActivity() {

    private lateinit var binding: ActivityBottomNavigationBinding
    private val userViewModel: UserViewModel by viewModels()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        binding = ActivityBottomNavigationBinding.inflate(layoutInflater)
        setContentView(binding.root)

        val navView: BottomNavigationView = binding.navView

        val navHostFragment = supportFragmentManager.findFragmentById(R.id.nav_host_fragment_activity_bottom_navigation) as? NavHostFragment
        val navController = navHostFragment?.navController

        if (navController != null) {
            navView.setupWithNavController(navController)
        }

        // Get intent data
        val userName = intent.getStringExtra("USER_NAME") ?: "No name provided"
        val userEmail = intent.getStringExtra("USER_EMAIL") ?: "No email provided"

        // Pass data to ViewModel
        userViewModel.setUserData(userName, userEmail)
    }
}
