import argparse
import pymysql
import json
from decimal import Decimal
from datetime import datetime, date

def calculate_average(data, column_name, precision=1):
    values = [row[column_name] for row in data if row[column_name] is not None]
    return round(sum(values) / len(values), precision) if values else None

def find_max_with_dates(data, column_name):
    max_value = max(entry[column_name] if entry[column_name] is not None else float('-inf') for entry in data)
    max_entries = [{'Date': str(entry['Date']), 'Value': str(entry[column_name])} for entry in data if entry[column_name] == max_value]    
    return max_entries

def find_min_with_dates(data, column_name):
    # Extract the minimum value using the lambda function
    min_value = min(data, key=lambda x: x[column_name] if x[column_name] is not None else float('inf'))[column_name]

    # Extract all entries with the minimum value
    min_entries = [{'Date': str(entry['Date']), 'Value': str(entry[column_name])} for entry in data if entry[column_name] == min_value]

    return min_entries

def calculate_average_days_precipitation(data, precipitation_range):
    days_precipitation_by_year = {}
    for row in data:
        year = row['Date'].year
        if year not in days_precipitation_by_year:
            days_precipitation_by_year[year] = 0
        if row['PrecipitationSum'] is not None and precipitation_range[0] <= row['PrecipitationSum'] < precipitation_range[1]:
            days_precipitation_by_year[year] += 1

    avg_days_precipitation = round(sum(days_precipitation_by_year.values()) / len(days_precipitation_by_year))
    return avg_days_precipitation

def calculate_yearly_average_precipitation(data):
    yearly_precipitation = {}

    for row in data:
        year = row['Date'].year
        if year not in yearly_precipitation:
            yearly_precipitation[year] = 0
        
        if row['PrecipitationSum'] is not None:
            yearly_precipitation[year] += row['PrecipitationSum']

    total_precipitation = sum(yearly_precipitation.values())
    total_years = len(yearly_precipitation)

    yearly_average_precipitation = round(total_precipitation / total_years, 1) if total_years > 0 else None

    return yearly_average_precipitation

def calculate_average_days_temp_avg(data, temp_avg_range):
    days_temp_avg_by_year = {}
    for row in data:
        year = row['Date'].year
        if year not in days_temp_avg_by_year:
            days_temp_avg_by_year[year] = 0
        if row['TempAvg'] is not None and temp_avg_range[0] <= row['TempAvg'] <= temp_avg_range[1]:
            days_temp_avg_by_year[year] += 1

    avg_days_temp_avg = round(sum(days_temp_avg_by_year.values()) / len(days_temp_avg_by_year))
    return avg_days_temp_avg

def calculate_average_days_temp_high(data, temp_high_range):
    days_temp_high_by_year = {}
    for row in data:
        year = row['Date'].year
        if year not in days_temp_high_by_year:
            days_temp_high_by_year[year] = 0
        if row['TempHigh'] is not None and temp_high_range[0] <= row['TempHigh'] <= temp_high_range[1]:
            days_temp_high_by_year[year] += 1

    avg_days_temp_high = round(sum(days_temp_high_by_year.values()) / len(days_temp_high_by_year))
    return avg_days_temp_high

def calculate_average_days_temp_low(data, temp_low_range):
    days_temp_low_by_year = {}
    for row in data:
        year = row['Date'].year
        if year not in days_temp_low_by_year:
            days_temp_low_by_year[year] = 0
        if row['TempLow'] is not None and temp_low_range[0] < row['TempLow'] <= temp_low_range[1]:
            days_temp_low_by_year[year] += 1

    avg_days_temp_low = round(sum(days_temp_low_by_year.values()) / len(days_temp_low_by_year))
    return avg_days_temp_low

