document.addEventListener("DOMContentLoaded", function () {
    // No need to set display:none here since CSS handles it
    const searchInput = document.getElementById("searchAppointment");
    const tableRows = document.querySelectorAll("tbody tr");

    searchInput.addEventListener("keyup", function () {
        let filter = searchInput.value.toLowerCase();
        tableRows.forEach(row => {
            let customerName = row.cells[0].innerText.toLowerCase();
            row.style.display = customerName.includes(filter) ? "" : "none";
        });
    });
});
