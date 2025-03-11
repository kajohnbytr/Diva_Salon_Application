document.addEventListener("DOMContentLoaded", function () {
    function confirmLogout() {
        if (confirm("Are you sure you want to log out?")) {
            window.location.href = 'logout.php';
        }
    }

    const logoutButton = document.querySelector('.logout'); // Fix: Select by class instead of ID
    if (logoutButton) {
        logoutButton.addEventListener('click', function (e) {
            e.preventDefault();
            confirmLogout();
        });
    } else {
        console.error("Logout button not found. Please check the HTML.");
    }
});
