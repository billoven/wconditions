#!/usr/bin/env python3

from __future__ import print_function
import json
import sys
from astral import LocationInfo
from astral.sun import sun
from datetime import date
import pytz

import urllib.request, urllib.error
# https://pypi.org/project/easydict/ Access easily to Dictionnary values
from easydict import EasyDict as edict

import pymysql.cursors
# from datetime import date, timedelta, datetime
import argparse

# Time functions library
from datetime import date, timedelta, datetime

# Class WeatherConditions
class DayWeatherConditions:
    """This class is describing the various WeatherCondition parameters of a day
        ILDEFRA131 wheather station located in Villebon-sur-YVette 
        https://docs.google.com/document/d/1KGb8bTVYRsNgljnNH67AMhckY8AQT2FVwZ9urj8SWBs/edit#heading=h.n5xouxl8sojv  
        {"observations":
            [{  "stationID":"ILEDEFRA131",
                "obsTimeUtc":"2021-03-20T18:05:32Z",
                "obsTimeLocal":"2021-03-20 19:05:32",
                "neighborhood":"Villebon sur Yvette - Moulin de la Planche",
                "softwareType":"WS-1002 V2.4.6",
                "country":"FR",
                "solarRadiation":0.0,
                "lon":2.233016,
                "realtimeFrequency":null,
                "epoch":1616263532,
                "lat":48.700191,
                "uv":0.0,
                "winddir":186,
                "humidity":56.0,
                "qcStatus":1,
                "metric":{
                    "temp":7.9,
                    "heatIndex":7.9,
                    "dewpt":-0.3,
                    "windChill":7.9,
                    "windSpeed":0.0,
                    "windGust":0.0,
                    "pressure":1027.09,
                    "precipRate":0.00,
                    "precipTotal":0.00,
                    "elev":54.9
                }
            }]
        }
        """
    def __init__(self, config):
        # Assuming 'config' is a dictionary containing all necessary configuration parameters
        self.stationID = config['weatherStation']
        self.mysqlHost = config['host']
        self.mysqlDBname = config['database']
        self.user = config['username']
        self.dbpassword = config['password']
        self.longitude = config['longitude']
        self.latitude = config['latitude']
        self.timezone = config['timezone']
        self.tabledwc = config['tabledwc']
        self.tablewc = config['tablewc']

    def GetDayWCFromDB(self, DateDash):
        # Access the instance variables like self.user, self.dbpassword, etc.
        # Use self.mysqlHost, self.mysqlDBname, self.user, self.dbpassword to interact with DB
        connection = pymysql.connect(host=self.mysqlHost,
                                     user=self.user,
                                     password=self.dbpassword,
                                     db=self.mysqlDBname,
                                     charset='utf8mb4',
                                     cursorclass=pymysql.cursors.DictCursor,
                                     autocommit=True)
        try:
            with connection.cursor() as cursor:

                # Create a LocationInfo object with the city's name and country
                # Example: classastral.LocationInfo(name: str = 'Greenwich', region: str = 'England', timezone: str = 'Europe/London', latitude: float = 51.4733, longitude: float = -0.0008333)
                city = LocationInfo(name=self.stationID, region="France", timezone=self.timezone, latitude=self.latitude, longitude=self.longitude)

                # Get the current date (replace Date with the appropriate variable)
                current_date = datetime.strptime(DateDash, "%Y-%m-%d")

                # Calculate sunrise and sunset times for the given date
                s = sun(city.observer, date=current_date)

                # Extract sunrise and sunset times (these are in UTC)
                sunrise_utc = s['sunrise']
                sunset_utc = s['sunset']

                # Convert to timezone (if required)
                tz = pytz.timezone(self.timezone)
                sunrise_local = sunrise_utc.astimezone(tz)
                sunset_local = sunset_utc.astimezone(tz)

                # Convert local sunrise and sunset times to string format for SQL query
                sunrise_str = sunrise_local.strftime("%Y-%m-%d %H:%M:%S")
                sunset_str = sunset_local.strftime("%Y-%m-%d %H:%M:%S")

                # Calcul de la durée du jour
                day_duration = sunset_local - sunrise_local

                # Convertir la durée en heures, minutes et secondes
                hours, remainder = divmod(day_duration.seconds, 3600)
                minutes, seconds = divmod(remainder, 60)

                # Afficher les résultats
                print(f"Lever du soleil (local): {sunrise_str}")
                print(f"Coucher du soleil (local): {sunset_str}")
                print(f"Durée du jour : {hours} heures, {minutes} minutes et {seconds} secondes")

                # Query for all-day weather data
                sql_all_day = f"""SELECT 
                        ROUND(AVG(WC_temp),1) as WC_TempAvg, 
                        MAX(WC_temp) as WC_TempHigh, 
                        MIN(WC_temp) as WC_TempLow,
                        ROUND(AVG(WC_dewpt),1) as WC_DewPointAvg,
                        MAX(WC_dewpt) as WC_DewPointHigh,
                        MIN(WC_dewpt) as WC_DewPointLow,
                        ROUND(AVG(WC_humidity),0) as WC_HumidityAvg,
                        MAX(WC_humidity) as WC_HumidityHigh,
                        MIN(WC_humidity) as WC_HumidityLow,
                        ROUND(AVG(WC_pressure),1) as WC_PressureAvg,
                        ROUND(MAX(WC_pressure),1) as WC_PressureHigh,
                        ROUND(MIN(WC_pressure),1) as WC_PressureLow,
                        MAX(WC_windSpeed) as WC_WindSpeedMax,
                        MAX(WC_windGust) as WC_GustSpeedMax,
                        ROUND(MAX(WC_precipTotal),1) as WC_PrecipitationSum
                        FROM `{self.tablewc}`
                        WHERE DATE(WC_Datetime) = '{DateDash}'"""

                cursor.execute(sql_all_day)
                result_all_day = cursor.fetchone()

                # Query for solar radiation data
                sql_solar_radiation = f"""SELECT 
                        ROUND(AVG(WC_SolarRadiation),1) as WC_SolarRadiationAvg
                        FROM `{self.tablewc}`
                        WHERE DATE(WC_Datetime) = %s
                        AND WC_Datetime BETWEEN %s AND %s"""

                cursor.execute(sql_solar_radiation, (DateDash, sunrise_str, sunset_str))
                result_solar = cursor.fetchone()

                # Merge results
                if result_solar and 'WC_SolarRadiationAvg' in result_solar:
                    result_all_day['WC_SolarRadiationAvg'] = result_solar['WC_SolarRadiationAvg']
                else:
                    result_all_day['WC_SolarRadiationAvg'] = None

        finally:
            connection.close()

        return result_all_day
        
    # Method to fetch the columns of the table dynamically based on self.tabledwc
    def GetTableColumns(self, connection):
        with connection.cursor() as cursor:
            # Use the table name from the instance variable self.tabledwc
            sql = f"DESCRIBE `{self.tabledwc}`"
            cursor.execute(sql)
            # Extract the column names from the DESCRIBE query result
            columns = [row['Field'] for row in cursor.fetchall()]
        return columns

    # Method to insert or update weather conditions
    def InsertDayWeatherConditions(self, wc, date, fields=None, noexecute=False):
        # Connect to the MySQL database
        connection = pymysql.connect(host=self.mysqlHost,
                                     user=self.user,
                                     password=self.dbpassword,
                                     db=self.mysqlDBname,
                                     charset='utf8mb4',
                                     cursorclass=pymysql.cursors.DictCursor,
                                     autocommit=True)
        try:
            # Retrieve all valid column names from the table
            valid_columns = self.GetTableColumns(connection)

            # Validate the provided fields against the valid column names
            if fields:
                invalid_fields = [field for field in fields if f"{field}" not in valid_columns]
                if invalid_fields:
                    raise ValueError(f"Invalid fields: {', '.join(invalid_fields)}")

            with connection.cursor() as cursor:
                # Check if a record already exists for the given date
                sql = f"SELECT * FROM `{self.tabledwc}` WHERE `WC_Date`=%s"
                cursor.execute(sql, (date,))
                result = cursor.fetchone()
              
                if result is None:
                    # Ensure that WC_Date is included in the columns and values for the INSERT statement
                    columns = ["WC_Date"] + [f"{key}" for key in wc.keys()]
                    values = [date] + [wc[key] for key in wc.keys()]

                    # Construct the SQL query for inserting a new record
                    sql = f"""INSERT INTO `{self.tabledwc}` ({', '.join(columns)}) 
                            VALUES ({', '.join(['%s'] * len(values))})"""
                    
                    if noexecute:
                        # Debug mode: Print the SQL query and values instead of executing it
                        print("[DEBUG] SQL for INSERT:", sql)
                        print("[DEBUG] Values:", tuple(values))
                    else:
                        # Execute the SQL query with the provided values
                        cursor.execute(sql, tuple(values))
                else:
                    # Generate the SQL for UPDATE
                    if fields:
                        columns = [f"{field}" for field in fields]
                        values = [wc[field] for field in fields]
                    else:
                        # If no fields are specified, update all fields in `wc`
                        columns = [f"{key}" for key in wc.keys()]
                        values = [wc[key] for key in wc.keys()]
                    set_clause = ", ".join([f"{col} = %s" for col in columns])
                    sql = f"""UPDATE `{self.tabledwc}` SET {set_clause} WHERE WC_Date = %s"""
                    if noexecute:
                        print("[DEBUG] SQL for UPDATE:", sql)
                        print("[DEBUG] Values:", tuple(values + [date]))
                    else:
                        cursor.execute(sql, tuple(values + [date]))

                # Commit the changes if not in noexecute mode
                if not noexecute:
                    connection.commit()
                    print(cursor.rowcount, "record(s) affected")
        finally:
            # Close the database connection
            connection.close()

        return

    def DisplayWeatherConditions(self,wc,date):    
        print("-----------------------------------------")
        print (wc)
        print("StationID           : ",self.stationID)
        print("Date                :",date)
        print("Température Moyenne : ",wc['WC_TempAvg'],"°C",sep="")
        print("Température Maxi    : ",wc['WC_TempHigh'],"°C",sep="")
        print("Température Mini    : ",wc['WC_TempLow'],"°C",sep="")
        print("Point de rosée Moyen: ",wc['WC_DewPointAvg'],"°C",sep="")
        print("Point de rosée Maxi : ",wc['WC_DewPointHigh'],"°C",sep="")
        print("Point de rosée Mini : ",wc['WC_DewPointLow'],"°C",sep="")
        print("Taux d'humidité Moy.: ",wc['WC_HumidityAvg'],"%",sep="")
        print("Taux d'humidité Maxi: ",wc['WC_HumidityHigh'],"%",sep="")
        print("Taux d'humidité Mini: ",wc['WC_HumidityLow'],"%",sep="")
        print("Pression Atmos. Moy : ",wc['WC_PressureAvg']," hpa",sep="")
        print("Pression Atmos. Maxi: ",wc['WC_PressureHigh']," hpa",sep="")
        print("Pression Atmos. Mini: ",wc['WC_PressureLow']," hpa",sep="")
        print("Vitesse du Vent Maxi: ",wc['WC_WindSpeedMax']," Km/h",sep="")
        print("Vitesse Rafale Maxi : ",wc['WC_GustSpeedMax']," Km/h",sep="")
        print("Précipiation jour   : ",wc['WC_PrecipitationSum']," mm",sep="")
        print("Radiation Sol. Moy  : ",wc['WC_SolarRadiationAvg']," w/m2",sep="")
        print("-----------------------------------------")
 
        return

