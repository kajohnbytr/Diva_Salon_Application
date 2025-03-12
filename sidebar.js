document.addEventListener("DOMContentLoaded", function () {
    function confirmLogout() {
        if (confirm("Are you sure you want to log out?")) {
            window.location.href = 'logout.php';
        }
    }

    const logoutButton = document.querySelector('.logout');
    if (logoutButton) {
        logoutButton.addEventListener('click', function (e) {
            e.preventDefault();
            confirmLogout();
        });
    } else {
        console.error("Logout button not found. Please check the HTML.");
    }

    function setActive(element) {
        // Remove active class from all buttons and list items
        document.querySelectorAll("nav ul li, .dropdown button").forEach(item => {
            item.classList.remove("active");
        });

        // Add active class to clicked element
        element.classList.add("active");
    }

    // Toggle dropdown menu visibility
    function toggleDropdown(element) {
        var dropdown = document.getElementById("appointmentDropdown");
        dropdown.style.display = dropdown.style.display === "flex" ? "none" : "flex";
        setActive(element);
    }

    // Attach click event to the "Appointments" button
    document.querySelector(".appointments-btn").addEventListener("click", function () {
        toggleDropdown(this);
    });

    // Attach click events to dropdown buttons
    document.querySelectorAll("#appointmentDropdown button").forEach(button => {
        button.addEventListener("click", function () {
            setActive(this);
            window.location.href = this.getAttribute("data-href");
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function (event) {
        var dropdown = document.getElementById("appointmentDropdown");
        var appointmentBtn = document.querySelector(".appointments-btn");

        if (!appointmentBtn.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });
});
