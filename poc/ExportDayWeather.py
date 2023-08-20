import argparse
import datetime
import pymysql
import csv

# MySQL connection settings
host = "192.168.17.10"
user = "admin"
database = "VillebonWeatherReport"

def export_weather_data(start_date, end_date, password):
    try:
        # Convert start_date and end_date strings to datetime objects
        start_date = datetime.datetime.strptime(start_date, "%Y-%m-%d")
        end_date = datetime.datetime.strptime(end_date, "%Y-%m-%d")

        # Check if end_date is greater than start_date
        if end_date <= start_date:
            print("Error: end_date must be greater than start_date.")
            return

        # Check if end_date is greater than the current day
        current_date = datetime.datetime.now().date()
        if end_date > current_date:
            print("Error: end_date cannot be greater than the current day.")
            return

        # Establish MySQL connection
        connection = pymysql.connect(host=host, user=user, password=password, database=database)
        cursor = connection.cursor()

        # MySQL query
        query = f"""
            SELECT DATE_FORMAT(WC_Date, '%d/%m/%Y'), WC_TempHigh, WC_TempAvg, WC_TempLow, WC_HumidityHigh,
                WC_HumidityAvg, WC_HumidityLow, WC_DewPointHigh, WC_DewPointAvg, WC_DewPointLow,
                WC_PressureHigh, WC_PressureLow, WC_WindSpeedMax, 'NULL', WC_GustSpeedMax, WC_PrecipitationSum
            FROM DayWeatherConditions
            WHERE WC_Date >= '{start_date}' AND WC_Date <= '{end_date}'
        """

        # Execute the query
        cursor.execute(query)

        # Fetch all rows from the result
        rows = cursor.fetchall()

        # Save the results to a CSV file
        csv_file_path = "/tmp/ExportedWeatherConditions.csv"
        with open(csv_file_path, "w", newline="") as csv_file:
            csv_writer = csv.writer(csv_file, delimiter=";")
            csv_writer.writerows(rows)

        # Close the cursor and connection
        cursor.close()
        connection.close()

        # Print the path of the exported CSV file
        print(f"Exported data to: {csv_file_path}")

        # Perform transformations on the CSV file
        transform_csv_file(csv_file_path)

    except (pymysql.Error, ValueError) as e:
        print(f"An error occurred: {str(e)}")


def transform_csv_file(csv_file_path):
    try:
        transformed_csv_file_path = "/tmp/TransformedWeatherConditions.csv"

        with open(csv_file_path, "r") as csv_file:
            with open(transformed_csv_file_path, "w", newline="") as transformed_csv_file:
                csv_reader = csv.reader(csv_file, delimiter=";")
                csv_writer = csv.writer(transformed_csv_file, delimiter=";")

                for row in csv_reader:
                    transformed_row = [cell.replace("\t", ";") if cell != "NULL" else "" for cell in row]
                    csv_writer.writerow(transformed_row)

        # Print the path of the transformed CSV file
        print(f"Transformed data saved to: {transformed_csv_file_path}")

    except IOError as e:
        print(f"An error occurred while transforming the CSV file: {str(e)}")


if __name__ == "__main__":
    # Create the argument parser
    parser = argparse.ArgumentParser(description="Export weather data within a date range.")

    # Add the arguments
    parser.add_argument("--start_date", type=str, help="Start date (YYYY-MM-DD)", required=True)
    parser.add_argument("--end_date", type=str, help="End date (YYYY-MM-DD)", required=True)
    parser.add_argument("--password", type=str, help="MySQL password", required=True)

    # Parse the command-line arguments
    args = parser.parse_args()

    # Call the function to export weather data
    export_weather_data(args.start_date, args.end_date, args.password)

