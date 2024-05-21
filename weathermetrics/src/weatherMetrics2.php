<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temperature and Precipitation Graphs</title>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .graph-container {
            margin-bottom: 50px;
        }
    </style>
</head>
<body>
    <div class="graph-container">
        <h2>Daily Temperatures Graph</h2>
        <div id="DailyTempGraphContainer">
            <canvas id="DailyTempChart" width="1024" height="400"></canvas>  
        </div>
        <div id="DailyTempSummary"></div> <!-- Container for Daily Temp Summary -->
    </div>

    <div class="graph-container">
        <h2>Monthly Temperatures Graph</h2>
        <div id="MonthlyTempGraphContainer">
            <canvas id="MonthlyTempChart" width="1024" height="400"></canvas> 
        </div>
        <div id="MonthlyTempSummary"></div> <!-- Container for Monthly Temp Summary -->
    </div>

    <div class="graph-container">
        <h2>Daily Rainfall and Cumulative of the Period</h2>
        <div id="precipitationGraphContainer">
            <canvas id="precipitationChart" width="1024" height="400"></canvas>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Function to calculate statistics
            function calculateStatistics(data) {
                const sum = data.reduce((acc, val) => acc + val, 0);
                const avg = sum / data.length;
                const max = Math.max(...data);
                const min = Math.min(...data);
                return { avg, max, min };
            }

            // Function to update the summary display
            function updateSummary(containerId, datasets, normals) {
                const summaryContainer = document.getElementById(containerId);
                summaryContainer.innerHTML = ''; // Clear previous summary

                datasets.forEach((dataset, index) => {
                    if (dataset.label.includes('Norm')) return; // Skip normals

                    const stats = calculateStatistics(dataset.data);
                    let diff = '';
                    if (dataset.label.includes('Average Temperature')) {
                        diff = (stats.avg - normals.avg).toFixed(2);
                    } else if (dataset.label.includes('Maximum Temperature')) {
                        diff = (stats.avg - normals.max).toFixed(2);
                    } else if (dataset.label.includes('Minimum Temperature')) {
                        diff = (stats.avg - normals.min).toFixed(2);
                    }

                    const summary = document.createElement('div');
                    summary.innerHTML = `
                        <p><strong>${dataset.label}</strong></p>
                        <p>Average: ${stats.avg.toFixed(2)}°C (${diff >= 0 ? '+' : ''}${diff}°C)</p>
                        <p>Max: ${stats.max}°C</p>
                        <p>Min: ${stats.min}°C</p>
                    `;
                    summaryContainer.appendChild(summary);
                });

                // Display normals
                const normalsSummary = document.createElement('div');
                normalsSummary.innerHTML = `
                    <p><strong>Normals</strong></p>
                    <p>Average: ${normals.avg.toFixed(2)}°C</p>
                    <p>Max: ${normals.max.toFixed(2)}°C</p>
                    <p>Min: ${normals.min.toFixed(2)}°C</p>
                `;
                summaryContainer.appendChild(normalsSummary);
            }

            // Define the temperatureChart variable outside the function
            var temperatureChart;
            var monthlyAvgChart;
            var precipitationChart;

            // Initialize the daily temperature chart
            var ctx = document.getElementById('DailyTempChart').getContext('2d');
            temperatureChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Average Temperature',
                            data: [],
                            borderColor: 'blue',
                            backgroundColor: 'rgba(0, 0, 255, 0.1)',
                            fill: true
                        },
                        {
                            label: 'Maximum Temperature',
                            data: [],
                            borderColor: 'red',
                            backgroundColor: 'rgba(255, 0, 0, 0.1)',
                            fill: true
                        },
                        {
                            label: 'Minimum Temperature',
                            data:  [],
                            borderColor: 'green',
                            backgroundColor: 'rgba(0, 255, 0, 0.1)',
                            fill: true
                        },
                        {
                            label: 'Norm Avg Temp',
                            data:  [],
                            borderColor: 'blue',
                            backgroundColor: 'rgba(0, 0, 255, 0.1)',
                            fill: true,
                            pointRadius: 0,
                            borderDash: [5, 5]
                        },
                        {
                            label: 'Norm High Temp',
                            data:  [],
                            borderColor: 'red',
                            backgroundColor: 'rgba(0, 0, 255, 0.1)',
                            fill: true,
                            pointRadius: 0,
                            borderDash: [5, 5]
                        },
                        {
                            label: 'Norm Low Temp',
                            data:  [],
                            borderColor: 'green',
                            backgroundColor: 'rgba(0, 0, 255, 0.1)',
                            fill: true,
                            pointRadius: 0,
                            borderDash: [5, 5]
                        },
                        {
                            label: 'Moving Average',
                            data: [],
                            borderColor: 'yellow',
                            borderWidth: 2,
                            fill: false,
                            pointRadius: 0 // Hide data points
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Temperature'
                            }
                        }   
                    }
                }
            });

            // Update the daily temperature graph and summary
            function updateDailyTempGraph(dates, averages, maximums, minimums, AvgTempAvgs, AvgTempHighs, AvgTempLows, movingAverages) {
                temperatureChart.data.labels = dates;
                temperatureChart.data.datasets[0].data = averages;
                temperatureChart.data.datasets[1].data = maximums;
                temperatureChart.data.datasets[2].data = minimums;
                temperatureChart.data.datasets[3].data = AvgTempAvgs;
                temperatureChart.data.datasets[4].data = AvgTempHighs;
                temperatureChart.data.datasets[5].data = AvgTempLows;
                temperatureChart.data.datasets[6].data = movingAverages;
                temperatureChart.update();

                // Update the summary
                const normals = {
                    avg: calculateStatistics(AvgTempAvgs).avg,
                    max: calculateStatistics(AvgTempHighs).avg,
                    min: calculateStatistics(AvgTempLows).avg
                };
                updateSummary('DailyTempSummary', temperatureChart.data.datasets, normals);
            }

            // Initialize the monthly average temperature chart
            var monthlyAvgCtx = document.getElementById('MonthlyTempChart').getContext('2d');
            monthlyAvgChart = new Chart(monthlyAvgCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Average Temperature',
                            data: [],
                            backgroundColor: 'blue'
                        },
                        {
                            label: 'Maximum Temperature',
                            data: [],
                            backgroundColor: 'red'
                        },
                        {
                            label: 'Minimum Temperature',
                            data:  [],
                            backgroundColor: 'green'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Temperature'
                            }
                        }
                    }
                }
            });

            // Update the monthly temperature graph and summary
            function updateMonthlyTempGraph(labels, monthlyAvgData) {
                // Extract separate arrays for averages, maximums, and minimums from monthlyAvgData
                var averages = monthlyAvgData.map(data => parseFloat(data.avg));
                var maximums = monthlyAvgData.map(data => parseFloat(data.max));
                var minimums = monthlyAvgData.map(data => parseFloat(data.min));

                monthlyAvgChart.data.labels = labels;
                monthlyAvgChart.data.datasets[0].data = averages;
                monthlyAvgChart.data.datasets[1].data = maximums;
                monthlyAvgChart.data.datasets[2].data = minimums;
                monthlyAvgChart.update();

                // Calculate normals
                const normals = {
                    avg: calculateStatistics(averages).avg,
                    max: calculateStatistics(maximums).avg,
                    min: calculateStatistics(minimums).avg
                };

                // Update the summary
                updateSummary('MonthlyTempSummary', monthlyAvgChart.data.datasets, normals);
            }

            // Initialize the precipitation chart
            var precipCtx = document.getElementById('precipitationChart').getContext('2d');
            precipitationChart = new Chart(precipCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Daily Precipitation',
                            data: [],
                            borderColor: 'blue',
                            backgroundColor: 'rgba(0, 0, 255, 0.5)',
                            type: 'bar',
                            yAxisID: 'y'
                        },
                        {
                            label: 'Cumulative Precipitation',
                            data: [],
                            borderColor: 'green',
                            backgroundColor: 'rgba(0, 255, 0, 0.5)',
                            type: 'line',
                            yAxisID: 'y-axis-2'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Daily Precipitation (mm)',
                            },
                            ticks: {
                                beginAtZero: true
                            }
                        },
                        'y-axis-2': {
                            type: 'linear',
                            position: 'right',
                            grid: {
                                drawOnChartArea: false // only want the grid lines for one axis to show up
                            },
                            title: {
                                display: true,
                                text: 'Cumulative Precipitation (mm)'
                            }
                        }
                    }
                }
            });

            // Function to update the precipitation graph
            function updatePrecipitationGraph(labels, dailyData, cumulativeData) {
                precipitationChart.data.labels = labels;
                precipitationChart.data.datasets[0].data = dailyData;
                precipitationChart.data.datasets[1].data = cumulativeData;
                precipitationChart.update();
            }

            // Example data
            var dates = ['2024-01-01', '2024-01-02', '2024-01-03', '2024-01-04', '2024-01-05'];
            var dailyAvgTemps = [5, 7, 6, 8, 5];
            var dailyMaxTemps = [10, 12, 11, 13, 10];
            var dailyMinTemps = [0, 2, 1, 3, 0];
            var AvgTempAvgs = [6, 8, 7, 9, 6];
            var AvgTempHighs = [11, 13, 12, 14, 11];
            var AvgTempLows = [1, 3, 2, 4, 1];
            var movingAverages = [6, 7, 6.5, 7.5, 6];
            var monthlyLabels = ['January', 'February', 'March', 'April', 'May'];
            var monthlyData = [
                {avg: 5, max: 10, min: 0},
                {avg: 6, max: 11, min: 1},
                {avg: 7, max: 12, min: 2},
                {avg: 8, max: 13, min: 3},
                {avg: 9, max: 14, min: 4}
            ];

            var precipitationLabels = ['2024-01-01', '2024-01-02', '2024-01-03', '2024-01-04', '2024-01-05'];
            var dailyPrecipitation = [2, 0, 1, 3, 4];
            var cumulativePrecipitation = [2, 2, 3, 6, 10];

            // Update graphs with example data
            updateDailyTempGraph(dates, dailyAvgTemps, dailyMaxTemps, dailyMinTemps, AvgTempAvgs, AvgTempHighs, AvgTempLows, movingAverages);
            updateMonthlyTempGraph(monthlyLabels, monthlyData);
            updatePrecipitationGraph(precipitationLabels, dailyPrecipitation, cumulativePrecipitation);
        });
    </script>
</body>
</html>

