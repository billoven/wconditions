
// Function to update the daily temperature chart with new data
    function updateDailyTemp(dates, averages, maximums, minimums, AvgTempAvgs, AvgTempHighs, AvgTempLows, movingAverages) {

        const seriesData = [
            { name: 'Average Temperature', type: 'line', data: averages, smooth: true, lineStyle: { color: 'blue' }, areaStyle: { color: 'rgba(0, 0, 255, 0.1)' } },
            { name: 'Maximum Temperature', type: 'line', data: maximums, smooth: true, lineStyle: { color: 'red' }, areaStyle: { color: 'rgba(255, 0, 0, 0.1)' } },
            { name: 'Minimum Temperature', type: 'line', data: minimums, smooth: true, lineStyle: { color: 'green' }, areaStyle: { color: 'rgba(0, 255, 0, 0.1)' } },
            { name: '<?php global $selectedPeriod, $selectedCity; echo substr($selectedCity, 0, 2) . "-" . $selectedPeriod; ?> Avg Norm. Temp.', type: 'line', data: AvgTempAvgs, lineStyle: { color: 'blue', type: 'dashed' }, areaStyle: { color: 'rgba(0, 0, 255, 0.1)' }, symbol: 'none' },
            { name: '<?php global $selectedPeriod, $selectedCity; echo substr($selectedCity, 0, 2) . "-" . $selectedPeriod; ?> High Norm. Temp.', type: 'line', data: AvgTempHighs, lineStyle: { color: 'red', type: 'dashed' }, areaStyle: { color: 'rgba(255, 0, 0, 0.1)' }, symbol: 'none' },
            { name: '<?php global $selectedPeriod, $selectedCity; echo substr($selectedCity, 0, 2) . "-" . $selectedPeriod; ?> Low Norm. Temp.', type: 'line', data: AvgTempLows, lineStyle: { color: 'green', type: 'dashed' }, areaStyle: { color: 'rgba(0, 255, 0, 0.1)' }, symbol: 'none' },
            { name: 'Moving Average', type: 'line', data: movingAverages, lineStyle: { color: 'yellow', width: 2 }, smooth: true, symbol: 'none' }
        ];

        // Define and apply chart options
        const chartOptions = {
            title: { text: `Daily Temperatures (${periodTitle})`, left: 'center' },
            tooltip: {
                trigger: 'axis',
                formatter: params => params.map(item => `${item.marker} ${item.seriesName}: ${item.value}°C`).join('<br/>')
            },
            legend: { bottom: 0 },
            xAxis: { type: 'category', data: dates || [] },
            yAxis: { type: 'value', name: 'Temperature (°C)' },
            series: seriesData || []
        };

        // Ensure isDarkMode is defined, default to false if undefined
        const darkMode = document.getElementById("DailyDataDarkModeSwitch").checked;

        // Ensure dailyChart is properly initialized
        if (!dailyChart) {
            dailyChart = initializeChart('DailyData', darkMode);
        }

        if (dailyChart) {
            loadChart('DailyData', chartOptions, darkMode);
        }

        const columnsHeaders = [
            "Day", 
            "Mean",
            "Normal Mean",
            "Max", 
            "Normal Max",
            "Min",
            "Normal Min"
        ];

        // Update the weather table with new data
        updateWeatherTable("DailyData", dates, {
            "Average": averages,
            "NormAvg": AvgTempAvgs,
            "Max": maximums,
            "NormMax": AvgTempHighs,
            "Min": minimums,
            "NormMin": AvgTempLows
        }, columnsHeaders,"°C");

        // ============== Update Daily Temperature Metrics Summary =============
        const normals = {
            avg: calculateStatistics(AvgTempAvgs).avg,
            max: calculateStatistics(AvgTempHighs).avg,
            min: calculateStatistics(AvgTempLows).avg
        };

        // Update the summary section with calculated statistics
        updateSummary('DailyDataSummary', seriesData, normals);
    }

    // Function to update the monthly temperature chart with new data
    function updateMonthlyTempGraph(labels,monthlyAvgMeanData,monthlyAvgMaxData,monthlyAvgMinData) {

        // Update the existing chart instance (monthlyAvgChart) with new data
        // Extract separate arrays for averages, maximums, and minimums from monthlyAvgData
        var averages = monthlyAvgMeanData.map(parseFloat);
        var maximums = monthlyAvgMaxData.map(parseFloat);
        var minimums = monthlyAvgMinData.map(parseFloat);
        
        const seriesData = [
            { name: "Average Temperature", type: 'bar', data: averages, itemStyle: { color: 'blue' } },
            { name: "Maximum Temperature", type: 'bar', data: maximums, itemStyle: { color: 'red' } },
            { name: "Minimum Temperature", type: 'bar', data: minimums, itemStyle: { color: 'green' } }
        ];
        
        // Define and apply chart options
        const chartOptions = {
            title: { text: `Monthly Temperatures (${periodTitle})`, left: 'center' },
            tooltip: {
                trigger: 'axis',
                formatter: params => params.map(item => `${item.marker} ${item.seriesName}: ${item.value}°C`).join('<br/>')
            },
            legend: { bottom: 0 },
            xAxis: { type: 'category', data: labels || [] },
            yAxis: { type: 'value', name: 'Temperature (°C)' },
            series: seriesData || []
        };

        // Ensure isDarkMode is defined, default to false if undefined
        const darkMode = typeof isDarkMode !== 'undefined' ? isDarkMode : false;
        
        // Ensure monthlyChart is properly initialized
        if (!monthlyChart) {
            monthlyChart = initializeChart('MonthlyData', darkMode);
        }

        if (monthlyChart) {
            // Update the chart instance and store the reference
            loadChart('MonthlyData', chartOptions, darkMode);
        }

        // ============== Update Monthly Temperature Table =============
        // Sélectionnez le <tbody> de la table
        // Call the function to update the table
        const columnsHeaders = [
            "Month", 
            "Monthly Avg of Daily Mean Temps", 
            "Monthly Avg of Daily Max Temps", 
            "Monthly Avg of Daily Min Temps"
        ];

        // Appel de la fonction
        updateWeatherTable("MonthlyData", labels, {
            "Average": averages,
            "Max": maximums,
            "Min": minimums
        }, columnsHeaders,"°C");

    }

    function updateYearlyTempGraph(labels,yearlyAvgMeanData,yearlyAvgMaxData,yearlyAvgMinData) {

        // Update the existing chart instance (yearlyAvgChart) with new data
        // Extract separate arrays for averages, maximums, and minimums from monthlyAvgData
        var averages = yearlyAvgMeanData.map(parseFloat);
        var maximums = yearlyAvgMaxData.map(parseFloat);
        var minimums = yearlyAvgMinData.map(parseFloat);

        const seriesData = [
            { name: "Average Temperature", type: 'bar', data: averages, itemStyle: { color: 'blue' } },
            { name: "Maximum Temperature", type: 'bar', data: maximums, itemStyle: { color: 'red' } },
            { name: "Minimum Temperature", type: 'bar', data: minimums, itemStyle: { color: 'green' } }
        ];

        // Define and apply chart options
        const chartOptions = {
            title: { text: `Yearly Temperatures (${periodTitle})`, left: 'center' },
            tooltip: {
                trigger: 'axis',
                formatter: params => params.map(item => `${item.marker} ${item.seriesName}: ${item.value}°C`).join('<br/>')
            },
            legend: { bottom: 0 },
            xAxis: { type: 'category', data: labels || [] },
            yAxis: { type: 'value', name: 'Temperature (°C)' },
            series: seriesData || []
        };
        
        // Ensure isDarkMode is defined, default to false if undefined
        const darkMode = typeof isDarkMode !== 'undefined' ? isDarkMode : false;
        
        // Ensure yearlyChart is properly initialized
        if (!yearlyChart) {
            yearlyChart = initializeChart('YearlyData', darkMode);
        }

        if (yearlyChart) {
            // Update the chart instance and store the reference
            loadChart('YearlyData', chartOptions, darkMode);
        }
        
        // ============== Update Yearly Temperature Table =============
        // Sélectionnez le <tbody> de la table
        // Call the function to update the table
        const columnsHeaders = [
            "Year", 
            "Yearly Avg of Daily Mean Temps", 
            "Yearly Avg of Daily Max Temps", 
            "Yearly Avg of Daily Min Temps"
        ];

        // Appel de la fonction
        updateWeatherTable("YearlyData", labels, {
            "Average": averages,
            "Max": maximums,
            "Min": minimums
        }, columnsHeaders,"°C");         
    }

    function updateSeasonalTempGraph(labels, seasonalAvgMeanData,seasonalAvgMaxData,seasonalAvgMinData) {
        
        // Update the existing chart instance (seasonalAvgChart) with new data
        // Extract separate arrays for averages, maximums, and minimums from seasonalAvgData
        var averages = seasonalAvgMeanData.map(parseFloat);
        var maximums = seasonalAvgMaxData.map(parseFloat);
        var minimums = seasonalAvgMinData.map(parseFloat);

        // Construct the period title based on start_date and end_date
        const seriesData = [
            { name: "Average Temperature", type: 'bar', data: averages, itemStyle: { color: 'blue' } },
            { name: "Maximum Temperature", type: 'bar', data: maximums, itemStyle: { color: 'red' } },
            { name: "Minimum Temperature", type: 'bar', data: minimums, itemStyle: { color: 'green' } }
        ];
        
        // Define and apply chart options
        const chartOptions = {
            title: { text: `Seasonal Temperatures (${periodTitle})`, left: 'center' },
            tooltip: {
                trigger: 'axis',
                formatter: params => params.map(item => `${item.marker} ${item.seriesName}: ${item.value}°C`).join('<br/>')
            },
            legend: { bottom: 0 },
            xAxis: { type: 'category', data: labels || [] },
            yAxis: { type: 'value', name: 'Temperature (°C)' },
            series: seriesData || []
        };

        // Ensure isDarkMode is defined, default to false if undefined
        const darkMode = typeof isDarkMode !== 'undefined' ? isDarkMode : false;

        
        // Ensure monthlyChart is properly initialized
        if (!seasonalChart) {
            seasonalChart = initializeChart('SeasonalData', darkMode);
        }

        if (seasonalChart) {
            // Update the chart instance and store the reference
            loadChart('SeasonalData', chartOptions, darkMode);
        }

        // ============== Update Seasonal Temperature Table =============
        // Sélectionnez le <tbody> de la table
        // Call the function to update the table
        const columnsHeaders = [
            "Season", 
            "Seasonal Avg of Daily Mean Temps", 
            "Seasonal Avg of Daily Max Temps", 
            "Seasonal Avg of Daily Min Temps"
        ];

        // Appel de la fonction
        updateWeatherTable("SeasonalData", labels, {
            "Average": averages,
            "Max": maximums,
            "Min": minimums
        }, columnsHeaders,"°C");     
    }
    // 📌 Handle form submission for temperature data retrieval
    // This script listens for the form submission, validates the date inputs, 
    // sends an AJAX request to fetch data, and updates the corresponding graphs.
    const form = document.getElementById('weatherMetricsForm');

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault(); // Prevent default form submission behavior

            // Validate date inputs before proceeding
            if (!validateDates()) {
                return;
            }

            // Convert the form into a jQuery object for easier manipulation
            const $form = $(this);

            // Perform an AJAX POST request to submit the form data
            $.ajax({
                type: "POST",
                url: $form.attr("action"),  // Get the action URL from the form attribute
                data: $form.serialize(),    // Serialize form data for submission
                success: function (response) {  
                    try {
                        // Parse the JSON response from the server
                        var responseData = JSON.parse(response);

                        // Extract start and end dates for potential further use
                        start_date = responseData.start_date;
                        end_date = responseData.end_date;

                        periodTitle = `Period: from ${start_date} to ${end_date}`; // Define chart title based on date range

                        // Update the Daily Temperature graph with the received data
                        updateDailyTemp(
                            responseData.dates,
                            responseData.averages,
                            responseData.maximums,
                            responseData.minimums,
                            responseData.AvgTempAvgs,
                            responseData.AvgTempHighs,
                            responseData.AvgTempLows,
                            responseData.movingAverages
                        );

                        // Update the Monthly Temperature graph
                        updateMonthlyTempGraph(
                            responseData.monthlyAvgLabels,
                            responseData.monthlyAvgMeanData,
                            responseData.monthlyAvgMaxData,
                            responseData.monthlyAvgMinData
                        );

                        // Update the Yearly Temperature graph
                        updateYearlyTempGraph(
                            responseData.yearlyAvgLabels,
                            responseData.yearlyAvgMeanData,
                            responseData.yearlyAvgMaxData,
                            responseData.yearlyAvgMinData
                        );

                        // Update the Seasonal Temperature graph
                        updateSeasonalTempGraph(
                            responseData.seasonalAvgLabels,
                            responseData.seasonalAvgMeanData,
                            responseData.seasonalAvgMaxData,
                            responseData.seasonalAvgMinData
                        );

                    } catch (error) {
                        console.error("❌ Error parsing JSON response:", error);
                        console.error("Raw server response:", response);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("❌ AJAX request failed:", error);
                }
            });
        });
    }
