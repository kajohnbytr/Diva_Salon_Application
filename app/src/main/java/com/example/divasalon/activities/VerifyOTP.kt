package com.example.divasalon.activities

import android.content.Intent
import android.os.Bundle
import android.os.CountDownTimer
import android.view.View
import android.widget.Button
import android.widget.EditText
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.divasalon.R

import com.example.divasalon.backend.ApiService
import com.example.divasalon.backend.RetrofitClient
import com.example.divasalon.models.User
import com.example.divasalon.models.VerifyOtpRequest
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class VerifyOTP : AppCompatActivity() {

    private lateinit var emailTextView: TextView
    private lateinit var otpEditText: EditText
    private lateinit var timerTextView: TextView
    private lateinit var verifyButton: Button
    private lateinit var resendButton: Button
    private lateinit var progressBar: ProgressBar
    private lateinit var apiService: ApiService

    private lateinit var email: String
    private lateinit var countDownTimer: CountDownTimer

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_verify_otp)

        // Initialize API service
        apiService = RetrofitClient.instance.create(ApiService::class.java)

        // Get email from intent
        email = intent.getStringExtra("EMAIL") ?: ""
        if (email.isEmpty()) {
            Toast.makeText(this, "Email not provided", Toast.LENGTH_SHORT).show()
            finish()
            return
        }

        // Initialize views
        emailTextView = findViewById(R.id.textViewEmail)
        otpEditText = findViewById(R.id.editTextOTP)
        timerTextView = findViewById(R.id.textViewTimer)
        verifyButton = findViewById(R.id.buttonVerifyOTP)
        resendButton = findViewById(R.id.buttonResendOTP)
        progressBar = findViewById(R.id.progressBar)

        // Set email in TextView
        emailTextView.text = "Email: $email"

        // Start countdown timer
        startCountdownTimer()

        // Set click listeners
        verifyButton.setOnClickListener {
            val otp = otpEditText.text.toString().trim()

            if (otp.isEmpty()) {
                otpEditText.error = "OTP is required"
                return@setOnClickListener
            }

            if (otp.length != 6) {
                otpEditText.error = "Please enter a valid 6-digit OTP"
                return@setOnClickListener
            }

            verifyOTP(email, otp)
        }

        resendButton.setOnClickListener {
            resendOTP(email)
        }
    }

    private fun startCountdownTimer() {
        // 10 minutes in milliseconds
        val timerDuration = 10 * 60 * 1000L

        countDownTimer = object : CountDownTimer(timerDuration, 1000) {
            override fun onTick(millisUntilFinished: Long) {
                val minutes = millisUntilFinished / 60000
                val seconds = (millisUntilFinished % 60000) / 1000
                timerTextView.text = "Expires in: ${String.format("%02d:%02d", minutes, seconds)}"
            }

            override fun onFinish() {
                timerTextView.text = "OTP expired"
                resendButton.isEnabled = true
            }
        }.start()
    }

    private fun verifyOTP(email: String, otp: String) {
        progressBar.visibility = View.VISIBLE
        verifyButton.isEnabled = false

        val request = VerifyOtpRequest(email = email, otp = otp)
        val call = apiService.verifyOTP(request)

        call.enqueue(object : Callback<Map<String, Any>> {
            override fun onResponse(call: Call<Map<String, Any>>, response: Response<Map<String, Any>>) {
                progressBar.visibility = View.GONE
                verifyButton.isEnabled = true

                if (response.isSuccessful) {
                    val responseData = response.body()
                    val success = responseData?.get("success") as? Boolean ?: false
                    val message = responseData?.get("message") as? String ?: "Unknown error"

                    if (success) {
                        val token = responseData?.get("token") as? String ?: ""
                        Toast.makeText(this@VerifyOTP, message, Toast.LENGTH_SHORT).show()

                        // Navigate to Reset Password screen
                        val intent = Intent(this@VerifyOTP, ResetPassword::class.java)
                        intent.putExtra("EMAIL", email)
                        intent.putExtra("TOKEN", token)
                        startActivity(intent)
                        finish()
                    } else {
                        Toast.makeText(this@VerifyOTP, message, Toast.LENGTH_LONG).show()
                    }
                } else {
                    Toast.makeText(this@VerifyOTP,"Server error: ${response.code()}", Toast.LENGTH_LONG).show()
                }
            }

            override fun onFailure(call: Call<Map<String, Any>>, t: Throwable) {
                progressBar.visibility = View.GONE
                verifyButton.isEnabled = true
                Toast.makeText(this@VerifyOTP, "Network error: ${t.message}", Toast.LENGTH_LONG).show()
            }
        })
    }

    private fun resendOTP(email: String) {
        progressBar.visibility = View.VISIBLE
        resendButton.isEnabled = false

        val user = User(email = email, password = "", fullName = "", phone = 0)
        val call = apiService.requestPasswordReset(user)

        call.enqueue(object : Callback<Map<String, Any>> {
            override fun onResponse(call: Call<Map<String, Any>>, response: Response<Map<String, Any>>) {
                progressBar.visibility = View.GONE

                if (response.isSuccessful) {
                    val responseData = response.body()
                    val success = responseData?.get("success") as? Boolean ?: false
                    val message = responseData?.get("message") as? String ?: "Unknown error"

                    if (success) {
                        Toast.makeText(this@VerifyOTP, message, Toast.LENGTH_SHORT).show()
                        // Reset countdown timer
                        countDownTimer.cancel()
                        startCountdownTimer()
                        resendButton.isEnabled = false
                    } else {
                        resendButton.isEnabled = true
                        Toast.makeText(this@VerifyOTP, message, Toast.LENGTH_LONG).show()
                    }
                } else {
                    resendButton.isEnabled = true
                    Toast.makeText(this@VerifyOTP, "Server error: ${response.code()}", Toast.LENGTH_LONG).show()
                }
            }

            override fun onFailure(call: Call<Map<String, Any>>, t: Throwable) {
                progressBar.visibility = View.GONE
                resendButton.isEnabled = true
                Toast.makeText(this@VerifyOTP, "Network error: ${t.message}", Toast.LENGTH_LONG).show()
            }
        })
    }

    override fun onDestroy() {
        super.onDestroy()
        if (::countDownTimer.isInitialized) {
            countDownTimer.cancel()
        }
    }
}