# ------------------------------------------------------------
# Check if the format of the date given in Arguments is valid
# ------------------------------------------------------------
def valid_date_type(arg_date_str):
    """custom argparse *date* type for user dates values given from the command line"""
    try:
        return datetime.strptime(arg_date_str, "%Y-%m-%d")
    except ValueError:
        msg = "Given Date ({0}) not valid! Expected format, YYYY-MM-DD !".format(arg_date_str)
        raise argparse.ArgumentTypeError(msg)

# ------------------------------------------------------------------------------
# Arguments management
# ------------------------------------------------------------------------------
def getArgs(argv=None):
    parser = argparse.ArgumentParser(
        formatter_class=argparse.RawDescriptionHelpFormatter,
        description='''\
            Update MYSQL DB DayWeatherConditions table with Current conditions
            extracted from WeatherConditions table.
            --------------------------------------------------------
        ''',
        epilog='''--------------------------------------------------------------'''
    )

    # Gestion des dates
    parser.add_argument('-SD', '--startdate',
                        dest='startdate',
                        type=valid_date_type,
                        default=None,
                        required=True,
                        help='Start date in format "YYYY-MM-DD"')
    parser.add_argument('-ED', '--enddate',
                        dest='enddate',
                        type=valid_date_type,
                        default=None,
                        required=True,
                        help='End date in format "YYYY-MM-DD"')

    # Chemin vers le fichier JSON de configuration
    parser.add_argument('-c', '--config',
                        dest='config_path',
                        required=True,
                        help='Path to the JSON configuration file')

    # Station sélectionnée (Bethune ou Villebon)
    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument("-B", "--Bethune", action="store_true", help="Bethune WeatherUnderground station")
    group.add_argument("-V", "--Villebon", action="store_true", help="Villebon WeatherUnderground station")

    # Argument for the fields to be updated (optional), separated by commas
    parser.add_argument("-f", "--fields", type=str, help="Restrictive list of database fields to be updated, separated by commas, ex: WC_TempAvg,WC_TempHigh,WC_PressureAvg. Default all fields.")    
    
    # Optional argument to enable debug mode (no execution)
    parser.add_argument('--noexecute', action='store_true', help="If set, no SQL command will be executed, only printed for debugging.")

    # Options supplémentaires
    parser.add_argument('-d', '--display', action="store_true",
                        help='Only display current conditions')

    parser.add_argument('--version', action='version', version='[%(prog)20s] 2.0')

    return parser.parse_args(argv)

