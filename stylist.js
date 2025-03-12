document.addEventListener("DOMContentLoaded", function () {
    const addModal = document.getElementById("addModal");
    const editModal = document.getElementById("editModal");
    const searchInput = document.getElementById("searchStylist");
    const tableRows = document.querySelectorAll("tbody tr");

    if (addModal) addModal.style.display = "none";
    if (editModal) editModal.style.display = "none";

    if (searchInput) {
        searchInput.addEventListener("keyup", function () {
            let filter = searchInput.value.toLowerCase();
            tableRows.forEach(row => {
                let name = row.cells[1].innerText.toLowerCase();
                row.style.display = name.includes(filter) ? "" : "none";
            });
        });
    }
});

function openAddModal() {
    const addModal = document.getElementById("addModal");
    if (addModal) addModal.style.display = "flex";
}

function closeAddModal() {
    const addModal = document.getElementById("addModal");
    if (addModal) addModal.style.display = "none";
}

function openEditModal(id, name, expertise, phone, email) {
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_name").value = name;
    document.getElementById("edit_expertise").value = expertise;
    document.getElementById("edit_phone").value = phone;
    document.getElementById("edit_email").value = email;

    const editModal = document.getElementById("editModal");
    if (editModal) editModal.style.display = "flex";
}

function closeEditModal() {
    const editModal = document.getElementById("editModal");
    if (editModal) editModal.style.display = "none";
}

function deleteStylist(id) {
    if (confirm("Are you sure you want to delete this stylist?")) {
        const form = document.getElementById("deleteForm_" + id);
        if (form) form.submit();
    }
}

window.addEventListener("click", function (event) {
    const addModal = document.getElementById("addModal");
    const editModal = document.getElementById("editModal");

    if (event.target === addModal) closeAddModal();
    if (event.target === editModal) closeEditModal();
});
