document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchUser");
    const tableRows = document.querySelectorAll("tbody tr");

    searchInput?.addEventListener("keyup", function () {
        let filter = searchInput.value.toLowerCase();
        tableRows.forEach(row => {
            let name = row.cells[1].textContent.toLowerCase();
            let phone = row.cells[2].textContent.toLowerCase();
            let email = row.cells[3].textContent.toLowerCase();
            row.style.display = (name.includes(filter) || phone.includes(filter) || email.includes(filter)) ? "" : "none";
        });
    });

    // Attach event listeners to buttons that open modals
    const addUserButton = document.getElementById('addUserButton');
    if (addUserButton) {
        addUserButton.addEventListener('click', openAddUserModal);
    }

    // Add event listener for the Edit button if it exists
    const editButtons = document.querySelectorAll('.editButton');
    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const id = button.dataset.id;
            const name = button.dataset.name;
            const phone = button.dataset.phone;
            const email = button.dataset.email;
            openEditModal(id, name, phone, email);
        });
    });
});

// Function to confirm deletion of a user
function confirmDelete(userId) {
    if (confirm("Are you sure you want to delete this user?")) {
        document.getElementById('deleteForm_' + userId).submit();
    }
}

// Close modals when clicking outside them
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

// Functions to open and close modals
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
