<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Climatologic Statistics with bootstra5.3.1</title>
  <link id="bootstrap-theme" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.1/dist/darkly/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="styles/styles.css">
<style>
    .table th:first-child,
    .table td:first-child {
    position: sticky;
    left: 0;
    }
</style>
</head>
<body class="p-3 m-0 border-0 bd-example m-0 border-0">
    <script>
        // Get the theme value from the session storage
        const theme = sessionStorage.getItem('theme');
        
        // Display the theme value in the console
        console.log("Theme=[", theme, "]");
        
        // Check if a theme is stored in session storage
        if (theme !== undefined && theme !== null) {
            // If a theme is stored, set the theme to the value of the `theme` variable.
            const themeLink = document.getElementById('bootstrap-theme');
            themeLink.href = `https://cdn.jsdelivr.net/npm/bootswatch@5.3.1/dist/${theme}/bootstrap.min.css`;
        }
        else {
            // If no theme is stored, set the default theme (darkly).
            const themeLink = document.getElementById('bootstrap-theme');
            themeLink.href = 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.1/dist/darkly/bootstrap.min.css';
        }
    </script>
    <?php
        include "weatherMetricsNavBar.html";
    ?>