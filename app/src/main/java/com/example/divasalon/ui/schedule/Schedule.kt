package com.example.divasalon.ui.schedule

import android.os.Bundle
import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.AdapterView
import android.widget.ArrayAdapter
import android.widget.Button
import android.widget.Spinner
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.recyclerview.widget.GridLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.divasalon.R
import com.example.divasalon.adapters.ScheduleStylistAdapter
import com.example.divasalon.backend.ApiService
import com.example.divasalon.backend.RetrofitClient
import com.example.divasalon.models.AppointmentRequest
import com.example.divasalon.models.AppointmentResponse
import com.example.divasalon.models.StylistResponse
import com.example.divasalon.utils.SharedPrefManager
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.google.android.material.timepicker.MaterialTimePicker
import com.google.android.material.timepicker.TimeFormat
import java.text.SimpleDateFormat
import java.util.Calendar
import java.util.Date
import java.util.Locale
import com.google.android.material.datepicker.CalendarConstraints
import com.google.android.material.datepicker.DateValidatorPointForward
import com.google.android.material.datepicker.MaterialDatePicker
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class Schedule : Fragment() {

    private lateinit var serviceCategorySpinner: Spinner
    private lateinit var serviceTypeSpinner: Spinner
    private lateinit var setAppointmentButton: Button
    private lateinit var stylistAdapter: ScheduleStylistAdapter
    private val TAG = "Schedule"

    // Define service categories and their corresponding types
    private val serviceCategories = listOf(
        "Hair Services",
        "Nail Services",
        "Makeup Services",
        "Body Treatments & Massage"
    )

    // Map of service categories to their types
    private val serviceTypes = mapOf(
        "Hair Services" to listOf(
            "Haircuts and Trimming",
            "Hair Coloring",
            "Hair Styling",
            "Hair Treatments",
            "Hair Extensions & Rebonding"
        ),
        "Nail Services" to listOf(
            "Manicure",
            "Pedicure",
            "Nail Art",
            "Gel Extensions",
            "Acrylic Nails"
        ),
        "Skincare & Facial Treatments" to listOf(
            "Basic Facial",
            "Deep Cleansing",
            "Anti-aging Treatments",
            "Acne Treatments",
            "Chemical Peels"
        ),
        "Makeup Services" to listOf(
            "Everyday Makeup",
            "Special Occasion",
            "Bridal Makeup",
            "Makeup Lessons",
            "Theatrical Makeup"
        ),
        "Body Treatments & Massage" to listOf(
            "Swedish Massage",
            "Deep Tissue Massage",
            "Hot Stone Therapy",
            "Body Scrubs",
            "Aromatherapy"
        )
    )

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        return inflater.inflate(R.layout.fragment_schedule, container, false)
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        // Initialize views
        serviceCategorySpinner = view.findViewById(R.id.serviceCategorySpinner)
        serviceTypeSpinner = view.findViewById(R.id.serviceTypeSpinner)
        setAppointmentButton = view.findViewById(R.id.btnSetAppointment)

        // Set up service category spinner
        val categoryAdapter = ArrayAdapter(
            requireContext(),
            android.R.layout.simple_spinner_item,
            serviceCategories
        )
        categoryAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        serviceCategorySpinner.adapter = categoryAdapter

        // Set up service types spinner with initial data
        updateServiceTypeSpinner(serviceCategories[0])

        serviceCategorySpinner.onItemSelectedListener =
            object : AdapterView.OnItemSelectedListener {
                override fun onItemSelected(
                    parent: AdapterView<*>?,
                    view: View?,
                    position: Int,
                    id: Long
                ) {
                    val selectedCategory = serviceCategories[position]
                    updateServiceTypeSpinner(selectedCategory)
                }

                override fun onNothingSelected(parent: AdapterView<*>?) {
                    // Do nothing
                }
            }

        setupStylistRecyclerView()
        setupAppointmentButton()
    }

    private fun updateServiceTypeSpinner(category: String) {
        val types = serviceTypes[category] ?: emptyList()

        val typeAdapter = ArrayAdapter(
            requireContext(),
            android.R.layout.simple_spinner_item,
            types
        )
        typeAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        serviceTypeSpinner.adapter = typeAdapter
    }

    private fun setupStylistRecyclerView() {
        val recyclerView = view?.findViewById<RecyclerView>(R.id.stylistRecyclerView)

        // Create a GridLayoutManager with 2 rows and horizontal orientation
        val layoutManager = GridLayoutManager(context, 2, GridLayoutManager.HORIZONTAL, false)
        recyclerView?.layoutManager = layoutManager

        // Create and set the adapter
        stylistAdapter = ScheduleStylistAdapter()
        recyclerView?.adapter = stylistAdapter

        // Load stylists from API
        loadStylists()
    }

    private fun loadStylists() {
        val apiService = RetrofitClient.instance.create(ApiService::class.java)
        apiService.getStylists().enqueue(object : retrofit2.Callback<StylistResponse> {
            override fun onResponse(call: retrofit2.Call<StylistResponse>, response: retrofit2.Response<StylistResponse>) {
                Log.d(TAG, "Raw response: ${response.raw().toString()}")

                if (response.isSuccessful) {
                    val responseBody = response.body()
                    Log.d(TAG, "Response body: $responseBody")

                    val stylists = responseBody?.Stylist ?: emptyList()
                    Log.d(TAG, "Loaded ${stylists.size} stylists from API")
                    stylistAdapter.updateStylists(stylists)
                } else {
                    Log.e(TAG, "Failed to load stylists: ${response.code()}")
                    // Show error message to user
                    context?.let {
                        MaterialAlertDialogBuilder(it)
                            .setTitle("Error")
                            .setMessage("Failed to load stylists. Please try again.")
                            .setPositiveButton("Retry") { _, _ -> loadStylists() }
                            .setNegativeButton("Cancel", null)
                            .show()
                    }
                }
            }

            override fun onFailure(call: retrofit2.Call<StylistResponse>, t: Throwable) {
                Log.e(TAG, "API call failed", t)
                // Show error message to user
                context?.let {
                    MaterialAlertDialogBuilder(it)
                        .setTitle("Connection Error")
                        .setMessage("Failed to connect to server. Please check your internet connection.")
                        .setPositiveButton("Retry") { _, _ -> loadStylists() }
                        .setNegativeButton("Cancel", null)
                        .show()
                }
            }
        })
    }

    private fun setupAppointmentButton() {
        setAppointmentButton.setOnClickListener {
            val selectedStylist = stylistAdapter.getSelectedStylist()

            if (selectedStylist == null) {
                MaterialAlertDialogBuilder(requireContext())
                    .setTitle("No Stylist Selected")
                    .setMessage("Please select a stylist before setting an appointment.")
                    .setPositiveButton("OK", null)
                    .show()
                return@setOnClickListener
            }

            showImprovedDatePicker()
        }
    }

    private fun showImprovedDatePicker() {
        // Set up constraints to only allow dates from today forward
        val today = MaterialDatePicker.todayInUtcMilliseconds()
        val calendar = Calendar.getInstance()

        // Set the maximum date to 30 days from now (you can adjust this based on salon policy)
        calendar.timeInMillis = today
        calendar.add(Calendar.DATE, 30)
        val maxDate = calendar.timeInMillis

        val constraints = CalendarConstraints.Builder()
            .setStart(today)
            .setEnd(maxDate)
            .setValidator(DateValidatorPointForward.now())
            .build()

        val datePicker = MaterialDatePicker.Builder.datePicker()
            .setTitleText("Select Appointment Date")
            .setSelection(today)
            .setCalendarConstraints(constraints)
            .setTheme(com.google.android.material.R.style.ThemeOverlay_Material3_MaterialCalendar)
            .build()

        datePicker.addOnPositiveButtonClickListener { dateInMillis ->
            val selectedDate = SimpleDateFormat("EEEE, MMM dd, yyyy", Locale.getDefault())
                .format(Date(dateInMillis))

            // Store selected date in a Calendar instance for easier manipulation
            val selectedCalendar = Calendar.getInstance()
            selectedCalendar.timeInMillis = dateInMillis

            // Show time picker with business hours constraints
            showImprovedTimePicker(selectedDate, selectedCalendar)
        }

        datePicker.show(parentFragmentManager, "IMPROVED_DATE_PICKER")
    }

    private fun showImprovedTimePicker(selectedDate: String, selectedCalendar: Calendar) {
        // Business hours: 9:00 AM to 7:00 PM (adjust as needed)
        val businessHourStart = 7
        val businessHourEnd = 22

        // Default to current time if within business hours, otherwise default to opening time
        val currentHour = Calendar.getInstance().get(Calendar.HOUR_OF_DAY)
        val defaultHour = when {
            currentHour < businessHourStart -> businessHourStart
            currentHour >= businessHourEnd -> businessHourStart
            else -> currentHour + 1 // Next available hour
        }

        val timePicker = MaterialTimePicker.Builder()
            .setTimeFormat(TimeFormat.CLOCK_12H)
            .setHour(defaultHour)
            .setMinute(0)
            .setTitleText("Select Appointment Time")
            .setInputMode(MaterialTimePicker.INPUT_MODE_CLOCK)
            .build()

        timePicker.addOnPositiveButtonClickListener {
            val hour = timePicker.hour
            val minute = timePicker.minute

            // Validate business hours
            if (hour < businessHourStart || hour >= businessHourEnd) {
                MaterialAlertDialogBuilder(requireContext())
                    .setTitle("Outside Business Hours")
                    .setMessage("Please select a time between 9:00 AM and 7:00 PM.")
                    .setPositiveButton("Try Again") { _, _ ->
                        showImprovedTimePicker(selectedDate, selectedCalendar)
                    }
                    .show()
                return@addOnPositiveButtonClickListener
            }

            // Format time in 12-hour format
            val amPm = if (hour < 12) "AM" else "PM"
            val displayHour = if (hour % 12 == 0) 12 else hour % 12
            val timeString = String.format("%d:%02d %s", displayHour, minute, amPm)

            // Set the selection in the Calendar
            selectedCalendar.set(Calendar.HOUR_OF_DAY, hour)
            selectedCalendar.set(Calendar.MINUTE, minute)

            // Check if the selected time is in the past
            if (selectedCalendar.timeInMillis < System.currentTimeMillis()) {
                MaterialAlertDialogBuilder(requireContext())
                    .setTitle("Time Already Passed")
                    .setMessage("Please select a future time for your appointment.")
                    .setPositiveButton("OK") { _, _ ->
                        showImprovedTimePicker(selectedDate, selectedCalendar)
                    }
                    .show()
                return@addOnPositiveButtonClickListener
            }

            // Show confirmation with selected date and time
            showAppointmentConfirmation(selectedDate, timeString, selectedCalendar)
        }

        timePicker.show(parentFragmentManager, "IMPROVED_TIME_PICKER")
    }

    private fun showAppointmentConfirmation(date: String, time: String, selectedCalendar: Calendar) {
        val selectedStylist = stylistAdapter.getSelectedStylist()
        val selectedService = serviceTypeSpinner.selectedItem.toString()
        val selectedCategory = serviceCategorySpinner.selectedItem.toString()

        val message = """
            Appointment Details:
            
            Date: $date
            Time: $time
            Stylist: ${selectedStylist?.name}
            Service: $selectedService
            Category: $selectedCategory
            
            Would you like to confirm this appointment?
        """.trimIndent()

        MaterialAlertDialogBuilder(requireContext())
            .setTitle("Confirm Appointment")
            .setMessage(message)
            .setPositiveButton("Confirm") { _, _ ->
                val stylistIdInt = selectedStylist?.id?.toIntOrNull() ?: 0
                saveAppointment(stylistIdInt, selectedCalendar, selectedService)
            }
            .setNegativeButton("Cancel", null)
            .show()
    }

    private fun saveAppointment(stylistId: Int, calendar: Calendar, service: String) {
        // Get the current user's ID from SharedPreferences
        val sharedPrefManager = SharedPrefManager.getInstance(requireContext())

        if (!sharedPrefManager.isLoggedIn()) {
            Toast.makeText(requireContext(), "Please log in to book an appointment", Toast.LENGTH_LONG).show()
            return
        }

        val userId = sharedPrefManager.getUserId()
        Log.d("Appointment", "User ID from SharedPref: $userId")

        val dateFormat = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
        val timeFormat = SimpleDateFormat("HH:mm:ss", Locale.getDefault())

        val appointmentDate = dateFormat.format(calendar.time)
        val appointmentTime = timeFormat.format(calendar.time)

        // Modify before posting appointment data
        val serviceType = serviceTypeSpinner.selectedItem.toString().replace("&amp;", "and")

        val appointmentRequest = AppointmentRequest(
            customerId = userId,
            stylistId = stylistId.toString(),
            appointmentDate = appointmentDate,
            appointmentTime = appointmentTime,
            service = serviceType  // Here, serviceType no longer contains &amp;
        )

        // Create loading dialog
        val loadingDialog = MaterialAlertDialogBuilder(requireContext())
            .setTitle("Booking Appointment")
            .setMessage("Please wait while we book your appointment...")
            .setCancelable(false)
            .create()

        loadingDialog.show()

        // Make API call to create appointment
        val apiService = RetrofitClient.instance.create(ApiService::class.java)
        apiService.createAppointment(appointmentRequest).enqueue(object : Callback<AppointmentResponse> {
            override fun onResponse(call: Call<AppointmentResponse>, response: Response<AppointmentResponse>) {
                loadingDialog.dismiss()

                if (response.isSuccessful && response.body()?.success == true) {
                    MaterialAlertDialogBuilder(requireContext())
                        .setTitle("Success")
                        .setMessage("Your appointment has been scheduled successfully!")
                        .setPositiveButton("OK", null)
                        .show()

                } else {
                    val errorMsg = response.body()?.message ?: "Failed to schedule appointment"
                    MaterialAlertDialogBuilder(requireContext())
                        .setTitle("Error")
                        .setMessage(errorMsg)
                        .setPositiveButton("OK", null)
                        .show()

                    Log.e(TAG, "Failed to create appointment: ${response.code()} - ${response.message()}")
                }
            }

            override fun onFailure(call: Call<AppointmentResponse>, t: Throwable) {
                loadingDialog.dismiss()

                MaterialAlertDialogBuilder(requireContext())
                    .setTitle("Connection Error")
                    .setMessage("Failed to connect to server. Please check your internet connection and try again.")
                    .setPositiveButton("OK", null)
                    .show()

                Log.e(TAG, "API call failed", t)
            }
        })
    }
}