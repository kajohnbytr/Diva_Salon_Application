package com.example.divasalon.adapters

import android.graphics.Color
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.bumptech.glide.load.engine.DiskCacheStrategy
import com.example.divasalon.R
import com.example.divasalon.models.Stylist
import de.hdodenhof.circleimageview.CircleImageView

class ScheduleStylistAdapter : RecyclerView.Adapter<ScheduleStylistAdapter.StylistViewHolder>() {
    // Initialize with empty list that will be populated from the API
    private var stylists = emptyList<Stylist>()

    // ViewHolder class to hold references to views in the item layout
    class StylistViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        val image: CircleImageView = itemView.findViewById(R.id.ivStylistPhoto)
        val name: TextView = itemView.findViewById(R.id.tvStylistName)
        val container: View = itemView
    }

    // Create new views (invoked by the layout manager)
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): StylistViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.stylist_item, parent, false)
        return StylistViewHolder(view)
    }

    // Replace the contents of a view (invoked by the layout manager)
    override fun onBindViewHolder(holder: StylistViewHolder, position: Int) {
        val stylist = stylists[position]

        // Set stylist name
        holder.name.text = stylist.name

        // Load stylist image using Glide
        if (!stylist.imageUrl.isNullOrEmpty()) {
            Glide.with(holder.image.context)
                .load(stylist.imageUrl)
                .diskCacheStrategy(DiskCacheStrategy.ALL)
                .placeholder(R.drawable.stylist_1) // Default placeholder while loading
                .error(R.drawable.stylist_1) // Image to show if loading fails
                .into(holder.image)
        } else {
            // Set default image if no URL is available
            holder.image.setImageResource(R.drawable.stylist_1)
        }

        // Set border for selected stylist
        if (stylist.isSelected) {
            holder.image.borderWidth = 2
            holder.image.borderColor = Color.parseColor("#E90202") // Red border for selected stylist
        } else {
            holder.image.borderWidth = 0 // No border for unselected stylists
        }

        // Set click listener for selecting a stylist
        holder.itemView.setOnClickListener {
            // Update the selected state of all stylists
            val updatedList = stylists.map {
                it.copy(isSelected = it.id == stylist.id)
            }
            updateStylists(updatedList)
        }
    }

    // Return the size of your dataset
    override fun getItemCount() = stylists.size

    // Method to update the data in the adapter
    fun updateStylists(newStylists: List<Stylist>) {
        stylists = newStylists
        notifyDataSetChanged()
    }

    // Method to get the currently selected stylist
    fun getSelectedStylist(): Stylist? {
        return stylists.find { it.isSelected }
    }
}