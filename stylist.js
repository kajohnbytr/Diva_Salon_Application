document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchStylist");
    const tableRows = document.querySelectorAll("tbody tr");

    // Search filter
    searchInput.addEventListener("keyup", function () {
        let filter = searchInput.value.toLowerCase();
        tableRows.forEach(row => {
            let name = row.cells[1].innerText.toLowerCase();
            row.style.display = name.includes(filter) ? "" : "none";
        });
    });
    
});

// Open and Close Add Modal
function openAddModal() {
    document.getElementById("addModal").style.display = "block";
}

function closeAddModal() {
    document.getElementById("addModal").style.display = "none";
}

// Open and Close Edit Modal
function openEditModal(id, name, expertise, phone, email) {
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_name").value = name;
    document.getElementById("edit_expertise").value = expertise;
    document.getElementById("edit_phone").value = phone;
    document.getElementById("edit_email").value = email;
    document.getElementById("editModal").style.display = "block";
}

function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
}

// Delete Stylist Confirmation
function deleteStylist(stylistId) {
    if (confirm("Are you sure you want to delete this stylist?")) {
        window.location.href = `delete_stylist.php?id=${stylistId}`;
    }
}

window.onclick = function(event) {
    let addUserModal = document.getElementById('addUserModal');
    let editModal = document.getElementById('editModal');

    if (event.target === addUserModal) {
        closeAddUserModal();
    }
    if (event.target === editModal) {
        closeEditModal();
    }
};
