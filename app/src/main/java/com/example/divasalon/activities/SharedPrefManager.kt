package com.example.divasalon.utils

import android.content.Context
import android.content.SharedPreferences

class SharedPrefManager private constructor(context: Context) {
    private val sharedPreferences: SharedPreferences = context.getSharedPreferences(SHARED_PREF_NAME, Context.MODE_PRIVATE)

    companion object {
        private const val SHARED_PREF_NAME = "diva_salon_pref"
        private const val KEY_USER_ID = "user_id"
        private const val KEY_USERNAME = "username"
        private const val KEY_EMAIL = "email"
        private const val KEY_NAME = "name"
        private const val KEY_IS_LOGGED_IN = "is_logged_in"

        @Volatile
        private var instance: SharedPrefManager? = null

        fun getInstance(context: Context): SharedPrefManager {
            return instance ?: synchronized(this) {
                instance ?: SharedPrefManager(context.applicationContext).also { instance = it }
            }
        }
    }

    fun saveUser(id: Int, username: String, email: String, name: String) {
        val editor = sharedPreferences.edit()
        editor.putInt(KEY_USER_ID, id)
        editor.putString(KEY_USERNAME, username)
        editor.putString(KEY_EMAIL, email)
        editor.putString(KEY_NAME, name)
        editor.putBoolean(KEY_IS_LOGGED_IN, true)
        editor.apply()
    }

    // Remove this problematic property that's causing the clash
    // val isLoggedIn = SharedPrefManager.getInstance(context.applicationContext).isLoggedIn()

    fun isLoggedIn(): Boolean {
        return sharedPreferences.getBoolean(KEY_IS_LOGGED_IN, false)
    }

    fun getUserId(): Int {
        return sharedPreferences.getInt(KEY_USER_ID, -1)
    }

    fun getUsername(): String? {
        return sharedPreferences.getString(KEY_USERNAME, null)
    }

    fun getEmail(): String? {
        return sharedPreferences.getString(KEY_EMAIL, null)
    }

    fun getName(): String? {
        return sharedPreferences.getString(KEY_NAME, null)
    }

    fun logout() {
        val editor = sharedPreferences.edit()
        editor.clear()
        editor.apply()
    }
}