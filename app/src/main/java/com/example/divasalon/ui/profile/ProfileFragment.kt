package com.example.divasalon.ui.profile

import android.content.Intent
import android.os.Bundle
import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.LinearLayout
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.cardview.widget.CardView
import androidx.core.content.ContextCompat
import androidx.fragment.app.Fragment
import androidx.fragment.app.activityViewModels
import com.example.divasalon.R
import com.example.divasalon.activities.Login
import com.example.divasalon.backend.ApiService
import com.example.divasalon.backend.RetrofitClient
import com.example.divasalon.databinding.FragmentProfileBinding
import com.example.divasalon.models.Appointment
import com.example.divasalon.models.AppointmentsResponse
import com.example.divasalon.utils.SharedPrefManager
import com.example.loginandregister.UserViewModel
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response
import java.text.SimpleDateFormat
import java.util.Calendar
import java.util.Date
import java.util.Locale

class ProfileFragment : Fragment() {

    private var _binding: FragmentProfileBinding? = null
    private val binding get() = _binding!!
    private val userViewModel: UserViewModel by activityViewModels()

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentProfileBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        // Observe the LiveData from the ViewModel
        userViewModel.userName.observe(viewLifecycleOwner) { name ->
            binding.fullName.text = name.ifEmpty { "No name provided" }
            Log.d("ProfileFragment", "Name updated: $name")
        }

        userViewModel.userEmail.observe(viewLifecycleOwner) { email ->
            binding.tvemail.text = email.ifEmpty { "No email provided" }
            Log.d("ProfileFragment", "Email updated: $email")
        }

        // Set up logout button with confirmation dialog
        binding.imageButton.setOnClickListener {
            // Show confirmation dialog
            AlertDialog.Builder(requireContext())
                .setTitle("Logout Confirmation")
                .setMessage("Are you sure you want to logout?")
                .setPositiveButton("Yes") { dialog, _ ->
                    // Perform logout
                    SharedPrefManager.getInstance(requireContext()).logout()
                    val intent = Intent(requireContext(), Login::class.java)
                    intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
                    startActivity(intent)
                    dialog.dismiss()
                }
                .setNegativeButton("No") { dialog, _ ->
                    // Cancel logout
                    dialog.dismiss()
                }
                .show()
        }