def write_to_json(data, filename):
    # Creating a dictionary to hold comments for each field
    comments = {
        'Avg_TempAvg': 'Average temperature of daily mean temperatures',
        'Avg_TempHigh': 'Average temperature of daily maximum temperatures',
        'Avg_TempLow': 'Average temperature of daily minimum temperatures',
        'PrecipitationSum': 'Total daily precipitations',
        'Max_Daily_Precipitation': 'Maximum daily precipitation(s) with the dates',
        'Avg_Daily_Precipitation': 'Average daily precipitations of the period',
        'Max_TempHigh': 'Highest daily temperature record with the dates',
        'Min_TempLow': 'Lowest daily temperature record with the dates',
        'Max_TempAvg': 'Highest daily average temperature record with the dates',
        'Min_TempAvg': 'Lowest daily average temperature record with the dates',
        'Avg_Days_TempLow_-5': 'Annual average days with minimum temperature <= -5°C by year',
        'Avg_Days_TempLow_0': 'Annual average days with minimum temperature <= 0°C by year',
        'Avg_Days_TempLow_0_5': 'Annual average days with minimum temperature > 0°C and <= 5°C by year',
        'Avg_Days_TempLow_5_10': 'Annual average days with minimum temperature > 5°C and <= 10°C by year',
        'Avg_Days_TempLow_10_15': 'Annual average days with minimum temperature > 10°C and <= 15°C by year',
        'Avg_Days_TempLow_15_20': 'Annual average days with minimum temperature > 15°C and <= 20°C by year',
        'Avg_Days_TempLow_20': 'Annual average days with minimum temperature >= 20°C by year',
        'Avg_Days_TempHigh_0': 'Annual average days with maximum temperature <= 0°C by year',
        'Avg_Days_TempHigh_30': 'Number of days with TempHigh >= 30°C by year',
        'Avg_Days_TempHigh_0_5': 'Annual average days with maximum temperature > 0°C and <= 5°C by year',
        'Avg_Days_TempHigh_5_10': 'Annual average days with maximum temperature > 5°C and <= 10°C by year',
        'Avg_Days_TempHigh_10_15': 'Annual average days with maximum temperature > 10°C and <= 15°C by year',
        'Avg_Days_TempHigh_15_20': 'Annual average days with maximum temperature > 15°C and <= 20°C by year',
        'Avg_Days_TempHigh_20': 'Annual average days with maximum temperature >= 20°C by year',
        'Avg_Days_TempAvg_0': 'Annual average days with average temperature <= 0°C by year',
        'Avg_Days_TempAvg_25': 'NAnnual average days with average temperature >= 25°C by year',
        'Avg_Days_TempAvg_0_5': 'Annual average days with average temperature > 0°C and <= 5°C by year',
        'Avg_Days_TempAvg_5_10': 'Annual average days with average temperature > 5°C and <= 10°C by year',
        'Avg_Days_TempAvg_10_15': 'Annual average days with average temperature > 10°C and <= 15°C by year',
        'Avg_Days_TempAvg_15_20': 'Annual average days with average temperature > 15°C and <= 20°C by year',
        'Avg_Days_TempAvg_20': 'Annual average days with average temperature >= 20°C by year',
        'Avg_Days_Precipitation_1': 'Annual Average Days with Precipitation >= 1mm',
        'Avg_Days_Precipitation_0': 'Annual Average Days with Precipitation > 0mm',
        'Avg_Days_Precipitation_1_5': 'Annual Average Days with Precipitation >= 1mm and < 5mm',
        'Avg_Days_Precipitation_5_10': 'Annual Average Days with Precipitation >= 5mm and < 10mm',
        'Avg_Days_Precipitation_10': 'Annual Average Days with Precipitation >= 10mm',
        'Avg_Days_Precipitation_20': 'Annual Average Days with Precipitation >= 20mm',
        'Yearly_Avg_Precipitation': 'Annual average precipitation',
    }
    
    with open(filename, 'w') as json_file:
        # Writing comments as dictionary at the beginning of the file
        json.dump({'_comments': comments, **data}, json_file, indent=4)

