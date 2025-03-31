package com.example.divasalon.ui.home

import android.os.Bundle
import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.LinearLayout
import android.widget.TextView
import androidx.fragment.app.Fragment
import androidx.recyclerview.widget.GridLayoutManager
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.divasalon.R
import com.example.divasalon.adapters.HomeStylistAdapter
import com.example.divasalon.adapters.HomeHorAdapters
import com.example.divasalon.backend.ApiService
import com.example.divasalon.backend.RetrofitClient
import com.example.divasalon.models.HomeHorModel
import com.example.divasalon.models.StylistResponse
import com.google.android.material.dialog.MaterialAlertDialogBuilder

class HomeFragment : Fragment() {

    private lateinit var recyclerView: RecyclerView
    private lateinit var adapter: HomeHorAdapters
    private lateinit var stylistRecyclerView: RecyclerView
    private lateinit var stylistAdapter: HomeStylistAdapter
    private val TAG = "HomeFragment"

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.fragment_home, container, false)
        recyclerView = view.findViewById(R.id.services_home)
        stylistRecyclerView = view.findViewById(R.id.stylist_recycler)
        return view
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        // Set up services horizontal recycler view
        setupServicesRecyclerView()

        // Set up stylist horizontal recycler view
        setupStylistRecyclerView()

        // Load stylists from API
        loadStylists()
    }

    private fun setupServicesRecyclerView() {
        val serviceList = listOf(
            HomeHorModel(
                R.drawable.pedicure_1,
                "Nail Services",
                listOf(
                    Pair("Manicure", "P 300.00"),
                    Pair("Pedicure", "P 370.00"),
                    Pair("Gel Polish", "P 1,170.00"),
                    Pair("Gel polish removal", "P 370.00"),
                    Pair("Nail Art", "P 300.00"),
                    Pair("Nail Extension", "P 400.00"),
                    Pair("Foot Spa", "P 500.00"),
                    Pair("Foot reflex", "P 450.00")
                )
            ),
            HomeHorModel(
                R.drawable.haircut_1,
                "Haircut",
                listOf(
                    Pair("Regular Cut", "P 250.00"),
                    Pair("Styling Cut", "P 350.00"),
                    Pair("Kids Cut", "P 180.00")
                )
            ),
            HomeHorModel(
                R.drawable.hair_coloring_1,
                "Hair Coloring",
                listOf(
                    Pair("Root Touch Up", "P 1,200.00"),
                    Pair("Full Color", "P 2,000.00"),
                    Pair("Highlights", "P 2,500.00")
                )
            )
        )


        adapter = HomeHorAdapters(requireContext(), serviceList) { name, services ->
            showServiceDetailsDialog(name, services)
        }

        recyclerView.layoutManager = LinearLayoutManager(requireContext(), LinearLayoutManager.HORIZONTAL, false)
        recyclerView.adapter = adapter
    }

    private fun setupStylistRecyclerView() {
        // Initialize adapter with empty list, will be populated from API
        stylistAdapter = HomeStylistAdapter(emptyList())

        // Set GridLayoutManager with 2 columns, to divide the items into 2 rows, while scrolling horizontally
        val gridLayoutManager = GridLayoutManager(requireContext(), 2)
        gridLayoutManager.orientation = GridLayoutManager.HORIZONTAL

        stylistRecyclerView.layoutManager = gridLayoutManager
        stylistRecyclerView.adapter = stylistAdapter
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

    private fun showServiceDetailsDialog(name: String, services: List<Pair<String, String>>) {
        // Create custom dialog view
        val dialogView = LayoutInflater.from(requireContext()).inflate(R.layout.service_details_dialog, null)
        val dialogTitle = dialogView.findViewById<TextView>(R.id.dialog_title)
        val servicesContainer = dialogView.findViewById<LinearLayout>(R.id.services_container)

        dialogTitle.text = name

        // Add each service to the container
        for (service in services) {
            val serviceItemView = LayoutInflater.from(requireContext())
                .inflate(R.layout.service_item, servicesContainer, false)

            val serviceNameView = serviceItemView.findViewById<TextView>(R.id.service_name)
            val servicePriceView = serviceItemView.findViewById<TextView>(R.id.service_price)

            serviceNameView.text = service.first
            servicePriceView.text = service.second

            servicesContainer.addView(serviceItemView)
        }

        // Show the dialog
        MaterialAlertDialogBuilder(requireContext())
            .setView(dialogView)
            .setPositiveButton("OK") { dialog, _ -> dialog.dismiss() }
            .show()
    }
}