# ------------------------------------------------------------------------------
# Chargement des configurations JSON
# ------------------------------------------------------------------------------
def load_db_config(config_path, station_key):
    with open(config_path, 'r') as file:
        config = json.load(file)
        if station_key in config['dbConfigs']:
            return config['dbConfigs'][station_key]
        else:
            raise ValueError(f"Station key '{station_key}' not found in configuration file.")

# ------------------------------------------------------------------------------
# Main script
# ------------------------------------------------------------------------------
if __name__ == "__main__":
    args = getArgs()

    # Déterminer la station sélectionnée
    station_key = 'db1' if args.Villebon else 'db2'

    # Charger la configuration depuis le fichier JSON
    db_config = load_db_config(args.config_path, station_key)

    print(f"Using configuration for station: {db_config['weatherStation']}")

    # If fields to update are specified in --fields option, convert to a list
    fields_to_update = args.fields.split(',') if args.fields else None

    # Déterminer les dates
    today = date.today()
    yesterday = today - timedelta(days=1)

    start_date = args.startdate
    end_date = args.enddate
    delta = timedelta(days=1)

    # Determine the current date and time
    today = datetime.combine(date.today(), datetime.min.time())  # Convert to datetime at midnight

    # Validate that the date range does not exceed the current day
    if start_date > today or end_date > today:
        raise ValueError(f"Date range cannot exceed the current date ({today}). "
                        f"Provided: start_date={start_date}, end_date={end_date}")

    # Validate that start_date is not after end_date
    if start_date > end_date:
        raise ValueError(f"Start date ({start_date}) cannot be after end date ({end_date}).")

    
    print(f"Processing weather data from {start_date} to {end_date}...")
   
    # Traitement des données
    while start_date <= end_date:
        DateDash = start_date.strftime("%Y-%m-%d")

        print(f"== Processing data for: {DateDash} ==")

        # Création de l'instance DayWeatherConditions avec les configurations JSON
        wc = DayWeatherConditions(db_config)

        wcID = wc.GetDayWCFromDB(DateDash)

        if wcID is not None:
            if args.display:
                wc.DisplayWeatherConditions(wcID, DateDash)
            else:
                wc.InsertDayWeatherConditions(wcID, DateDash, fields=fields_to_update, noexecute=args.noexecute)

        print(wcID)
        start_date += delta