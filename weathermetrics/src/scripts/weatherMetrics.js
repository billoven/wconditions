// Function to validate the selected dates
function validateDates() {
    // Get the start date and end date values from the input fields
    var startDateValue = document.getElementById("start_date").value;
    var endDateValue = document.getElementById("end_date").value;

    // Check if both dates are provided
    if (!startDateValue || !endDateValue) {
        showAlert("Warning", "Please select both a start date and an end date.", "warning", true);
        return false; // Prevent form submission
    }

    var startDate = new Date(startDateValue);
    var endDate = new Date(endDateValue);

    // Define the minimum allowed date (January 1, 2016)
    var minDate = new Date("2016-01-01");

    // Check if the selected dates are before January 1, 2016
    if (startDate < minDate || endDate < minDate) {
        showAlert("Error", "Selected dates cannot be before January 1, 2016.", "error", true);
        return false; // Prevent form submission
    }

    // Check if the start date is greater than the end date
    if (startDate > endDate) {
        showAlert("Error", "Start date cannot be greater than End date!", "error", true, 5000);
        return false; // Prevent form submission
    }

    // If all validation checks pass, allow form submission
    return true;
}