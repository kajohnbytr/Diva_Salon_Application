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
import com.example.divasalon.R
import com.example.divasalon.backend.ApiService
import com.example.divasalon.backend.RetrofitClient
import com.example.divasalon.models.User
import okhttp3.ResponseBody
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class Register : AppCompatActivity() {

    private lateinit var nameEditText: EditText
    private lateinit var emailEditText: EditText
    private lateinit var phoneEditText: EditText
    private lateinit var passwordEditText: EditText
    private lateinit var conpasswordEditText: EditText

    private lateinit var submitButton: Button
    private lateinit var eyeIcon: ImageView
    private lateinit var eyeIcon2: ImageView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_register)

        // Initialize UI elements
        nameEditText = findViewById(R.id.name)
        emailEditText = findViewById(R.id.email)
        phoneEditText = findViewById(R.id.password)
        passwordEditText = findViewById(R.id.pass)
        conpasswordEditText = findViewById(R.id.conpass)
        submitButton = findViewById(R.id.btnSubmit)
        eyeIcon = findViewById(R.id.eyeIcon2)
        eyeIcon2 = findViewById(R.id.eyeIcon3)

        submitButton.setOnClickListener {
            validateAndSubmitForm()
        }

        // Eye Icon for password
        eyeIcon.setOnClickListener {
            togglePasswordVisibility()
        }

        // Eye Icon for confirm password
        eyeIcon2.setOnClickListener {
            toggleConfirmPasswordVisibility()
        }
    }

    private fun validateAndSubmitForm() {
        val name = nameEditText.text.toString().trim()
        val email = emailEditText.text.toString().trim()
        val phoneStr = phoneEditText.text.toString().trim()
        val password = passwordEditText.text.toString().trim()
        val confirmPassword = conpasswordEditText.text.toString().trim()

        // Basic validation with Toast feedback
        if (name.isEmpty()) {
            Toast.makeText(this, "Username is required", Toast.LENGTH_LONG).show()
            return
        }

        if (email.isEmpty()) {
            Toast.makeText(this, "Email is required", Toast.LENGTH_LONG).show()
            return
        }

        if (!android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
            Toast.makeText(this, "Please enter a valid email", Toast.LENGTH_LONG).show()
            return
        }

        if (phoneStr.isEmpty()) {
            Toast.makeText(this, "Phone number is required", Toast.LENGTH_LONG).show()
            return
        }

        if (password.isEmpty()) {
            Toast.makeText(this, "Password is required", Toast.LENGTH_LONG).show()
            return
        }

        if (password.length < 6) {
            Toast.makeText(this, "Password must be at least 6 characters", Toast.LENGTH_LONG).show()
            return
        }

        if (confirmPassword != password) {
            Toast.makeText(this, "Passwords don't match", Toast.LENGTH_LONG).show()
            return
        }

        // Convert phone to integer safely
        val phone = try {
            phoneStr.toInt()
        } catch (e: NumberFormatException) {
            Toast.makeText(this, "Invalid phone number", Toast.LENGTH_LONG).show()
            return
        }

        // Create User object
        val user = User(fullName = name, email = email, phone = phone, password = password)

        // Check if email already exists
        checkIfEmailExists(email) { emailExists ->
            if (emailExists) {
                Toast.makeText(this, "Email already exists", Toast.LENGTH_LONG).show()
            } else {
                submitUserData(user)
            }
        }
    }

    private fun togglePasswordVisibility() {
        if (passwordEditText.transformationMethod == PasswordTransformationMethod.getInstance()) {
            passwordEditText.transformationMethod = HideReturnsTransformationMethod.getInstance()
            eyeIcon.setImageResource(R.drawable.password_ic)  // Set the eye icon to visible
        } else {
            passwordEditText.transformationMethod = PasswordTransformationMethod.getInstance()
            eyeIcon.setImageResource(R.drawable.eye_icon_closed)  // Set the eye icon to hidden
        }
        passwordEditText.setSelection(passwordEditText.text.length)
    }

    private fun toggleConfirmPasswordVisibility() {
        if (conpasswordEditText.transformationMethod == PasswordTransformationMethod.getInstance()) {
            conpasswordEditText.transformationMethod = HideReturnsTransformationMethod.getInstance()
            eyeIcon2.setImageResource(R.drawable.password_ic)  // Set the eye icon to visible
        } else {
            conpasswordEditText.transformationMethod = PasswordTransformationMethod.getInstance()
            eyeIcon2.setImageResource(R.drawable.eye_icon_closed)  // Set the eye icon to hidden
        }
        conpasswordEditText.setSelection(conpasswordEditText.text.length)
    }

    private fun checkIfEmailExists(email: String, callback: (Boolean) -> Unit) {
        val apiService = RetrofitClient.instance.create(ApiService::class.java)
        val call = apiService.checkEmailExists(email)
        call.enqueue(object : Callback<ResponseBody> {
            override fun onResponse(call: Call<ResponseBody>, response: Response<ResponseBody>) {
                if (response.isSuccessful) {
                    callback(false)
                } else {
                    callback(true)
                }
            }

            override fun onFailure(call: Call<ResponseBody>, t: Throwable) {
                Log.e("API_ERROR", "Failure: ${t.message}")
                Toast.makeText(this@Register, "Network error. Please check your connection.", Toast.LENGTH_LONG).show()
                callback(false) // Assume email doesn't exist in case of failure
            }
        })
    }

    private fun submitUserData(user: User) {
        submitButton.isEnabled = false
        val apiService = RetrofitClient.instance.create(ApiService::class.java)
        val call = apiService.registerUser(user)
        call.enqueue(object : Callback<ResponseBody> {
            override fun onResponse(call: Call<ResponseBody>, response: Response<ResponseBody>) {
                submitButton.isEnabled = true
                if (response.isSuccessful) {
                    Toast.makeText(this@Register, "Registration successful!", Toast.LENGTH_LONG).show()
                    clearFormFields()
                    val intent = Intent(this@Register, Login::class.java)
                    startActivity(intent)
                    finish()
                } else {
                    Log.e("API_ERROR", "Error: ${response.code()}")
                    Toast.makeText(this@Register, "Registration failed. Please try again.", Toast.LENGTH_LONG).show()
                }
            }

            override fun onFailure(call: Call<ResponseBody>, t: Throwable) {
                submitButton.isEnabled = true
                Log.e("API_ERROR", "Failure: ${t.message}")
                Toast.makeText(this@Register, "Network error. Please check your connection.", Toast.LENGTH_LONG).show()
            }
        })
    }

    private fun clearFormFields() {
        nameEditText.text.clear()
        emailEditText.text.clear()
        phoneEditText.text.clear()
        passwordEditText.text.clear()
        conpasswordEditText.text.clear()
        nameEditText.requestFocus()
    }

    fun Login(view: View) {
        val intent = Intent(this, Login::class.java)
        startActivity(intent)
    }
}
