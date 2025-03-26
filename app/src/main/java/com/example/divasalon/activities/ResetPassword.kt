package com.example.divasalon.activities

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.Button
import android.widget.EditText
import android.widget.ProgressBar
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.divasalon.R
import com.example.divasalon.activities.Login
import com.example.divasalon.backend.ApiService
import com.example.divasalon.backend.RetrofitClient
import com.example.divasalon.models.PasswordResetRequest
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class ResetPassword : AppCompatActivity() {

    private lateinit var newPasswordEditText: EditText
    private lateinit var confirmPasswordEditText: EditText
    private lateinit var resetButton: Button
    private lateinit var progressBar: ProgressBar
    private lateinit var apiService: ApiService

    private lateinit var email: String
    private lateinit var token: String

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_reset_password)

        // Initialize API service
        apiService = RetrofitClient.instance.create(ApiService::class.java)

        // Get email and token from intent
        email = intent.getStringExtra("EMAIL") ?: ""
        token = intent.getStringExtra("TOKEN") ?: ""

        if (email.isEmpty() || token.isEmpty()) {
            Toast.makeText(this, "Invalid session. Please try again.", Toast.LENGTH_SHORT).show()
            finish()
            return
        }

        // Initialize views
        newPasswordEditText = findViewById(R.id.editTextNewPassword)
        confirmPasswordEditText = findViewById(R.id.editTextConfirmPassword)
        resetButton = findViewById(R.id.buttonResetPassword)
        progressBar = findViewById(R.id.progressBar)

        // Set click listener for reset button
        resetButton.setOnClickListener {
            val newPassword = newPasswordEditText.text.toString().trim()
            val confirmPassword = confirmPasswordEditText.text.toString().trim()

            if (newPassword.isEmpty()) {
                newPasswordEditText.error = "New password is required"
                return@setOnClickListener
            }

            if (newPassword.length < 6) {
                newPasswordEditText.error = "Password must be at least 6 characters"
                return@setOnClickListener
            }

            if (confirmPassword.isEmpty()) {
                confirmPasswordEditText.error = "Please confirm your password"
                return@setOnClickListener
            }

            if (newPassword != confirmPassword) {
                confirmPasswordEditText.error = "Passwords do not match"
                return@setOnClickListener
            }

            resetPassword(email, token, newPassword)
        }
    }

    private fun resetPassword(email: String, token: String, newPassword: String) {
        progressBar.visibility = View.VISIBLE
        resetButton.isEnabled = false

        val request = PasswordResetRequest(email = email, token = token, new_password = newPassword)
        val call = apiService.resetPassword(request)

        call.enqueue(object : Callback<Map<String, Any>> {
            override fun onResponse(call: Call<Map<String, Any>>, response: Response<Map<String, Any>>) {
                progressBar.visibility = View.GONE
                resetButton.isEnabled = true

                if (response.isSuccessful) {
                    val responseData = response.body()
                    val success = responseData?.get("success") as? Boolean ?: false
                    val message = responseData?.get("message") as? String ?: "Unknown error"

                    if (success) {
                        Toast.makeText(this@ResetPassword, message, Toast.LENGTH_SHORT).show()

                        // Navigate back to login screen
                        val intent = Intent(this@ResetPassword, Login::class.java)
                        intent.flags = Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_NEW_TASK
                        startActivity(intent)
                        finishAffinity() // Close all activities in the stack
                    } else {
                        Toast.makeText(this@ResetPassword, message, Toast.LENGTH_LONG).show()
                    }
                } else {
                    Toast.makeText(this@ResetPassword, "Server error: ${response.code()}", Toast.LENGTH_LONG).show()
                }
            }

            override fun onFailure(call: Call<Map<String, Any>>, t: Throwable) {
                progressBar.visibility = View.GONE
                resetButton.isEnabled = true
                Toast.makeText(this@ResetPassword, "Network error: ${t.message}", Toast.LENGTH_LONG).show()
            }
        })
    }
}