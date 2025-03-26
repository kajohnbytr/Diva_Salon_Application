package com.example.divasalon.adapters

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

class HomeStylistAdapter(
    private var stylists: List<Stylist>
) : RecyclerView.Adapter<HomeStylistAdapter.StylistViewHolder>() {

    // ViewHolder class to hold references to views in the item layout
    class StylistViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        val image: CircleImageView = itemView.findViewById(R.id.ivHomeStylistPhoto)
        val name: TextView = itemView.findViewById(R.id.tvHomeStylistName)
    }

    // Create new views (invoked by the layout manager)
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): StylistViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.home_stylist_item, parent, false)
        return StylistViewHolder(view)
    }

    // Replace the contents of a view (invoked by the layout manager)
    override fun onBindViewHolder(holder: StylistViewHolder, position: Int) {
        val stylist = stylists[position]

        // Set stylist name and specialty
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
    }

    // Return the size of your dataset
    override fun getItemCount() = stylists.size

    // Method to update the data in the adapter
    fun updateStylists(newStylists: List<Stylist>) {
        stylists = newStylists
        notifyDataSetChanged()
    }
}