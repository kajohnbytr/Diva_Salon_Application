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
import com.example.divasalon.activities.VerifyOTP
import com.example.divasalon.backend.ApiService
import com.example.divasalon.backend.RetrofitClient
import com.example.divasalon.models.User
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class ForgotPassword: AppCompatActivity() {

    private lateinit var emailEditText: EditText
    private lateinit var requestOtpButton: Button
    private lateinit var progressBar: ProgressBar
    private lateinit var apiService: ApiService

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_forgot_password)

        // Initialize API service
        apiService = RetrofitClient.instance.create(ApiService::class.java)

        // Initialize views
        emailEditText = findViewById(R.id.editTextEmail)
        requestOtpButton = findViewById(R.id.buttonRequestOTP)
        progressBar = findViewById(R.id.progressBar)

        // Set click listener for request OTP button
        requestOtpButton.setOnClickListener {
            val email = emailEditText.text.toString().trim()

            if (email.isEmpty()) {
                emailEditText.error = "Email is required"
                return@setOnClickListener
            }

            if (!android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
                emailEditText.error = "Please enter a valid email"
                return@setOnClickListener
            }

            requestOTP(email)
        }
    }

    private fun requestOTP(email: String) {
        progressBar.visibility = View.VISIBLE
        requestOtpButton.isEnabled = false

        val user = User(email = email, password = "", fullName = "", phone = 0 )
        val call = apiService.requestPasswordReset(user)

        call.enqueue(object : Callback<Map<String, Any>> {
            override fun onResponse(call: Call<Map<String, Any>>, response: Response<Map<String, Any>>) {
                progressBar.visibility = View.GONE
                requestOtpButton.isEnabled = true

                if (response.isSuccessful) {
                    val responseData = response.body()
                    val success = responseData?.get("success") as? Boolean ?: false
                    val message = responseData?.get("message") as? String ?: "Unknown error"

                    if (success) {
                        Toast.makeText(this@ForgotPassword, message, Toast.LENGTH_SHORT).show()
                        // Navigate to OTP verification screen
                        val intent = Intent(this@ForgotPassword, VerifyOTP::class.java)
                        intent.putExtra("EMAIL", email)
                        startActivity(intent)
                    } else {
                        Toast.makeText(this@ForgotPassword, message, Toast.LENGTH_LONG).show()
                    }
                } else {
                    Toast.makeText(this@ForgotPassword, "Server error: ${response.code()}", Toast.LENGTH_LONG).show()
                }
            }

            override fun onFailure(call: Call<Map<String, Any>>, t: Throwable) {
                progressBar.visibility = View.GONE
                requestOtpButton.isEnabled = true
                Toast.makeText(this@ForgotPassword, "Network error: ${t.message}", Toast.LENGTH_LONG).show()
            }
        })
    }
}