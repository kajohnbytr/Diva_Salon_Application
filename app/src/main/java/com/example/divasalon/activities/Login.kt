package com.example.divasalon.activities

import android.content.Intent
import android.os.Bundle
import android.text.method.HideReturnsTransformationMethod
import android.text.method.PasswordTransformationMethod
import android.util.Log
import android.view.View
import android.widget.Button
import android.widget.EditText
import android.widget.ImageView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.divasalon.BottomNavigation
import com.example.divasalon.R
import com.example.divasalon.backend.ApiService
import com.example.divasalon.backend.RetrofitClient
import com.example.divasalon.models.User
import com.example.divasalon.utils.SharedPrefManager
import okhttp3.ResponseBody
import org.json.JSONObject
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class Login : AppCompatActivity() {

    private lateinit var emailEditText: EditText
    private lateinit var passwordEditText: EditText
    private lateinit var submitButton: Button
    private lateinit var eyeIcon: ImageView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)

        // Initialize UI elements
        emailEditText = findViewById(R.id.email)
        passwordEditText = findViewById(R.id.password)
        submitButton = findViewById(R.id.btnSubmit)
        eyeIcon = findViewById(R.id.eyeIcon)

        eyeIcon.setOnClickListener {
            togglePasswordVisibility()
        }

        // Set click listener for submit button
        submitButton.setOnClickListener {
            validateAndLogin()
        }
    }
    private fun togglePasswordVisibility() {
        // Check the current input type to toggle between password visibility and hiding
        if (passwordEditText.transformationMethod == PasswordTransformationMethod.getInstance()) {
            // Show password
            passwordEditText.transformationMethod = HideReturnsTransformationMethod.getInstance()
            eyeIcon.setImageResource(R.drawable.eye_icon)
        } else {
            // Hide password
            passwordEditText.transformationMethod = PasswordTransformationMethod.getInstance()
            eyeIcon.setImageResource(R.drawable.eye_icon_closed)  // Set the eye icon to hidden
        }
        // Move the cursor to the end of the password field after changing visibility
        passwordEditText.setSelection(passwordEditText.text.length)
    }

    private fun validateAndLogin() {
        val email = emailEditText.text.toString().trim()
        val password = passwordEditText.text.toString().trim()

        // Basic validation
        if (email.isEmpty()) {
            Toast.makeText(this, "Username is required", Toast.LENGTH_LONG).show()
            return
        }
        if (!android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
            Toast.makeText(this, "Please enter a valid email", Toast.LENGTH_LONG).show()
            return
        }

        if (password.isEmpty()) {
            Toast.makeText(this, "Password is required", Toast.LENGTH_LONG).show()
            return
        }


        // Create User object with login credentials
        val loginUser = User(
            fullName = "",  // Not needed for login
            email = email,
            phone = 0,      // Not needed for login
            password = password
        )

        // Attempt login
        loginUser(loginUser)
    }

    private fun loginUser(loginUser: User) {
        // Show loading indicator or disable button
        submitButton.isEnabled = false

        // Get API service instance
        val apiService = RetrofitClient.instance.create(ApiService::class.java)

        // Make API call using the new loginUser method
        val call = apiService.loginUser(loginUser)
        call.enqueue(object : Callback<ResponseBody> {
            override fun onResponse(call: Call<ResponseBody>, response: Response<ResponseBody>) {
                submitButton.isEnabled = true

                if (response.isSuccessful) {
                    try {
                        val responseString = response.body()?.string() ?: ""
                        Log.d("LOGIN_RESPONSE", "Response: $responseString")

                        val jsonResponse = JSONObject(responseString)

                        // Assuming your API returns success status and message
                        val success = jsonResponse.optBoolean("success", false)
                        val message = jsonResponse.optString("message", "")
                        val userId = jsonResponse.optInt("user_id", -1) // Extract user_id here!

                        Log.d("LOGIN", "Extracted user_id: $userId") // Log for debugging

                        if (success) {
                            // Save user ID immediately
                            val sharedPrefManager = SharedPrefManager.getInstance(this@Login)
                            // Temporarily save with minimal info, will update after fetching details
                            sharedPrefManager.saveUser(
                                id = userId,
                                username = loginUser.email, // Use email as temporary username
                                email = loginUser.email,
                                name = ""  // Will be updated after fetching details
                            )

                            // Handle successful login
                            Toast.makeText(
                                this@Login,
                                "Login successful!",
                                Toast.LENGTH_LONG
                            ).show()

                            // Now fetch user details using email
                            fetchUserDetails(loginUser.email, userId)
                        } else  {
                            // Handle login failure
                            Toast.makeText(
                                this@Login,
                                message.ifEmpty { "Invalid credentials" },
                                Toast.LENGTH_LONG
                            ).show()
                        }
                    } catch (e: Exception) {
                        // Handle parsing error
                        Log.e("LOGIN_ERROR", "Parse error: ${e.message}")
                        Toast.makeText(
                            this@Login,
                            "Error processing response. Please try again.",
                            Toast.LENGTH_LONG
                        ).show()
                    }
                } else {
                    // Handle error
                    Log.e("LOGIN_ERROR", "Error: ${response.code()}")
                    Toast.makeText(
                        this@Login,
                        "Login failed. Please try again.",
                        Toast.LENGTH_LONG
                    ).show()
                }
            }

            override fun onFailure(call: Call<ResponseBody>, t: Throwable) {
                submitButton.isEnabled = true

                // Handle failure
                Log.e("LOGIN_ERROR", "Failure: ${t.message}")
                Toast.makeText(
                    this@Login,
                    "Network error. Please check your connection.",
                    Toast.LENGTH_LONG
                ).show()
            }
        })
    }

    private fun fetchUserDetails(email: String, userId: Int) {
        // Get API service instance
        val apiService = RetrofitClient.instance.create(ApiService::class.java)

        // Make API call to get user details
        val call = apiService.getUser(email)
        call.enqueue(object : Callback<ResponseBody> {
            override fun onResponse(call: Call<ResponseBody>, response: Response<ResponseBody>) {
                if (response.isSuccessful) {
                    try {
                        val responseString = response.body()?.string() ?: ""
                        Log.d("USER_DETAILS_RESPONSE", "Response: $responseString")

                        val jsonResponse = JSONObject(responseString)

                        // Extract user details
                        var fullName = ""
                        var username = ""

                        if (jsonResponse.has("user")) {
                            val userObject = jsonResponse.getJSONObject("user")
                            username = userObject.optString("username", email)  // If no username, use email
                            fullName = userObject.optString("customer_name", "")
                        }

                        // IMPORTANT: Use the userId parameter that was passed in
                        SharedPrefManager.getInstance(this@Login).saveUser(
                            id = userId,  // Use the passed userId instead of extracting again
                            username = username,
                            email = email,
                            name = fullName
                        )

                        // Log the data to verify
                        Log.d("USER_DETAILS", "Email: $email, Name: $fullName, ID: $userId")

                        // Navigate to BottomViewNavigation activity with user data
                        val intent = Intent(this@Login, BottomNavigation::class.java)
                        intent.putExtra("USER_EMAIL", email)
                        intent.putExtra("USER_NAME", fullName)
                        startActivity(intent)
                        finish()

                    } catch (e: Exception) {
                        // Handle parsing error
                        Log.e("USER_DETAILS_ERROR", "Parse error: ${e.message}")
                        e.printStackTrace()

                        // Still navigate but with limited data
                        val intent = Intent(this@Login, BottomNavigation::class.java)
                        intent.putExtra("USER_EMAIL", email)
                        startActivity(intent)
                        finish()
                    }
                } else {
                    // Handle error but still navigate
                    Log.e("USER_DETAILS_ERROR", "Error: ${response.code()}")

                    // Still navigate but with limited data
                    val intent = Intent(this@Login, BottomNavigation::class.java)
                    intent.putExtra("USER_EMAIL", email)
                    startActivity(intent)
                    finish()
                }
            }

            override fun onFailure(call: Call<ResponseBody>, t: Throwable) {
                // Handle failure but still navigate
                Log.e("USER_DETAILS_ERROR", "Failure: ${t.message}")

                // Still navigate but with limited data
                val intent = Intent(this@Login, BottomNavigation::class.java)
                intent.putExtra("USER_EMAIL", email)
                startActivity(intent)
                finish()
            }
        })
    }

    fun register(view: View) {
        val intent = Intent(this, Register::class.java)
        startActivity(intent)
    }

    fun forgot_password(view: View) {
        val intent = Intent(this,ForgotPassword::class.java)
        startActivity(intent)
    }
}