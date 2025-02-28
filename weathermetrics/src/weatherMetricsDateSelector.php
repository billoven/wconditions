<!-- Include Flatpickr CSS and JS, plus monthSelect plugin -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<style>
    .checkbox-group {
        display: grid;
        grid-template-columns: 1fr 1fr; /* 2 colonnes de même largeur */
        gap: 5px; /* Espace entre les cases à cocher */
    }

    .checkbox-item {
        display: flex;
        align-items: center;
    }
</style>

<!-- Date Range Selector Component -->
<div class="container d-flex align-items-center flex-nowrap" id="dateRangeSelector" style="gap: 5px;">
  
  <!-- Selection Type Dropdown for different range options -->
  <select id="rangeType" class="form-select form-select-sm" style="width: 130px;">
      <option value="dates" selected>Date Range</option>
      <option value="months">Month Range</option>
      <option value="years">Year Range</option>
      <option value="seasons">Season</option>
  </select>

  <!-- Date Range Picker Input with Clear Button -->
  <div id="dateRangePickerContainer" class="d-flex align-items-center" style="gap: 5px;">
    <input id="dateRangePicker" class="form-control form-control-sm" style="width: 180px;" placeholder="Select date range">
  </div>

  <!-- Month Range Fields -->
  <div id="monthRangeFields" class="d-flex d-none" style="gap: 8px;">
    <input id="monthFrom" class="form-control form-control-sm" style="width: 100px;" placeholder="From Month">
    <input id="monthTo" class="form-control form-control-sm" style="width: 100px;" placeholder="To Month">
  </div>

  <!-- Year Range Fields -->
  <div id="yearRangeFields" class="d-flex d-none" style="gap: 8px;">
    <select id="yearFrom" class="form-select form-select-sm" style="width: 100px;"></select>
    <select id="yearTo" class="form-select form-select-sm" style="width: 100px;"></select>
  </div>

  <!-- Season Selection Fields -->
  <div id="seasonFields" class="d-flex d-none" style="gap: 8px;">
    <select id="seasonSelect" class="form-select form-select-sm" style="width: 100px;">
      <option value="spring">Spring</option>
      <option value="summer">Summer</option>
      <option value="autumn">Autumn</option>
      <option value="winter">Winter</option>
    </select>
    <select id="seasonYear" class="form-select form-select-sm" style="width: 100px;"></select>
  </div>

  <!-- Add type="button" to prevent it from behaving as a submit button -->
  <button id="clearAll" type="button" class="btn btn-sm btn-outline-secondary">Clear All</button>

  <!-- Checkbox options for graph type selection -->
  <div class="checkbox-group">
      <div class="checkbox-item">
          <label>
              <input type="checkbox" name="by_day" value="true" checked onclick="toggleGraphVisibility()"> By Day
          </label>
      </div>
      <div class="checkbox-item">
          <label>
              <input type="checkbox" name="by_month" onclick="toggleGraphVisibility()"> By Month
          </label>
      </div>
      <div class="checkbox-item">
          <label>
              <input type="checkbox" name="by_year" onclick="toggleGraphVisibility()"> By Year
          </label>
      </div>
      <div class="checkbox-item">
          <label>
              <input type="checkbox" name="by_season" onclick="toggleGraphVisibility()"> By Season
          </label>
      </div>
  </div>
  <!-- Hidden fields for start and end dates to be sent to the backend -->
  <input type="hidden" id="start_date" name="start_date">
  <input type="hidden" id="end_date" name="end_date">

  <input type="submit" value="Generate Graph">
</div>