def generate_climate_stats(year_start, year_end, host, user, password, database, table, output_file):
    # Establishing a connection to the MySQL database
    connection = pymysql.connect(host=host, user=user, password=password, database=database)

    try:
        # Creating a cursor to execute SQL queries
        with connection.cursor() as cursor:
            # Executing the query to retrieve meteorological data
            query = f"SELECT * FROM {table}"
            cursor.execute(query)

            # Fetching all rows
            data = cursor.fetchall()

    finally:
        # Closing the database connection
        connection.close()

    # Converting fetched data to a list of dictionaries
    data = [
        {
            'Date': row[0],
            'TempAvg': float(row[1]) if row[1] is not None else None,
            'TempHigh': float(row[2]) if row[2] is not None else None,
            'TempLow': float(row[3]) if row[3] is not None else None,
            'DewPointAvg': float(row[4]) if row[4] is not None else None,
            'DewPointHigh': float(row[5]) if row[5] is not None else None,
            'DewPointLow': float(row[6]) if row[6] is not None else None,
            'HumidityAvg': int(row[7]) if row[7] is not None else None,
            'HumidityHigh': int(row[8]) if row[8] is not None else None,
            'HumidityLow': int(row[9]) if row[9] is not None else None,
            'PressureAvg': float(row[10]) if row[10] is not None else None,
            'PressureHigh': float(row[11]) if row[11] is not None else None,
            'PressureLow': float(row[12]) if row[12] is not None else None,
            'WindSpeedMax': float(row[13]) if row[13] is not None else None,
            'GustSpeedMax': float(row[14]) if row[14] is not None else None,
            'PrecipitationSum': float(row[15]) if row[15] is not None else None,
        }
        for row in data
    ]

    # Filtering data for the reference period
    data = [
        {
            'Date': row['Date'],
            'TempAvg': row['TempAvg'],
            'TempHigh': row['TempHigh'],
            'TempLow': row['TempLow'],
            'DewPointAvg': row['DewPointAvg'],
            'DewPointHigh': row['DewPointHigh'],
            'DewPointLow': row['DewPointLow'],
            'HumidityAvg': row['HumidityAvg'],
            'HumidityHigh': row['HumidityHigh'],
            'HumidityLow': row['HumidityLow'],
            'PressureAvg': row['PressureAvg'],
            'PressureHigh': row['PressureHigh'],
            'PressureLow': row['PressureLow'],
            'WindSpeedMax': row['WindSpeedMax'],
            'GustSpeedMax': row['GustSpeedMax'],
            'PrecipitationSum': row['PrecipitationSum'],
        }
        for row in data
        if row['Date'].year >= year_start and row['Date'].year <= year_end
    ]

    # Calculating climate statistics over the entire period
    climate_stats = {}

    # Average temperature of TempAvg, TempHigh, TempLow
    climate_stats['Avg_TempAvg'] = calculate_average(data, 'TempAvg', precision=1)
    climate_stats['Avg_TempHigh'] = calculate_average(data, 'TempHigh', precision=1)
    climate_stats['Avg_TempLow'] = calculate_average(data, 'TempLow', precision=1)

    # Calculating yearly average precipitation for the specified period
    climate_stats['Yearly_Avg_Precipitation'] = calculate_yearly_average_precipitation(data)


    # Maximal daily precipitation(s) with the dates
    climate_stats['Max_Daily_Precipitation'] = find_max_with_dates(data, 'PrecipitationSum')

    # Average daily precipitations of the period
    #climate_stats['Avg_Daily_Precipitation'] = climate_stats['Sum_Precipitation'] / len(data)
    climate_stats['Avg_Daily_Precipitation'] = calculate_average(data, 'PrecipitationSum', precision=1)

    # Maximal(s) temperature of TempHigh with the dates
    climate_stats['Max_TempHigh'] = find_max_with_dates(data, 'TempHigh')

    # Minimal(s) temperature of TempLow with the dates
    climate_stats['Min_TempLow'] = find_min_with_dates(data, 'TempLow')

    # Maximal(s) temperature of TempAvg with the dates
    climate_stats['Max_TempAvg'] = find_max_with_dates(data, 'TempAvg')

    # Minimal(s) temperature of TempAvg with the dates
    climate_stats['Min_TempAvg'] = find_min_with_dates(data, 'TempAvg')

    # Number of days with TempLow <= -5° by year
    climate_stats['Avg_Days_TempLow_-5'] = calculate_average_days_temp_low(data, (-99, -5))
    
    # Number of days with TempLow <= 0 by year
    climate_stats['Avg_Days_TempLow_0'] = calculate_average_days_temp_low(data, (-99, 0))

    # Number of days with TempLow > 0 and <= 5 by year
    climate_stats['Avg_Days_TempLow_0_5'] = calculate_average_days_temp_low(data, (0, 5))

    # Number of days with TempLow > 5 and <= 10 by year
    climate_stats['Avg_Days_TempLow_5_10'] = calculate_average_days_temp_low(data, (5, 10))

    # Number of days with TempLow > 10 and <= 15 by year
    climate_stats['Avg_Days_TempLow_10_15'] = calculate_average_days_temp_low(data, (10, 15))

    # Number of days with TempLow > 15 and <= 20 by year
    climate_stats['Avg_Days_TempLow_15_20'] = calculate_average_days_temp_low(data, (15, 20))

    # Number of days with TempLow >= 20 by year
    climate_stats['Avg_Days_TempLow_20'] = calculate_average_days_temp_low(data, (20, float('inf')))

    # Number of days with TempHigh <= 0 by year
    climate_stats['Avg_Days_TempHigh_0'] = calculate_average_days_temp_high(data, (-999, 0))

    # Number of days with TempHigh >= 30 by year
    climate_stats['Avg_Days_TempHigh_30'] = calculate_average_days_temp_high(data, (30, float('inf')))

    # Number of days with TempHigh > 0 and <= 5 by year
    climate_stats['Avg_Days_TempHigh_0_5'] = calculate_average_days_temp_high(data, (0, 5))

    # Number of days with TempHigh > 5 and <= 10 by year
    climate_stats['Avg_Days_TempHigh_5_10'] = calculate_average_days_temp_high(data, (5, 10))

    # Number of days with TempHigh > 10 and <= 15 by year
    climate_stats['Avg_Days_TempHigh_10_15'] = calculate_average_days_temp_high(data, (10, 15))

    # Number of days with TempHigh > 15 and <= 20 by year
    climate_stats['Avg_Days_TempHigh_15_20'] = calculate_average_days_temp_high(data, (15, 20))

    # Number of days with TempHigh >= 20 by year
    climate_stats['Avg_Days_TempHigh_20'] = calculate_average_days_temp_high(data, (20, float('inf')))

      # Number of days with TempAvg <= 0 by year
    climate_stats['Avg_Days_TempAvg_0'] = calculate_average_days_temp_avg(data, (-999, 0))

    # Number of days with TempAvg >= 25 by year
    climate_stats['Avg_Days_TempAvg_25'] = calculate_average_days_temp_avg(data, (25, float('inf')))

    # Number of days with TempAvg > 0 and <= 5 by year
    climate_stats['Avg_Days_TempAvg_0_5'] = calculate_average_days_temp_avg(data, (0, 5))

    # Number of days with TempAvg > 5 and <= 10 by year
    climate_stats['Avg_Days_TempAvg_5_10'] = calculate_average_days_temp_avg(data, (5, 10))

    # Number of days with TempAvg > 10 and <= 15 by year
    climate_stats['Avg_Days_TempAvg_10_15'] = calculate_average_days_temp_avg(data, (10, 15))

    # Number of days with TempAvg > 15 and <= 20 by year
    climate_stats['Avg_Days_TempAvg_15_20'] = calculate_average_days_temp_avg(data, (15, 20))

    # Number of days with TempAvg >= 20 by year
    climate_stats['Avg_Days_TempAvg_20'] = calculate_average_days_temp_avg(data, (20, float('inf')))

    # Number of days with PrecipitationSum >= 1 by year
    climate_stats['Avg_Days_Precipitation_1'] = calculate_average_days_precipitation(data, (1, float('inf')))

    # Number of days with PrecipitationSum > 0 by year
    climate_stats['Avg_Days_Precipitation_0'] = calculate_average_days_precipitation(data, (0.1, float('inf')))

    # Number of days with PrecipitationSum >= 1 and < 5 by year
    climate_stats['Avg_Days_Precipitation_1_5'] = calculate_average_days_precipitation(data, (1, 5))

    # Number of days with PrecipitationSum >= 5 and < 10 by year
    climate_stats['Avg_Days_Precipitation_5_10'] = calculate_average_days_precipitation(data, (5, 10))

    # Number of days with PrecipitationSum >= 10 by year
    climate_stats['Avg_Days_Precipitation_10'] = calculate_average_days_precipitation(data, (10, float('inf')))
    
    # Number of days with PrecipitationSum >= 20 by year
    climate_stats['Avg_Days_Precipitation_20'] = calculate_average_days_precipitation(data, (20, float('inf')))

    # Displaying the overall climate statistics
    print(climate_stats)

    # Constructing the default output file name
    if not output_file:
        output_file = f"StatsNormals_{year_start}_{year_end}.json"

    # Writing the climate statistics to a JSON file
    write_to_json(climate_stats, output_file)

if __name__ == "__main__":
    # Configuring Argparse to handle arguments
    parser = argparse.ArgumentParser(description="Script to generate overall climate statistics for a specified period.")
    parser.add_argument("year_start", type=int, help="Starting year of the reference period")
    parser.add_argument("year_end", type=int, help="Ending year of the reference period")
    parser.add_argument("--host", type=str, help="MySQL database host", required=True)
    parser.add_argument("--user", type=str, help="MySQL database user", required=True)
    parser.add_argument("--password", type=str, help="MySQL database password", required=True)
    parser.add_argument("--database", type=str, help="MySQL database name", required=True)
    parser.add_argument("--table", type=str, help="Table name", required=True)
    parser.add_argument("--output_file", type=str, help="Output JSON file name", default="")

    # Parsing arguments
    args = parser.parse_args()

    # Calling the function with the specified years and connection details
    generate_climate_stats(args.year_start, args.year_end, args.host, args.user, args.password, args.database, args.table, args.output_file)