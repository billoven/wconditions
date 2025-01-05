#! /usr/bin/python3
import pymysql.cursors
from datetime import datetime
import argparse

def fetch_weather_data(date=None, period=None):
    try:
        connection = pymysql.connect(host='192.168.17.10',
                                     user='admin',
                                     password='Z0uZ0u0!',
                                     database='VillebonWeatherReport',
                                     cursorclass=pymysql.cursors.DictCursor)

        with connection.cursor() as cursor:
            # Fetch weather data for the specified date
            cursor.execute("SELECT * FROM DayWeatherConditions WHERE WC_Date = %s", (date,))
            weather_data = cursor.fetchone()

            # Fetch comparison data from ParisMontsourisDayWeatherConditions for the same day of the year within the specified period
            if period:
                start_year, end_year = map(int, period.split('_'))
                month_day = date[5:]  # Extract MM-DD from YYYY-MM-DD
                cursor.execute("SELECT MAX(WC_TempHigh), MIN(WC_TempLow), AVG(WC_TempAvg), AVG(WC_TempHigh), "
                               "AVG(WC_TempLow), MIN(WC_TempAvg), MAX(WC_TempAvg), MAX(WC_PrecipitationSum) "
                               "FROM ParisMontsourisDayWeatherConditions "
                               "WHERE MONTH(WC_Date) = MONTH(%s) AND DAY(WC_Date) = DAY(%s) "
                               "AND YEAR(WC_Date) BETWEEN %s AND %s",
                               (date, date, start_year, end_year))
            else:
                cursor.execute("SELECT MAX(WC_TempHigh), MIN(WC_TempLow), AVG(WC_TempAvg), AVG(WC_TempHigh), "
                               "AVG(WC_TempLow), MIN(WC_TempAvg), MAX(WC_TempAvg), MAX(WC_PrecipitationSum) "
                               "FROM ParisMontsourisDayWeatherConditions "
                               "WHERE MONTH(WC_Date) = MONTH(%s) AND DAY(WC_Date) = DAY(%s)",
                               (date, date))

            comparison_data = cursor.fetchone()

            return weather_data, comparison_data

    except pymysql.Error as e:
        print("Error while connecting to MySQL", e)
    finally:
        if connection:
            connection.close()

def main():
    parser = argparse.ArgumentParser(description="Fetch weather data from a MySQL database")
    parser.add_argument("-date", help="Date in YYYY-MM-DD format", default=datetime.now().strftime('%Y-%m-%d'))
    parser.add_argument("-p", help="Period in YYYY_YYYY format", default=None)
    args = parser.parse_args()

    weather_data, comparison_data = fetch_weather_data(args.date, args.p)

    if weather_data:
        print("Weather Data for", args.date)
        print("Minimal Temperature:", f"{weather_data['WC_TempLow']:.1f} °C")
        print("Maximal Temperature:", f"{weather_data['WC_TempHigh']:.1f} °C")
        print("Average Temperature:", f"{weather_data['WC_TempAvg']:.1f} °C")
        print("Sum of Precipitation:", f"{weather_data['WC_PrecipitationSum']:.1f} mm")

    if comparison_data:
        start_year, end_year = map(int, args.p.split('_')) if args.p else (None, None)
        comparison_period = f"{start_year}-{end_year}" if start_year and end_year else "Same Day"
        
        print(f"\nComparison Data for the Same Day of Year within the Period {comparison_period}")
        print("Maximal of Maximal Temperature:", f"{comparison_data['MAX(WC_TempHigh)']:.1f} °C")
        print("Minimal of Minimal Temperature:", f"{comparison_data['MIN(WC_TempLow)']:.1f} °C")
        print("Average of Average Temperatures:", f"{comparison_data['AVG(WC_TempAvg)']:.1f} °C")
        print("Average of Maximal Temperatures:", f"{comparison_data['AVG(WC_TempHigh)']:.1f} °C")
        print("Average of Minimal Temperatures:", f"{comparison_data['AVG(WC_TempLow)']:.1f} °C")
        print("Minimal of Average Temperatures:", f"{comparison_data['MIN(WC_TempAvg)']:.1f} °C")
        print("Maximal of Average Temperatures:", f"{comparison_data['MAX(WC_TempAvg)']:.1f} °C")
        print("Maximal of Daily Precipitation Sum:", f"{comparison_data['MAX(WC_PrecipitationSum)']:.1f} mm")

if __name__ == "__main__":
    main()
