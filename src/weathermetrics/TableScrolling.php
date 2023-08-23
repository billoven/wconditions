<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Horizontal Scrolling Table Example</title>
  <style>
    .container {
      width: 400px;
      border: 1px solid #ccc;
      overflow-x: scroll;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="container">
    <table>
      <thead>
        <tr>
          <th>Header 1</th>
          <th>Header 2</th>
          <th>Header 3</th>
          <th>Header 4</th>
          <th>Header 5</th>
          <!-- Add more headers as needed -->
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Data 1</td>
          <td>Data 2</td>
          <td>Data 3</td>
          <td>Data 4</td>
          <td>Data 5</td>
          <!-- Add more rows and data cells as needed -->
        </tr>
        <!-- Add more rows as needed -->
      </tbody>
    </table>
  </div>
</body>
</html>
