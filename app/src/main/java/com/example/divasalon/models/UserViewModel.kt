package com.example.loginandregister

import android.content.Context
import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.example.divasalon.utils.SharedPrefManager

class UserViewModel : ViewModel() {
    private val _userName = MutableLiveData<String>()
    val userName: LiveData<String> = _userName

    private val _userEmail = MutableLiveData<String>()
    val userEmail: LiveData<String> = _userEmail

    fun setUserData(name: String, email: String) {
        _userName.value = name
        _userEmail.value = email
    }

    fun loadUserData(context: Context) {
        val prefManager = SharedPrefManager.getInstance(context)
        if (prefManager.isLoggedIn()) {
            _userName.value = prefManager.getName()
            _userEmail.value = prefManager.getEmail()
        }
    }
}
