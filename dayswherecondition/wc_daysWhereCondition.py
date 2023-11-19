#!/usr/bin/env python3
import argparse
import pymysql.cursors
import json
from datetime import datetime, timedelta

class WeatherAnalyzer:
    def __init__(self, config_filename, db_config_name):
        self.config = self.read_config(config_filename, db_config_name)

    def read_config(self, config_filename, db_config_name):
        try:
            with open(config_filename, "r") as config_file:
                config = json.load(config_file)
                db_config = config.get("dbConfigs", {}).get(db_config_name)
                if db_config:
                    return db_config
                else:
                    print(f"Database configuration '{db_config_name}' not found in config file.")
                    exit(1)
        except FileNotFoundError:
            print("Config file not found. Please create it with the appropriate database configurations.")
            exit(1)



    def generate_condition_description(self, conditions):
        # Initialize the description
        description = "Conditions set: "

        # Split the conditions into a list
        for condition in conditions.split(', '):
            # Split each condition into its components (parameter, operator, value)
            parts = condition.split()
            parameter, operator, value = parts[0], parts[1], parts[2]

            # Add the condition description to the overall description
            description += f"{parameter} {operator} {value}, "

        # Remove the trailing comma and space
        return description.rstrip(', ')


    def count_days_with_conditions(self, connection, start_date, end_date, conditions, min_consecutive):
        # Create a database cursor
        cursor = connection.cursor()

        # Split the conditions into a list
        condition_list = conditions.split(', ')

        # Build the SQL query
        sql = f"SELECT WC_Date FROM {self.config['tabledwc']} WHERE WC_Date BETWEEN '{start_date}' AND '{end_date}' AND "
        sql += ' AND '.join(condition_list)  # Join the conditions with 'AND' for the SQL query

        # Execute the SQL query
        cursor.execute(sql)

        # Fetch the results
        results = cursor.fetchall()

        if min_consecutive > 1:
            consecutive_periods = []
            current_period = None

            # Iterate through the query results
            for row in results:
                current_date = row['WC_Date']

                # Initialize or continue a consecutive period
                if current_period is None:
                    current_period = {'start_date': current_date, 'end_date': current_date, 'consecutive_count': 1}
                elif current_date == current_period['end_date'] + timedelta(days=1):
                    current_period['end_date'] = current_date
                    current_period['consecutive_count'] += 1
                else:
                    if current_period['consecutive_count'] >= min_consecutive:
                        consecutive_periods.append(current_period)

                    # Start a new consecutive period
                    current_period = {'start_date': current_date, 'end_date': current_date, 'consecutive_count': 1}

            # Check if the last consecutive period should be included
            if current_period is not None and current_period['consecutive_count'] >= min_consecutive:
                consecutive_periods.append(current_period)

            return consecutive_periods

        return len(results)  # Return the total count if not looking for consecutive periods

    def analyze(self, args):
        # Establish a database connection using the configuration provided in args
        connection = pymysql.connect(
            host=self.config["host"],
            user=self.config["username"],
            password=self.config["password"],
            db=self.config["database"],
            charset="utf8mb4",
            cursorclass=pymysql.cursors.DictCursor
        )

        # Convert start_date and end_date from string to datetime objects
        start_date = datetime.strptime(args.start_date, "%Y-%m-%d")
        end_date = datetime.strptime(args.end_date, "%Y-%m-%d")

        # Check if min_consecutive is specified
        if args.min_consecutive:
            # Calculate consecutive periods that meet the conditions
            consecutive_periods = self.count_days_with_conditions(connection, start_date, end_date, args.conditions, args.min_consecutive)
            print("Summary of Consecutive Days with Conditions:")

            # Check if consecutive periods were found
            if consecutive_periods:
                if isinstance(consecutive_periods, int):
                    # If the result is an integer, it represents the total consecutive days
                    print(f"Total consecutive days with conditions: {consecutive_periods}")
                else:
                    # If the result is a list of periods, sort them by consecutive count (in reverse) for display
                    consecutive_periods.sort(key=lambda x: x['consecutive_count'], reverse=True)
                    print(f"Total consecutive days with conditions: {sum(p['consecutive_count'] for p in consecutive_periods)}")
                    print(self.generate_condition_description(args.conditions))
                    print("Consecutive Periods (sorted from greatest to lowest):")

                    # Iterate through the consecutive periods and display their details
                    for period in consecutive_periods:
                        print(f"Start Date: {period['start_date']}, End Date: {period['end_date']}, Consecutive Days: {period['consecutive_count']}")
            else:
                print("No consecutive days with conditions found.")
        else:
            # If min_consecutive is not specified, calculate the total days that meet the conditions
            result = self.count_days_with_conditions(connection, start_date, end_date, args.conditions, 1)
            print("Summary of Days with Conditions:")
            print(f"Total days with conditions: {result}")
            print(self.generate_condition_description(args.conditions))

        # Close the database connection
        connection.close()


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Find consecutive days with weather conditions")
    parser.add_argument("--conditions", "-c", required=True, help="Weather conditions in SQL format")
    parser.add_argument("--start_date", "-s", required=True, help="Start date (YYYY-MM-DD)")
    parser.add_argument("--end_date", "-e", required=True, help="End date (YYYY-MM-DD)")
    parser.add_argument("--min_consecutive", "-m", type=int, help="Minimum consecutive days")

    args = parser.parse_args()

    # Create a WeatherAnalyzer instance with db1 configuration
    weather_analyzer = WeatherAnalyzer("db_config.json", "db1")
    weather_analyzer.analyze(args)

