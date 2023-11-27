<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Climatologic Statistics with bootstra5.3.1</title>
  <link id="bootstrap-theme" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.1/dist/darkly/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .table th:first-child,
        .table td:first-child {
        position: sticky;
        left: 0;
        }
    </style>   
</head>
<body class="container mt-5">
    <h1 class="mb-4">Select a Weather Site Database</h1>
    <form action="weatherMetrics.php" method="post" class="mx-auto">
        <div class="mb-3 text-center">
            <label for="database" class="form-label">Choose a Database:</label>
            <select name="database" id="database" class="form-select w-auto mx-auto">
                <?php
                // Include the database configurations
                require_once('/etc/weathermetrics/db_config.php');
                
                // Dynamically generate options based on the configurations
                foreach ($dbConfigs as $dbName => $dbConfig) {
                    echo "<option value=\"$dbName\">{$dbConfig['database']}</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</body>
</html>
