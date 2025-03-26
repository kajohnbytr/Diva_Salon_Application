package com.example.divasalon.adapters

import android.content.Context
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import com.example.divasalon.R
import com.example.divasalon.models.HomeHorModel

class HomeHorAdapters(
    private val context: Context,
    private val list: List<HomeHorModel>,
    private val onServiceClick: (String, List<Pair<String, String>>) -> Unit
) : RecyclerView.Adapter<HomeHorAdapters.ViewHolder>() {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val view = LayoutInflater.from(parent.context).inflate(R.layout.home_services, parent, false)
        return ViewHolder(view)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        val item = list[position]
        holder.imageView.setImageResource(item.image)
        holder.name.text = item.name

        holder.itemView.setOnClickListener {
            // Pass name and services list to display in dialog
            onServiceClick(item.name, item.services)
        }
    }

    override fun getItemCount(): Int = list.size

    class ViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        val imageView: ImageView = itemView.findViewById(R.id.haircut_img)
        val name: TextView = itemView.findViewById(R.id.haircut_txt)
    }
}