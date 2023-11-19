//showAlert('Information', 'Welcome to our website!', 'info', true);
//showAlert('Error', 'An unexpected error occurred.', 'error', true, 5000);
//showAlert('Important Update', 'A new version of our app is available. Please update now.', 'info');
//showAlert('Warning', 'Please review the form before submitting.', 'warning');
//showAlert('Success', 'Data submitted successfully!', 'success', true);
//showAlert('Information', 'This is an informational message.', 'info');

/// alertBox.js

// Function to show an alert message
function showAlert(title, message, type, draggable = true, timeout) {
    // Get the container for alert messages
    const messagesContainer = document.getElementById("messagesContainer");

    // Create the alert box element
    const alertBox = document.createElement("div");
    alertBox.className = `alert ${type}`;

    // Add the icon for the alert box
    const iconElement = document.createElement("i");
    switch (type) {
        case "error":
            iconElement.className = "fas fa-times";
            break;
        case "warning":
            iconElement.className = "fas fa-exclamation-triangle";
            break;
        case "info":
            iconElement.className = "fas fa-info-circle";
            break;
        case "success":
            iconElement.className = "fas fa-check-circle";
            break;
        default:
            iconElement.className = "fas fa-info-circle";
    }
    alertBox.appendChild(iconElement);

    // Add the title to the alert box
    const alertTitleElement = document.createElement("div");
    alertTitleElement.className = "alert-title";
    alertTitleElement.textContent = title;
    alertBox.appendChild(alertTitleElement);

    // Add the message to the alert box
    const alertMessageElement = document.createElement("div");
    alertMessageElement.className = "alert-message";
    alertMessageElement.textContent = message;
    alertBox.appendChild(alertMessageElement);

    // Add the close button to the alert box
    const closeButton = document.createElement("span");
    closeButton.className = "close-btn";
    closeButton.innerHTML = "&times;";
    closeButton.onclick = function () {
        messagesContainer.removeChild(alertBox);
    };
    alertBox.appendChild(closeButton);

    // Make the alert box draggable if draggable is true
    if (draggable) {
        let offsetX, offsetY, isDragging = false;

        // Handle mouse events for dragging
        alertBox.addEventListener("mousedown", function (e) {
            isDragging = true;
            offsetX = e.clientX - alertBox.getBoundingClientRect().left;
            offsetY = e.clientY - alertBox.getBoundingClientRect().top;
        });

        document.addEventListener("mousemove", function (e) {
            if (isDragging) {
                const x = e.clientX - offsetX;
                const y = e.clientY - offsetY;
                alertBox.style.left = x + "px";
                alertBox.style.top = y + "px";
            }   
        });

        document.addEventListener("mouseup", function () {
            isDragging = false; // Reset the isDragging flag
        });
    }

    // Add the alert box to the messages container
    messagesContainer.appendChild(alertBox);

    // Automatically hide the alert box after a few seconds if timeout is provided
    if (timeout) {
        setTimeout(function () {
            messagesContainer.removeChild(alertBox);
        }, timeout);
    }  
}