        // Fetch appointment history
        fetchAppointmentHistory()
    }


    private fun fetchAppointmentHistory() {
        val userId = SharedPrefManager.getInstance(requireContext()).getUserId()
        if (userId == -1) {
            Toast.makeText(requireContext(), "User not logged in", Toast.LENGTH_SHORT).show()
            return
        }

        val apiService = RetrofitClient.instance.create(ApiService::class.java)
        apiService.getUserAppointments(userId).enqueue(object : Callback<AppointmentsResponse> {
            override fun onResponse(call: Call<AppointmentsResponse>, response: Response<AppointmentsResponse>) {
                if (response.isSuccessful && response.body()?.success == true) {
                    val appointments = response.body()?.appointments ?: emptyList()
                    if (appointments.isNotEmpty()) {
                        displayAppointmentsByMonth(appointments)
                    } else {
                        showNoAppointmentsMessage()
                    }
                } else {
                    Toast.makeText(requireContext(), "Failed to load appointments", Toast.LENGTH_SHORT).show()
                    Log.e("ProfileFragment", "Error response: ${response.errorBody()?.string()}")
                }
            }

            override fun onFailure(call: Call<AppointmentsResponse>, t: Throwable) {
                Toast.makeText(requireContext(), "Network error: ${t.message}", Toast.LENGTH_SHORT).show()
                Log.e("ProfileFragment", "Error fetching appointments", t)
            }
        })
    }

    private fun displayAppointmentsByMonth(appointments: List<Appointment>) {
        val appointmentsContainer = binding.root.findViewById<LinearLayout>(R.id.appointmentsContainer)

        // Clear any existing appointment containers
        appointmentsContainer.removeAllViews()

        // Group appointments by month
        val appointmentsByMonth = appointments.groupBy { appointment ->
            try {
                val format = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
                val date = format.parse(appointment.appointmentDate)
                val calendar = Calendar.getInstance()
                calendar.time = date ?: Date()
                SimpleDateFormat("MMMM yyyy", Locale.getDefault()).format(calendar.time)
            } catch (e: Exception) {
                "Unknown"
            }
        }

        // Create a container for each month and its appointments
        appointmentsByMonth.forEach { (month, monthAppointments) ->
            // Create a card for this month
            val monthCard = layoutInflater.inflate(R.layout.item_month_header, null) as CardView

            // Set the month title
            monthCard.findViewById<TextView>(R.id.monthTitle).text = month

            // Get the container for appointments in this month
            val monthAppointmentsContainer = monthCard.findViewById<LinearLayout>(R.id.monthAppointmentsContainer)

            // Sort appointments by date (newest first)
            val sortedAppointments = monthAppointments.sortedByDescending { it.appointmentDate }

            sortedAppointments.forEach { appointment ->
                val appointmentItem = createAppointmentItem(appointment)
                monthAppointmentsContainer.addView(appointmentItem)
            }

            // Add this month's card to the main container
            appointmentsContainer.addView(monthCard)
        }
    }

    private fun createAppointmentItem(appointment: Appointment): View {
        val appointmentItem = layoutInflater.inflate(R.layout.appointment_item, null)

        // Set date circle info
        val dateCircle = appointmentItem.findViewById<LinearLayout>(R.id.dateCircle)
        val dayOfWeekText = dateCircle.findViewById<TextView>(R.id.dayOfWeekText)
        val dayText = dateCircle.findViewById<TextView>(R.id.dayText)

        try {
            val format = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
            val date = format.parse(appointment.appointmentDate)
            val calendar = Calendar.getInstance()
            calendar.time = date ?: Date()

            // First letter of day of week (M, T, W, etc.)
            val dayOfWeek = SimpleDateFormat("E", Locale.getDefault()).format(calendar.time).first().toString()
            dayOfWeekText.text = dayOfWeek

            // Day of month (1-31)
            dayText.text = calendar.get(Calendar.DAY_OF_MONTH).toString()
        } catch (e: Exception) {
            dayOfWeekText.text = "?"
            dayText.text = "?"
            Log.e("ProfileFragment", "Error parsing date: ${appointment.appointmentDate}", e)
        }

        // Set appointment details
        val appointmentText = appointmentItem.findViewById<TextView>(R.id.appointmentText)
        val stylistNameText = appointmentItem.findViewById<TextView>(R.id.stylistNameText)
        val statusText = appointmentItem.findViewById<TextView>(R.id.statusText)
        val statusIndicator = appointmentItem.findViewById<View>(R.id.statusIndicator)
        val appointmentCard = appointmentItem.findViewById<CardView>(R.id.appointmentCard)

        try {
            val serviceTime = SimpleDateFormat("h:mm a", Locale.getDefault()).format(
                SimpleDateFormat("HH:mm:ss", Locale.getDefault()).parse(appointment.appointmentTime) ?: Date()
            )
            appointmentText.text = "${appointment.service} @ $serviceTime"
            stylistNameText.text = "Stylist: ${appointment.stylistName}"

            // Set status
            statusText.text = appointment.status

            // Set colors based on status
            when (appointment.status.lowercase()) {
                "approved" -> {
                    val greenColor = ContextCompat.getColor(requireContext(), android.R.color.holo_green_light)
                    statusIndicator.setBackgroundColor(greenColor)
                    statusText.setBackgroundColor(greenColor)
                }
                "rejected" -> {
                    val redColor = ContextCompat.getColor(requireContext(), android.R.color.holo_red_light)
                    statusIndicator.setBackgroundColor(redColor)
                    statusText.setBackgroundColor(redColor)
                }
                else -> { // Pending or any other status
                    val yellowColor = ContextCompat.getColor(requireContext(), android.R.color.holo_orange_light)
                    statusIndicator.setBackgroundColor(yellowColor)
                    statusText.setBackgroundColor(yellowColor)
                }
            }

        } catch (e: Exception) {
            appointmentText.text = appointment.service
            Log.e("ProfileFragment", "Error parsing time: ${appointment.appointmentTime}", e)
        }

        // Set edit button click listener


        return appointmentItem
    }

    private fun showNoAppointmentsMessage() {
        // Clear any existing content
        val appointmentsContainer = binding.root.findViewById<LinearLayout>(R.id.appointmentsContainer)
        appointmentsContainer.removeAllViews()

        val noAppointmentsText = TextView(requireContext()).apply {
            text = "No appointment history found"
            textSize = 16f
            setPadding(32, 32, 32, 32)
        }
        appointmentsContainer.addView(noAppointmentsText)
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}