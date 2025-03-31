package com.example.divasalon.backend

import com.example.divasalon.models.AppointmentRequest
import com.example.divasalon.models.AppointmentResponse
import com.example.divasalon.models.AppointmentsResponse
import com.example.divasalon.models.PasswordResetRequest
import com.example.divasalon.models.Stylist
import com.example.divasalon.models.StylistResponse
import com.example.divasalon.models.User
import com.example.divasalon.models.VerifyOtpRequest
import okhttp3.ResponseBody
import retrofit2.Call
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.Headers
import retrofit2.http.POST
import retrofit2.http.Query

interface ApiService {


    @GET("checkEmailExists")
    fun checkEmailExists(@Query("email") email: String): Call<ResponseBody>

    @Headers("Content-Type: application/json")
    @POST("registerMobile.php")
    fun registerUser(@Body user: User): Call<ResponseBody>

    @Headers("Content-Type: application/json")
    @POST("loginMobile.php")
    fun loginUser(@Body user: User): Call<ResponseBody>

    @Headers("Content-Type: application/json")
    @GET("getsingle.php")
    fun getUser(@Query("email") email: String): Call<ResponseBody>

    @Headers("Content-Type: application/json")
    @GET("fetch_stylist.php")
    fun getStylists(): Call<StylistResponse>


    @Headers("Content-Type: application/json")
    @POST("setAppointment.php")
    fun createAppointment(@Body appointmentRequest: AppointmentRequest): Call<AppointmentResponse>

    @Headers("Content-Type: application/json")
    @GET("getAppointment.php")
    fun getUserAppointments(@Query("user_id") userId: Int): Call<AppointmentsResponse>

    @Headers("Content-Type: application/json")
    @POST("request_reset.php")
    fun requestPasswordReset(@Body user: User): Call<Map<String, Any>>

    @Headers("Content-Type: application/json")
    @POST("verify_otp.php")
    fun verifyOTP(@Body request: VerifyOtpRequest): Call<Map<String, Any>>

    @Headers("Content-Type: application/json")
    @POST("reset_password.php")
    fun resetPassword(@Body request: PasswordResetRequest): Call<Map<String, Any>>
}