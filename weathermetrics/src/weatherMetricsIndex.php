<?php
    // Common Header for all the weatherMetrics files
    include "weatherMetricsHeader.php";

    ini_set('display_errors', 1);
    error_reporting(E_ALL);
?>
    <h1 class="mb-4">Select a Weather Site Database</h1>
    <form action="weatherMetrics.php" method="post" class="mx-auto">
        <div class="mb-3 text-center">
            <label for="database" class="form-label">Choose a Database:</label>
            <select name="database" id="database" class="form-select w-auto mx-auto">
                <?php
                // Include the database configurations
                require_once('/etc/weathermetrics/db_config.php');
                
                // Dynamically generate options based on the configurations
                foreach ($dbConfigs as $index => $dbConfig) {
                    echo "<option value=\"$index\">{$dbConfig['database']}</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</body>
</html>
