
// Function to update the daily Rainfall chart with new data
    function updateDailyRainGraph(dates, precipitations, CumulPrecipitations, CumulNormPrecipitations ) {

        const seriesData = [
            { 
                name: 'Precipitation', 
                type: 'bar', 
                data: precipitations, 
                yAxisIndex: 0, // Use left Y-axis
                itemStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(0, 123, 255, 0.7)' },
                        { offset: 1, color: 'rgba(0, 123, 255, 0.1)' }
                    ])
                }
            },
            { 
                name: 'Cumulative Precipitations', 
                type: 'line', 
                data: CumulPrecipitations, 
                smooth: true, 
                yAxisIndex: 1, // Use right Y-axis
                lineStyle: { color: 'green' }
            },
            { 
                name: '<?php global $selectedPeriod, $selectedCity; echo substr($selectedCity, 0, 2) . "-" . $selectedPeriod; ?> Cumulative Normal Precipitations', 
                type: 'line', 
                data: CumulNormPrecipitations, 
                yAxisIndex: 1, // Use right Y-axis
                lineStyle: { color: 'red', width: 2, type: 'dashed'} 
            }
        ];

        // Define and apply chart options
        const chartOptions = {
            title: { text: `Daily Rainfall (${periodTitle})`, left: 'center' },
            tooltip: {
                trigger: 'axis',
                formatter: params => params.map(item => `${item.marker} ${item.seriesName}: ${item.value}mm`).join('<br/>')
            },
            legend: { bottom: 0 },
            xAxis: { type: 'category', data: dates || [] },
            yAxis: [
                {
                    type: 'value',
                    name: 'Daily Rainfall (mm)',
                    position: 'left',
                    axisLine: { show: true, lineStyle: { color: 'blue' } }
                },
                {
                    type: 'value',
                    name: 'Cumulative Rainfall (mm)',
                    position: 'right',
                    axisLine: { show: true, lineStyle: { color: 'green' } }
                }
            ],
            series: seriesData || []
        };

        // Ensure isDarkMode is defined, default to false if undefined
        const darkMode = document.getElementById("DailyDataDarkModeSwitch").checked;

        // Ensure dailyChart is properly initialized and updated
        dailyChart = dailyChart || initializeChart('DailyData', darkMode);
        loadChart('DailyData', chartOptions, darkMode);

        const columnsHeaders = [
            "Day", 
            "Precipitations",
            "Cumulative Precipitations",
            "Cumul. Norm. Precipitations"
        ];

        // Update the weather table with new data
        updateWeatherTable("DailyData", dates, {
            "Precipitations": precipitations,
            "Cumulative Precipitations": CumulPrecipitations,
            "Cumul. Norm. Precipitations": CumulNormPrecipitations
        }, columnsHeaders,"mm");

    }

    // Function to update the monthly temperature chart with new data
    function updateMonthlyRainGraph(labels,monthlyCumulPrecipitations,monthlyCumulNormPrecipitations) {

        // Update the existing chart instance (monthlyDataChart) with new data
        // Extract separate arrays for averages, maximums, and minimums from monthlyAvgData
        var cumulPrecipitations = monthlyCumulPrecipitations.map(parseFloat);
        var cumulNormPrecipitations = monthlyCumulNormPrecipitations.map(parseFloat);
       
        const seriesData = [
            {
            name: 'Cumulative Precipitations', 
                type: 'bar', 
                data: cumulPrecipitations, 
                itemStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(0, 123, 255, 0.7)' },
                        { offset: 1, color: 'rgba(0, 123, 255, 0.1)' }
                    ])
                } },
                {
            name: '<?php global $selectedPeriod, $selectedCity; echo substr($selectedCity, 0, 2) . "-" . $selectedPeriod; ?> Monthly Norm. Precipitations', 
                type: 'bar', 
                data: cumulNormPrecipitations, 
                itemStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(189, 190, 205, 0.52)' },
                        { offset: 1, color: 'rgba(0, 123, 255, 0.1)' }
                    ])
                } }
        ];
        
        // Define and apply chart options
        const chartOptions = {
            title: { text: `Monthly Precipitations (${periodTitle})`, left: 'center' },
            tooltip: {
                trigger: 'axis',
                formatter: params => params.map(item => `${item.marker} ${item.seriesName}: ${item.value}mm`).join('<br/>')
            },
            legend: { bottom: 0 },
            xAxis: { type: 'category', data: labels || [] },
            yAxis: { type: 'value', name: 'Cumulative Precipitations (Mm)' },
            series: seriesData || []
        };

        // Ensure isDarkMode is defined, default to false if undefined
        const darkMode = typeof isDarkMode !== 'undefined' ? isDarkMode : false;
        
        // Ensure monthlyChart is properly initialized and updated
        monthlyChart = monthlyChart || initializeChart('MonthlyData', darkMode);
        loadChart('MonthlyData', chartOptions, darkMode);

        // ============== Update Monthly Precipitations Table =============
        // Sélectionnez le <tbody> de la table
        // Call the function to update the table
        const columnsHeaders = [
            "Month", 
            "Monthly Precipitations", 
            "Monthly Normal Precipitations"
        ];

        // Appel de la fonction
        updateWeatherTable("MonthlyData", labels, {
            "MonthlyPrecipit": cumulPrecipitations,
            "MonthlyNormPrecipit": cumulNormPrecipitations
        }, columnsHeaders,"mm");

    }

    function updateYearlyRainGraph(labels,yearlyCumulPrecipitations,yearlyCumulNormPrecipitations) {

        // Update the existing chart instance (YearlyDataChart) with new data
        // Extract separate arrays for averages, maximums, and minimums from monthlyAvgData
        var cumulPrecipitations = yearlyCumulPrecipitations.map(parseFloat);
        var cumulNormPrecipitations = yearlyCumulNormPrecipitations.map(parseFloat);
       
        const seriesData = [
            {
            name: 'Cumulative Precipitations', 
                type: 'bar', 
                data: cumulPrecipitations, 
                itemStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(0, 123, 255, 0.7)' },
                        { offset: 1, color: 'rgba(0, 123, 255, 0.1)' }
                    ])
                } },
                {
            name: '<?php global $selectedPeriod, $selectedCity; echo substr($selectedCity, 0, 2) . "-" . $selectedPeriod; ?> Yearly Norm. Precipitations', 
                type: 'bar', 
                data: cumulNormPrecipitations, 
                itemStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(189, 190, 205, 0.52)' },
                        { offset: 1, color: 'rgba(0, 123, 255, 0.1)' }
                    ])
                } }
        ];
        
        // Define and apply chart options
        const chartOptions = {
            title: { text: `Yearly Precipitations (${periodTitle})`, left: 'center' },
            tooltip: {
                trigger: 'axis',
                formatter: params => params.map(item => `${item.marker} ${item.seriesName}: ${item.value}mm`).join('<br/>')
            },
            legend: { bottom: 0 },
            xAxis: { type: 'category', data: labels || [] },
            yAxis: { type: 'value', name: 'Cumulative Precipitations (Mm)' },
            series: seriesData || []
        };

        // Ensure isDarkMode is defined, default to false if undefined
        const darkMode = typeof isDarkMode !== 'undefined' ? isDarkMode : false;
        
        // Ensure yearlyChart is properly initialized and updated
        yearlyChart = yearlyChart || initializeChart('YearlyData', darkMode);
        loadChart('YearlyData', chartOptions, darkMode);

        // ============== Update Yearly Precipitations Table =============
        // Sélectionnez le <tbody> de la table
        // Call the function to update the table
        const columnsHeaders = [
            "Year", 
            "Yearly Precipitations", 
            "Yearly Normal Precipitations"
        ];

        // Appel de la fonction
        updateWeatherTable("YearlyData", labels, {
            "YearlyPrecipit": cumulPrecipitations,
            "YearlyNormPrecipit": cumulNormPrecipitations
        }, columnsHeaders,"mm");    
    }

    function updateSeasonalRainGraph(labels, seasonalCumulPrecipitations,seasonalCumulNormPrecipitations) {
        // Update the existing chart instance (monthlyDataChart) with new data
        // Extract separate arrays for averages, maximums, and minimums from monthlyAvgData
        var cumulPrecipitations = seasonalCumulPrecipitations.map(parseFloat);
        var cumulNormPrecipitations = seasonalCumulNormPrecipitations.map(parseFloat);
       
        const seriesData = [
            {
            name: 'Cumulative Precipitations', 
                type: 'bar', 
                data: cumulPrecipitations, 
                itemStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(0, 123, 255, 0.7)' },
                        { offset: 1, color: 'rgba(0, 123, 255, 0.1)' }
                    ])
                } },
                {
            name: '<?php global $selectedPeriod, $selectedCity; echo substr($selectedCity, 0, 2) . "-" . $selectedPeriod; ?> Seasonal Norm. Precipitations', 
                type: 'bar', 
                data: cumulNormPrecipitations, 
                itemStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(189, 190, 205, 0.52)' },
                        { offset: 1, color: 'rgba(0, 123, 255, 0.1)' }
                    ])
                } }
        ];
        
        // Define and apply chart options
        const chartOptions = {
            title: { text: `Seasonal Precipitations (${periodTitle})`, left: 'center' },
            tooltip: {
                trigger: 'axis',
                formatter: params => params.map(item => `${item.marker} ${item.seriesName}: ${item.value}mm`).join('<br/>')
            },
            legend: { bottom: 0 },
            xAxis: { type: 'category', data: labels || [] },
            yAxis: { type: 'value', name: 'Cumulative Precipitations (Mm)' },
            series: seriesData || []
        };

        // Ensure isDarkMode is defined, default to false if undefined
        const darkMode = typeof isDarkMode !== 'undefined' ? isDarkMode : false;
        
        // Ensure seasonalChart is properly initialized and updated
        seasonalChart = seasonalChart || initializeChart('SeasonalData', darkMode);
        loadChart('SeasonalData', chartOptions, darkMode);

        // ============== Update Seasonal Precipitations Table =============
        // Sélectionnez le <tbody> de la table
        // Call the function to update the table
        const columnsHeaders = [
            "Season", 
            "Seasonal Precipitations", 
            "Seasonal Normal Precipitations"
        ];

        // Appel de la fonction
        updateWeatherTable("SeasonalData", labels, {
            "SeasonalPrecipit": cumulPrecipitations,
            "SeasonalNormPrecipit": cumulNormPrecipitations
        }, columnsHeaders,"mm");        
         
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
                        updateDailyRainGraph(
                            responseData.dates,
                            responseData.precipitations,
                            responseData.CumulPrecipitations,
                            responseData.CumulNormPrecipitations
                        );

                        // Update the Monthly Temperature graph
                        updateMonthlyRainGraph(
                            responseData.monthlyLabels,
                            responseData.monthlyCumulPrecipitations,
                            responseData.monthlyCumNormPrecipitations
                        );

                        // Update the Yearly Temperature graph
                        updateYearlyRainGraph(
                            responseData.yearlyLabels,
                            responseData.yearlyCumulPrecipitations,
                            responseData.yearlyCumNormPrecipitations
                        );

                        // Update the Seasonal Temperature graph
                        updateSeasonalRainGraph(
                            responseData.seasonalLabels,
                            responseData.seasonalCumulPrecipitations,
                            responseData.seasonalCumNormPrecipitations
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
