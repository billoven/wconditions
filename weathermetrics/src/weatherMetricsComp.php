<?php
    // Common Header for all the weatherMetrics files
    include "weatherMetricsHeader.php";

    ini_set('display_errors', 1);
    error_reporting(E_ALL);
?>
     
    <div class="container" id="SectionComparison">
        <h5 class="mb-4">Compare 2 periods : <?php global $selectedStation; echo $selectedStation?></h5>
        <form id="formcomp" method="POST" action="weatherMetricsFormComp.php">
                <label for="start_date_1">Start Date (Period 1):</label>      
                <input type="date" name="start_date_1" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['start_date_1']) ? $_POST['start_date_1'] : ''; ?>">
                
                <label for="end_date_1">End Date (Period 1):</label>
                <input type="date" name="end_date_1" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['end_date_1']) ? $_POST['end_date_1'] : ''; ?>">
                
                <label for="start_date_2">Start Date (Period 2):</label>
                <input type="date" name="start_date_2" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['start_date_2']) ? $_POST['start_date_2'] : ''; ?>">
                
                <label for="end_date_2">End Date (Period 2):</label>
                <input type="date" name="end_date_2" required pattern="\d{4}-\d{2}-\d{2}" value="<?php echo isset($_POST['end_date_2']) ? $_POST['end_date_2'] : ''; ?>">
                
                <label for="weather_data_type">Select Weather Data Type:</label>
                <select id="weather_data_type" name="weather_data_type" required>
                    <option value="WC_TempAvg">Average Temperature</option>
                    <option value="WC_TempHigh">Maximum Temperature</option>
                    <option value="WC_TempLow">Minimum Temperature</option>
                    <option value="WC_PrecipitationSum">Precipitation Sum</option>
                </select>
                <input type="submit" value="Generate Comparison Graph">
        </form>
        <div class="graph-container">
            <h2>Comparison Graph</h2>
            <div id="ComparisonGraphContainer">
                <canvas id="comparisonChart" width="1024" height="400"></canvas>
            </div>
        </div>  
    </div>
    <script>

        console.log("Debut Script");

        // Define the temperatureChart variable outside the function
        var comparisonChart;

        $(document).ready(function () {

            function updateComparisonGraph(indexOfDay, datesPeriod1, datesPeriod2, weatherdata1, weatherdata2, datatype) {
                console.log("DataType:", datatype);
                console.log("DatesPeriod1:", datesPeriod1);
                console.log("DatesPeriod2:", datesPeriod2);
                console.log("WeatherData1:", weatherdata1);
                console.log("WeatherData2:", weatherdata2);
                console.log("IndexOfDay:", indexOfDay);

                // Format the dates for Period 1
                const fromDate1 = new Date(datesPeriod1[0]);
                const toDate1 = new Date(datesPeriod1[datesPeriod1.length - 1]);
                const formattedDatesPeriod1 = `${fromDate1.toLocaleDateString("en-GB")} to ${toDate1.toLocaleDateString("en-GB")}`;

                // Format the dates for Period 2
                const fromDate2 = new Date(datesPeriod2[0]);
                const toDate2 = new Date(datesPeriod2[datesPeriod2.length - 1]);
                const formattedDatesPeriod2 = `${fromDate2.toLocaleDateString("en-GB")} to ${toDate2.toLocaleDateString("en-GB")}`;

                // Update the labels for both Period 1 and Period 2 with the formatted dates
                comparisonChart.data.datasets[0].label = `From: ${formattedDatesPeriod1}`;
                comparisonChart.data.datasets[1].label = `From: ${formattedDatesPeriod2}`;

                // Update the title of the graph with the selected data type value
                const selectedDataType = $('#weather_data_type option:selected').text();
                comparisonChart.options.plugins.title.text = `${selectedDataType} Comparison Graph`;

                comparisonChart.data.labels = indexOfDay;
                comparisonChart.data.datasets[0].data = weatherdata1;
                comparisonChart.data.datasets[1].data = weatherdata2;
                comparisonChart.update();
            }

            // Helper function to format the date range as 'from: MM-DD-YYYY to MM-DD-YYYY'
            function formatDateRange(startDate, endDate) {
                var start = new Date(startDate);
                var end = new Date(endDate);
                return 'from: ' + formatDate(start) + ' to ' + formatDate(end);
            }

            // Helper function to format date as 'MM-DD-YYYY'
            function formatDate(date) {
                var day = date.getDate();
                var month = date.getMonth() + 1;
                var year = date.getFullYear();

                return (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day + '-' + year;
            }

            // Chart update logic for comparisonChart
            // Use the global comparisonChart variable here
            var comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
            comparisonChart = new Chart(comparisonCtx, {
                type: 'line', // Change the chart type to 'line'
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Period 1 Data',
                            data: [],
                            borderColor: 'blue', // Add border color for the curve
                            fill: true // enable fill for the curve
                        },
                        {
                            label: 'Period 2 Data',
                            data: [],
                            borderColor: 'red', // Add border color for the curve
                            fill: true // Enable fill for the curve
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Comparison Graph', // Default title before any update
                        },
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Index of the Day',
                            },
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Weather Data',
                            },
                        }
                    },
                },
            });


            console.log("Avant Handle FormComp submission");
            
            // Handle formComp submission
            $("#formcomp").submit(function (event) {
                event.preventDefault(); // Prevent default form submission behavior

                // Perform AJAX request to process_formcomp.php
                $.ajax({
                    type: "POST",
                    url: $(this).attr("action"),
                    data: $(this).serialize(), // Serialize form data
                    success: function (response) {
                        try {
                            // Parse the JSON response
                            var responseData = JSON.parse(response);

                            console.log("responseDataFormComp:", responseData);

                            // Update the comparison chart 
                            updateComparisonGraph(
                                responseData.IndexOfDay,
                                responseData.DatesPeriod1,
                                responseData.DatesPeriod2,
                                responseData.WeatherData1,
                                responseData.WeatherData2,
                                responseData.DataType
                            );

                        } catch (error) {
                            console.error("Error parsing JSON response:", error);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Error processing formComp:", error);
                    }
                });
            });
        });

    </script>
    
</body>
</html>