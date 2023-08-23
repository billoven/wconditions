<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fixed Table with Minimum Width for Columns</title>
<style>
    .table-container {
        width: 100%;
        overflow-x: auto;
    }

    .fixed-columns {
        position: sticky;
        left: 0;
        background-color: white;
        min-width: 100px; /* Adjust the value as needed */
    }

    .table-scroll {
        width: max-content;
        border-collapse: collapse;
    }

    .table-scroll th,
    .table-scroll td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
</style>
</head>
<body>
<div class="table-container">
    <table class="table-scroll">
        <thead>
            <tr>
                <th class="fixed-columns">Column 1</th>
                <th class="fixed-columns">Column 2</th>
                <th>Column 3</th>
                <th>Column 4</th>
                <th>Column 5</th>
                <th>Column 6</th>
                <th>Column 7</th>
                <!-- Add more columns as needed -->
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="fixed-columns">Row 1, Column 1</td>
                <td class="fixed-columns">Row 1, Column 2</td>
                <td>Row 1, Column 3</td>
                <td>Row 1, Column 4</td>
                <td>Row 1, Column 5</td>
                <td>Row 1, Column 6</td>
                <td>Row 1, Column 7</td>
                <!-- Add more cells as needed -->
            </tr>
            <!-- Add more rows as needed -->
        </tbody>
    </table>
</div>
</body>
</html>

