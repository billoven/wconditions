<!-- Include the alertBox.js script and alertBox.css stylesheet -->
<script src="alertBox.js"></script>
<link rel="stylesheet" href="alertBox.css">

<!-- Add this custom alert box HTML structure -->
<!-- This is the modal structure for displaying individual alert messages -->
<div id="alertModal" class="modal">
    <div class="modal-content">
        <!-- Close button to dismiss the alert modal -->
        <span class="close-btn" onclick="closeAlertModal()">&times;</span>
        <!-- Placeholder for the alert title -->
        <h2 id="alertTitle"></h2>
        <!-- Placeholder for the alert message content -->
        <p id="alertMessage"></p>
    </div>
</div>

<!-- Add a container for displaying alert messages -->
<div class="messages-container" id="messagesContainer">
    <!-- Each of these div elements represents a specific type of alert message -->
    <div class="message info"></div>
    <div class="message error"></div>
    <div class="message warning"></div>
    <div class="message success"></div>
</div>
