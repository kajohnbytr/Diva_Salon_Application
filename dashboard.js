document.addEventListener("DOMContentLoaded", function () {
    let chartElement = document.getElementById("chart");
    if (!chartElement) return; // Prevent errors if the chart element is missing

    let ctx = chartElement.getContext("2d");

    let appointmentsPerDay = JSON.parse(chartElement.dataset.appointmentsPerDay || '{}');
    let appointmentsPerWeek = JSON.parse(chartElement.dataset.appointmentsPerWeek || '{}');
    let appointmentsPerMonth = JSON.parse(chartElement.dataset.appointmentsPerMonth || '{}');

    new Chart(ctx, {
        type: "bar",
        data: {
            labels: [...Object.keys(appointmentsPerDay), ...Object.keys(appointmentsPerWeek), ...Object.keys(appointmentsPerMonth)],
            datasets: [
                {
                    label: "Appointments per Day",
                    data: Object.values(appointmentsPerDay),
                    backgroundColor: "#007bff"
                },
                {
                    label: "Appointments per Week",
                    data: Object.values(appointmentsPerWeek),
                    backgroundColor: "#28a745"
                },
                {
                    label: "Appointments per Month",
                    data: Object.values(appointmentsPerMonth),
                    backgroundColor: "#dc3545"
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                },
                x: {
                    stacked: true
                }
            }
        }
    });
});
