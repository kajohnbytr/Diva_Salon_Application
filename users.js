document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchUser");
    const tableRows = document.querySelectorAll("tbody tr");

    // Hide modals on page load
    document.getElementById('addUserModal').style.display = 'none';
    document.getElementById('editModal').style.display = 'none';

    searchInput?.addEventListener("keyup", function () {
        let filter = searchInput.value.toLowerCase();
        tableRows.forEach(row => {
            let name = row.cells[1].textContent.toLowerCase();
            let phone = row.cells[2].textContent.toLowerCase();
            let email = row.cells[3].textContent.toLowerCase();
            row.style.display = (name.includes(filter) || phone.includes(filter) || email.includes(filter)) ? "" : "none";
        });
    });
});

function openAddUserModal() {
    document.getElementById('addUserModal').style.display = 'block';
}

function closeAddUserModal() {
    document.getElementById('addUserModal').style.display = 'none';
}

function openEditModal(id, name, phone, email) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_email').value = email;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside
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