<script>
  // Get references to key DOM elements for easier access
  const rangeType = document.getElementById('rangeType');
  const dateRangePickerContainer = document.getElementById('dateRangePickerContainer');
  const dateRangePicker = flatpickr("#dateRangePicker", {
    mode: 'range',             // Enable selecting a range of dates
    dateFormat: 'Y-m-d',        // Set format for selected dates (e.g., 2024-01-01)
  });

  // Initialize Month Range fields with monthSelect plugin for month-only input
  const monthFrom = flatpickr("#monthFrom", {
    plugins: [new monthSelectPlugin({ shorthand: true, dateFormat: "Y-m", altFormat: "F Y", theme: "dark" })]
  });

  const monthTo = flatpickr("#monthTo", {
    plugins: [new monthSelectPlugin({ shorthand: true, dateFormat: "Y-m", altFormat: "F Y", theme: "dark" })]
  });

  // Containers for different date range fields
  const monthRangeFields = document.getElementById('monthRangeFields');
  const yearRangeFields = document.getElementById('yearRangeFields');
  const seasonFields = document.getElementById('seasonFields');
  const currentYear = new Date().getFullYear();

  // Function to populate year dropdowns dynamically with years from 2016 to current year
  function populateYearOptions(selectElement) {
    for (let year = currentYear; year >= 2016; year--) {
      const option = document.createElement('option');
      option.value = year;
      option.text = year;
      selectElement.add(option);
    }
  }


  // Function to update hidden start_date and end_date fields based on current selection
  function updateDateRange() {
    const selectedRange = rangeType.value;  // Get the selected range type
    let startDate = '';
    let endDate = '';

    // Calculate start and end dates based on range type
    if (selectedRange === 'dates') {
      const selectedDates = dateRangePicker.selectedDates;
      if (selectedDates.length === 2) {
        // Get the start and end dates without time zone adjustments
        // toISOString() converts the date to a string using the UTC time zone, which may cause the date to shift by one day, depending on your local time zone. By using 
        //     getFullYear(), getMonth(), and getDate(), 
        //     you're working directly with the raw date values, avoiding any time zone-related shifts.
        startDate = `${selectedDates[0].getFullYear()}-${String(selectedDates[0].getMonth() + 1).padStart(2, '0')}-${String(selectedDates[0].getDate()).padStart(2, '0')}`;
        endDate = `${selectedDates[1].getFullYear()}-${String(selectedDates[1].getMonth() + 1).padStart(2, '0')}-${String(selectedDates[1].getDate()).padStart(2, '0')}`;
      }
    } else if (selectedRange === 'months') {
      const fromMonth = monthFrom.selectedDates[0];
      const toMonth = monthTo.selectedDates[0];
      if (fromMonth && toMonth) {
        // Convert selected months to range start and end dates
        // Construct the start date in YYYY-MM-DD format
        // Year and month are extracted; day is set to "01" (first day).
        startDate = `${fromMonth.getFullYear()}-${String(fromMonth.getMonth() + 1).padStart(2, '0')}-01`;

        // Construct the end date in YYYY-MM-DD format
        // Year and month are extracted, and the day is set to the last day of the month.
        // Using `day 0` of the next month gives the correct last day (28, 29, 30, or 31).
        endDate = `${toMonth.getFullYear()}-${String(toMonth.getMonth() + 1).padStart(2, '0')}-${new Date(toMonth.getFullYear(), toMonth.getMonth() + 1, 0).getDate()}`;
      }
    } else if (selectedRange === 'years') {
      const fromYear = document.getElementById("yearFrom").value;
      const toYear = document.getElementById("yearTo").value;
      if (fromYear && toYear) {
        startDate = `${fromYear}-01-01`;  // Start of the from-year
        endDate = `${toYear}-12-31`;      // End of the to-year
      }
    } else if (selectedRange === 'seasons') {
      const season = document.getElementById("seasonSelect").value;
      const seasonYear = parseInt(document.getElementById("seasonYear").value, 10); // Parse as an integer
      if (season && seasonYear) {
          // Function to check if a year is a leap year
          const isLeapYear = (year) => {
              return (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);
          };

          // Determine start and end dates for the selected season
          switch (season) {
              case 'spring':
                  startDate = `${seasonYear}-03-01`;
                  endDate = `${seasonYear}-05-31`;
                  break;
              case 'summer':
                  startDate = `${seasonYear}-06-01`;
                  endDate = `${seasonYear}-08-31`;
                  break;
              case 'autumn':
                  startDate = `${seasonYear}-09-01`;
                  endDate = `${seasonYear}-11-30`;
                  break;
              case 'winter':
                  startDate = `${seasonYear - 1}-12-01`;
                  // Check if the winter end date should be February 28 or 29
                  endDate = isLeapYear(seasonYear) ? `${seasonYear}-02-29` : `${seasonYear}-02-28`;
                  break;
          }
      }
    }

    // Set hidden fields to the calculated start and end dates
    document.getElementById('start_date').value = startDate;
    document.getElementById('end_date').value = endDate;
  }

  document.getElementById("clearAll").addEventListener("click", () => {
    // Reset the rangeType to "dates"
    const rangeType = document.getElementById("rangeType");
    rangeType.value = "dates";

    // Reset input fields for all ranges
    document.getElementById("dateRangePicker").value = ""; // Clear date range
    document.getElementById("monthFrom").value = "";       // Clear "From Month"
    document.getElementById("monthTo").value = "";         // Clear "To Month"

    // Reset dropdowns properly
    document.getElementById("yearFrom").selectedIndex = 0; 
    document.getElementById("yearTo").selectedIndex = 0;   
    document.getElementById("seasonSelect").selectedIndex = 0; 
    document.getElementById("seasonYear").selectedIndex = 0;   

    // Clear hidden fields for start and end dates
    document.getElementById("start_date").value = "";
    document.getElementById("end_date").value = "";

    // Reset checkboxes (default: only 'By Day' checked)
    document.querySelector("input[name='by_day']").checked = true;
    document.querySelector("input[name='by_month']").checked = false;
    document.querySelector("input[name='by_year']").checked = false;
    document.querySelector("input[name='by_season']").checked = false;

    // Update visible sections to match "dates" as the default
    updateVisibleFields("dates");
});


    // Helper function to show/hide input fields based on the selected range type
    function updateVisibleFields(selectedType) {
      document.getElementById("dateRangePickerContainer").classList.toggle("d-none", selectedType !== "dates");
      document.getElementById("monthRangeFields").classList.toggle("d-none", selectedType !== "months");
      document.getElementById("yearRangeFields").classList.toggle("d-none", selectedType !== "years");
      document.getElementById("seasonFields").classList.toggle("d-none", selectedType !== "seasons");
    }

    // Automatically update visible fields when the rangeType changes
    document.getElementById("rangeType").addEventListener("change", (event) => {
      updateVisibleFields(event.target.value);
    });


  // Update field visibility based on selected range type (dates, months, years, or seasons)
  function updateFieldVisibility() {
    // Hide all fields initially
    dateRangePickerContainer.classList.add('d-none');
    monthRangeFields.classList.add('d-none');
    yearRangeFields.classList.add('d-none');
    seasonFields.classList.add('d-none');

    // Show specific field group based on current selection
    switch (rangeType.value) {
      case 'months': monthRangeFields.classList.remove('d-none'); break;
      case 'years': yearRangeFields.classList.remove('d-none'); break;
      case 'seasons': seasonFields.classList.remove('d-none'); break;
      default: dateRangePickerContainer.classList.remove('d-none'); break;
    }
    updateDateRange(); // Update hidden fields each time range type changes
  }

  // Populate year dropdowns on page load with the current year as the default
  populateYearOptions(document.getElementById('yearFrom'));
  populateYearOptions(document.getElementById('yearTo'));
  populateYearOptions(document.getElementById('seasonYear'));

  // Event listeners for updates when user selects different range options or changes input values
  rangeType.addEventListener("change", updateFieldVisibility);
  dateRangePicker.config.onChange.push(updateDateRange);
  monthFrom.config.onChange.push(updateDateRange);
  monthTo.config.onChange.push(updateDateRange);
  document.getElementById("yearFrom").addEventListener("change", updateDateRange);
  document.getElementById("yearTo").addEventListener("change", updateDateRange);
  document.getElementById("seasonSelect").addEventListener("change", updateDateRange);
  document.getElementById("seasonYear").addEventListener("change", updateDateRange);

</script>