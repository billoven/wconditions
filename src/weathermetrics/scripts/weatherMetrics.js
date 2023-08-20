
// Function to validate the selected dates
function validateDates() {

    // Get the start date and end date values from the input fields
    var startDate = new Date(document.getElementById("start_date").value);
    var endDate = new Date(document.getElementById("end_date").value);

    // Define the minimum allowed date (January 1, 2016)
    var minDate = new Date("2016-01-01");

    // Check if the selected dates are before January 1, 2016
    if (startDate < minDate || endDate < minDate) {
        // If the dates are invalid, show an error alert with a draggable close button
        showAlert("Error", "Selected dates cannot be before January 1, 2016.", "error", true);
        return false; // Prevent form submission
    }

    // Check if the start date is greater than the end date
    if (startDate > endDate) {
        // If the dates are invalid, show an error alert with a draggable close button and a timeout of 5 seconds
        showAlert("Error", "Start date cannot be greater than End date!", "error", true, 5000);
        return false; // Prevent form submission
    }

    // If all validation checks pass, allow form submission
    return true;
}


