#!/usr/bin/env python3
import argparse
import json
import pymysql
import csv
import decimal

class DatabaseConnection:
    def __init__(self, config_file):
        with open(config_file, 'r') as config_file:
            config_data = json.load(config_file)

        # Assuming you want to access the 'db1' configuration
        db_config = config_data["dbConfigs"]["db1"]

        self.db_host = db_config['host']
        self.db_user = db_config['username']
        self.db_password = db_config['password']
        self.db_name = db_config['database'] 
          
    def connect(self):
        try:
            connection = pymysql.connect(
                host=self.db_host,
                user=self.db_user,
                password=self.db_password,
                database=self.db_name,
                cursorclass=pymysql.cursors.DictCursor
            )
            self.cursor = connection.cursor()
            return connection
        except pymysql.MySQLError as err:
            print(f"Error: {err}")
            return None

    def disconnect(self, connection):
        connection.close()

    def execute_query(self, query):
        self.cursor.execute(query)
        return self.cursor.fetchall()

class DataExporter:
    def __init__(self, connection, start_date, end_date):
        self.connection = connection
        self.start_date = start_date
        self.end_date = end_date

    def fetch_data(self):
        query = f"""
        SELECT DATE_FORMAT(WC_Date, '%d/%m/%Y') AS Date, WC_TempHigh, WC_TempAvg, WC_TempLow, WC_HumidityHigh, 
               WC_HumidityAvg, WC_HumidityLow, WC_DewPointHigh, WC_DewPointAvg, WC_DewPointLow, 
               WC_PressureHigh, WC_PressureLow, WC_WindSpeedMax, NULL AS Placeholder, WC_GustSpeedMax, WC_PrecipitationSum 
        FROM DayWeatherConditions
        WHERE WC_Date >= '{self.start_date}' AND WC_Date <= '{self.end_date}'
        """
        result = self.connection.execute_query(query)
        return [dict(row) for row in result]

class CSVExporter:
    def __init__(self, data, output_file):
        self.data = data
        self.output_file = output_file

    def format_decimal(self, value):
        # Format a decimal number as a string with a comma as the decimal separator
        return str(value).replace('.', ',')

    def export_to_csv(self):
        if self.data:
            with open(self.output_file, 'w', newline='') as csv_file:
                csv_writer = csv.writer(csv_file, delimiter=';', quoting=csv.QUOTE_MINIMAL, escapechar='\\', quotechar='"')

                # Write column headers from the cursor's description
                csv_writer.writerow(self.data[0].keys())  # Use keys to extract column headers

                # Write data (excluding the first row)
                for row in self.data[1:]:
                    formatted_row = [self.format_decimal(value) if isinstance(value, decimal.Decimal) else value for value in row.values()]
                    csv_writer.writerow(formatted_row)

            print(f"Data saved to {self.output_file}")
        else:
            print("No data to export.")


def main():
    parser = argparse.ArgumentParser(description="Generate CSV from MySQL database")
    parser.add_argument("-s", "--start-date", required=True, help="Start date in format 'YYYY-MM-DD'")
    parser.add_argument("-e", "--end-date", required=True, help="End date in format 'YYYY-MM-DD'")
    parser.add_argument("-c", "--config", required=True, help="Path to the config file")
    parser.add_argument("-o", "--output", required=True, help="Output CSV file")

    args = parser.parse_args()

    connection = DatabaseConnection(args.config)
    db_conn = connection.connect()

    if db_conn:
        data_exporter = DataExporter(connection, args.start_date, args.end_date)
        data = data_exporter.fetch_data()

        if data:
            csv_exporter = CSVExporter(data, args.output) 
            csv_exporter.export_to_csv()

        connection.disconnect(db_conn)

if __name__ == "__main__":
    main()




