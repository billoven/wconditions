
// Function to update the daily Pressure chart with new data
    function updateDailyPressureGraph(dates, averages, maximums, minimums, AvgPressureAvgs, AvgPressureHighs, AvgPressureLows, unit) {

        const seriesData = [
            { name: 'Average Pressure', type: 'line', data: averages, smooth: true, lineStyle: { color: 'blue' }, areaStyle: { color: 'rgba(0, 0, 255, 0.1)' } },
            { name: 'Maximum Pressure', type: 'line', data: maximums, smooth: true, lineStyle: { color: 'red' }, areaStyle: { color: 'rgba(255, 0, 0, 0.1)' } },
            { name: 'Minimum Pressure', type: 'line', data: minimums, smooth: true, lineStyle: { color: 'green' }, areaStyle: { color: 'rgba(0, 255, 0, 0.1)' } },
            { name: '<?php global $selectedPeriod, $selectedCity; echo substr($selectedCity, 0, 2) . "-" . $selectedPeriod; ?> Avg Norm. Press.', type: 'line', data: AvgPressureAvgs, lineStyle: { color: 'blue', type: 'dashed' }, areaStyle: { color: 'rgba(0, 0, 255, 0.1)' }, symbol: 'none' },
            { name: '<?php global $selectedPeriod, $selectedCity; echo substr($selectedCity, 0, 2) . "-" . $selectedPeriod; ?> High Norm. Press.', type: 'line', data: AvgPressureHighs, lineStyle: { color: 'red', type: 'dashed' }, areaStyle: { color: 'rgba(255, 0, 0, 0.1)' }, symbol: 'none' },
            { name: '<?php global $selectedPeriod, $selectedCity; echo substr($selectedCity, 0, 2) . "-" . $selectedPeriod; ?> Low Norm. Press.', type: 'line', data: AvgPressureLows, lineStyle: { color: 'green', type: 'dashed' }, areaStyle: { color: 'rgba(0, 255, 0, 0.1)' }, symbol: 'none' }
        ];

        // Define and apply chart options
        const chartOptions = {
            title: { text: `Daily Pressures (${periodTitle})`, left: 'center' },
            tooltip: {
                trigger: 'axis',
                formatter: params => params.map(item => `${item.marker} ${item.seriesName}: ${item.value}°C`).join('<br/>')
            },
            legend: { bottom: 0 },
            xAxis: { type: 'category', data: dates || [] },
            yAxis: { type: 'value', name: `Pressure (${unit})`, min: 'dataMin', max: 'dataMax' },
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
            "NormAvg": AvgPressureAvgs,
            "Max": maximums,
            "NormMax": AvgPressureHighs,
            "Min": minimums,
            "NormMin": AvgPressureLows
        }, columnsHeaders,"Hpa");

    }

    // Function to update the monthly temperature chart with new data
    function updateMonthlyPressureGraph(labels,monthlyAvgMeanData,monthlyAvgMaxData,monthlyAvgMinData,unit) {

        // Update the existing chart instance (monthlyAvgChart) with new data
        // Extract separate arrays for averages, maximums, and minimums from monthlyAvgData
        var averages = monthlyAvgMeanData.map(parseFloat);
        var maximums = monthlyAvgMaxData.map(parseFloat);
        var minimums = monthlyAvgMinData.map(parseFloat);
        
        const seriesData = [
            { name: "Average Pressure", type: 'bar', data: averages, itemStyle: { color: 'blue' } },
            { name: "Maximum Pressure", type: 'bar', data: maximums, itemStyle: { color: 'red' } },
            { name: "Minimum Pressure", type: 'bar', data: minimums, itemStyle: { color: 'green' } }
        ];
        
        // Define and apply chart options
        const chartOptions = {
            title: { text: `Monthly Pressure (${periodTitle})`, left: 'center' },
            tooltip: {
                trigger: 'axis',
                formatter: params => params.map(item => `${item.marker} ${item.seriesName}: ${item.value}${unit}`).join('<br/>')
            },
            legend: { bottom: 0 },
            xAxis: { type: 'category', data: labels || [] },
            yAxis: { 
                type: 'value', 
                name: `Pressure ${unit}` , 
                min: function (value) { 
                    return value.min - 1;
                }, 
                max: function (value) {
                    return value.max + 1; 
                } 
            },
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

        // ============== Update Monthly Pressure Table =============
        // Sélectionnez le <tbody> de la table
        // Call the function to update the table
        const columnsHeaders = [
            "Month", 
            "Monthly Avg of Daily Mean Pressures", 
            "Monthly Avg of Daily Max Pressures", 
            "Monthly Avg of Daily Min Pressures"
        ];

        // Appel de la fonction
        updateWeatherTable("MonthlyData", labels, {
            "Average": averages,
            "Max": maximums,
            "Min": minimums
        }, columnsHeaders, "Hpa");

    }

    function updateYearlyPressureGraph(labels,yearlyAvgMeanData,yearlyAvgMaxData,yearlyAvgMinData,unit) {

        // Update the existing chart instance (yearlyAvgChart) with new data
        // Extract separate arrays for averages, maximums, and minimums from monthlyAvgData
        var averages = yearlyAvgMeanData.map(parseFloat);
        var maximums = yearlyAvgMaxData.map(parseFloat);
        var minimums = yearlyAvgMinData.map(parseFloat);

        const seriesData = [
            { name: "Average Pressure", type: 'bar', data: averages, itemStyle: { color: 'blue' } },
            { name: "Maximum Pressure", type: 'bar', data: maximums, itemStyle: { color: 'red' } },
            { name: "Minimum Pressure", type: 'bar', data: minimums, itemStyle: { color: 'green' } }
        ];

        // Define and apply chart options
        const chartOptions = {
            title: { text: `Yearly Pressures (${periodTitle})`, left: 'center' },
            tooltip: {
                trigger: 'axis',
                formatter: params => params.map(item => `${item.marker} ${item.seriesName}: ${item.value}${unit}`).join('<br/>')
            },
            legend: { bottom: 0 },
            xAxis: { type: 'category', data: labels || [] },
            yAxis: { 
                type: 'value', 
                name: `Pressure ${unit}` , 
                min: function (value) { 
                    return value.min - 1;
                }, 
                max: function (value) {
                    return value.max + 1; 
                } 
            },
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
        
        // ============== Update Yearly Pressure Table =============
        // Sélectionnez le <tbody> de la table
        // Call the function to update the table
        const columnsHeaders = [
            "Year", 
            "Yearly Avg of Daily Mean Pressures", 
            "Yearly Avg of Daily Max Pressures", 
            "Yearly Avg of Daily Min Pressures"
        ];

        // Appel de la fonction
        updateWeatherTable("YearlyData", labels, {
            "Average": averages,
            "Max": maximums,
            "Min": minimums
        }, columnsHeaders, "Hpa");         
    }

    function updateSeasonalPressureGraph(labels, seasonalAvgMeanData,seasonalAvgMaxData,seasonalAvgMinData,unit) {
        
        // Update the existing chart instance (seasonalAvgChart) with new data
        // Extract separate arrays for averages, maximums, and minimums from seasonalAvgData
        var averages = seasonalAvgMeanData.map(parseFloat);
        var maximums = seasonalAvgMaxData.map(parseFloat);
        var minimums = seasonalAvgMinData.map(parseFloat);

        // Construct the period title based on start_date and end_date
        const seriesData = [
            { name: "Average Pressure", type: 'bar', data: averages, itemStyle: { color: 'blue' } },
            { name: "Maximum Pressure", type: 'bar', data: maximums, itemStyle: { color: 'red' } },
            { name: "Minimum Pressure", type: 'bar', data: minimums, itemStyle: { color: 'green' } }
        ];
        
        // Define and apply chart options
        const chartOptions = {
            title: { text: `Seasonal Pressures (${periodTitle})`, left: 'center' },
            tooltip: {
                trigger: 'axis',
                formatter: params => params.map(item => `${item.marker} ${item.seriesName}: ${item.value}${unit}`).join('<br/>')
            },
            legend: { bottom: 0 },
            xAxis: { type: 'category', data: labels || [] },
            yAxis: { 
                type: 'value', 
                name: `Pressure ${unit}` , 
                min: function (value) { 
                    return value.min - 1;
                }, 
                max: function (value) {
                    return value.max + 1; 
                } 
            },
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

        // ============== Update Seasonal Pressure Table =============
        // Sélectionnez le <tbody> de la table
        // Call the function to update the table
        const columnsHeaders = [
            "Season", 
            "Seasonal Avg of Daily Mean Pressures", 
            "Seasonal Avg of Daily Max Pressures", 
            "Seasonal Avg of Daily Min Pressures"
        ];

        // Appel de la fonction
        updateWeatherTable("SeasonalData", labels, {
            "Average": averages,
            "Max": maximums,
            "Min": minimums
        }, columnsHeaders,"Hpa");     
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

                        // Update the Daily Pressure graph with the received data
                        updateDailyPressureGraph(
                            responseData.dates,
                            responseData.averages,
                            responseData.maximums,
                            responseData.minimums,
                            responseData.AvgPressureAvgs,
                            responseData.AvgPressureHighs,
                            responseData.AvgPressureLows,
                            "Hpa"
                        );

                        // Update the Monthly Pressure graph
                        updateMonthlyPressureGraph(
                            responseData.monthlyAvgLabels,
                            responseData.monthlyAvgMeanData,
                            responseData.monthlyAvgMaxData,
                            responseData.monthlyAvgMinData,
                            "Hpa"
                        );

                        // Update the Yearly Pressure graph
                        updateYearlyPressureGraph(
                            responseData.yearlyAvgLabels,
                            responseData.yearlyAvgMeanData,
                            responseData.yearlyAvgMaxData,
                            responseData.yearlyAvgMinData,
                            "Hpa"
                        );

                        // Update the Seasonal Pressure graph
                        updateSeasonalPressureGraph(
                            responseData.seasonalAvgLabels,
                            responseData.seasonalAvgMeanData,
                            responseData.seasonalAvgMaxData,
                            responseData.seasonalAvgMinData,
                            "Hpa"
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
