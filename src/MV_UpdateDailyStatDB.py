#!/usr/bin/env python3

from __future__ import print_function
import json
import sys

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
 
    def __init__(self, stationID, mysqlHost, mysqlDBname,user,dbpassword):
        self.stationID = stationID
        self.mysqlHost = mysqlHost
        self.mysqlDBname = mysqlDBname
        self.user = user
        self.dbpassword = dbpassword

    def GetDayWCFromDB(self,user,dbpassword,Date):
        # ----------------------------------------------------------------
        # 
        # SELECT ROUND(AVG(WC_temp),1),
        #        MAX(WC_temp),
        #        MIN(WC_Temp)
        #  FROM `WeatherConditions` WHERE DATE(WC_Datetime) = '2021-10-15' 
        # Connect to the database
        connection = pymysql.connect(host=self.mysqlHost,
                            user=user,
                            password=dbpassword,
                            db=self.mysqlDBname,
                            charset='utf8mb4',
                            cursorclass=pymysql.cursors.DictCursor,
                            autocommit=True)
        try:
            with connection.cursor() as cursor:

                # Read a single record
                sql = """SELECT 
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
                FROM `WeatherConditions` WHERE DATE(WC_Datetime) = %s"""
                
                cursor.execute(sql, Date)
                #cursor.execute(sql)
                result = cursor.fetchone()
                print (result)
                        
        finally:
            connection.close()

        return result

    def InsertDayWeatherCondtions(self,user,dbpassword,date,wc):
        
        # Connect to the database
        connection = pymysql.connect(host=self.mysqlHost,
                            user=user,
                            password=dbpassword,
                            db=self.mysqlDBname,
                            charset='utf8mb4',
                            cursorclass=pymysql.cursors.DictCursor,
                            autocommit=True)
        try:
            with connection.cursor() as cursor:

                # Read a single record
                sql = "SELECT * FROM `DayWeatherConditions` WHERE `WC_Date`=%s"
                cursor.execute(sql, date)
                result = cursor.fetchone()

                if result is None:
                    # Insert a new record
                    sql = """INSERT INTO DayWeatherConditions (
                        WC_Date,
                        WC_TempAvg, 
                        WC_TempHigh, 
                        WC_TempLow,
                        WC_DewPointAvg,
                        WC_DewPointHigh,
                        WC_DewPointLow,
                        WC_HumidityAvg,
                        WC_HumidityHigh,
                        WC_HumidityLow,
                        WC_PressureAvg,
                        WC_PressureHigh,
                        WC_PressureLow,
                        WC_WindSpeedMax,
                        WC_GustSpeedMax,
                        WC_PrecipitationSum
                        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""
                    
                    cursor.execute(sql,
                        (
                            date,
                            wc['WC_TempAvg'],
                            wc['WC_TempHigh'],
                            wc['WC_TempLow'],
                            wc['WC_DewPointAvg'],
                            wc['WC_DewPointHigh'],
                            wc['WC_DewPointLow'],
                            wc['WC_HumidityAvg'],
                            wc['WC_HumidityHigh'],
                            wc['WC_HumidityLow'],
                            wc['WC_PressureAvg'],
                            wc['WC_PressureHigh'],
                            wc['WC_PressureLow'],
                            wc['WC_WindSpeedMax'],
                            wc['WC_GustSpeedMax'],
                            wc['WC_PrecipitationSum'])
                        )
                else:

                    # Update existing record
                    sql = """UPDATE DayWeatherConditions SET 
                            WC_Date = %s,
                            WC_TempAvg = %s, 
                            WC_TempHigh = %s, 
                            WC_TempLow = %s,
                            WC_DewPointAvg = %s,
                            WC_DewPointHigh = %s,
                            WC_DewPointLow = %s,
                            WC_HumidityAvg = %s,
                            WC_HumidityHigh = %s,
                            WC_HumidityLow = %s,
                            WC_PressureAvg = %s,
                            WC_PressureHigh = %s,
                            WC_PressureLow = %s,
                            WC_WindSpeedMax = %s,
                            WC_GustSpeedMax = %s,
                            WC_PrecipitationSum = %s
                            WHERE WC_Date = %s"""

                    cursor.execute(sql,
                        (date,
                        wc['WC_TempAvg'],
                        wc['WC_TempHigh'],
                        wc['WC_TempLow'],
                        wc['WC_DewPointAvg'],
                        wc['WC_DewPointHigh'],
                        wc['WC_DewPointLow'],
                        wc['WC_HumidityAvg'],
                        wc['WC_HumidityHigh'],
                        wc['WC_HumidityLow'],
                        wc['WC_PressureAvg'],
                        wc['WC_PressureHigh'],
                        wc['WC_PressureLow'],
                        wc['WC_WindSpeedMax'],
                        wc['WC_GustSpeedMax'],
                        wc['WC_PrecipitationSum'],
                        date))
            
                    # connection is not autocommit by default. So you must commit to save
                    # your changes.
                    connection.commit()
                    print(cursor.rowcount, "record(s) affected") 
        finally:
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
        print("-----------------------------------------")
 
        return

# ------------------------------------------------------------
# https://gist.github.com/monkut/e60eea811ef085a6540f
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


    parser = argparse.ArgumentParser(formatter_class=argparse.RawDescriptionHelpFormatter,
    description='''\
        Update MYSQL DB DayWeatherConditions table with Current conditions
        extracted from WeatherConditions table.
            --------------------------------------------------------
             ''',
    epilog='''
    --------------------------------------------------------------''')

    # Use here to check the date a type function
    parser.add_argument('-SD', '--startdate',
                        dest='startdate',
                        type=valid_date_type,
                        default=None,
                        required=True,
                        help='start date in format "YYYY-MM-DD"')
    parser.add_argument('-ED', '--enddate',
                        dest='enddate',
                        type=valid_date_type,
                        default=None,
                        required=True,
                        help='End date in format "YYYY-MM-DD"')

    parser.add_argument('-p', '--password',
                        dest='dbpassword',
                        default=None,
                        required=True,
                        help='Mysql admin user password')


    parser.add_argument('-d', '--display', action = "store_true",
                        help='Only Display Current Conditions')

    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument("-Y", "--yesterday", action="store_true", required=False, help="Process Yesterday's data")
    group.add_argument("-T", "--today", action="store_true", required=False, help="Process Today's data")


    parser.add_argument('--version', action='version', version='[%(prog)20s] 2.0')
   
    return parser.parse_args(argv)

if __name__ == "__main__":

    argvals = None             # init argv in case not testing

    args = getArgs(argvals)

    print ('display is ',args.display)
    print ('dbpassword is ',args.dbpassword)
    print ('Start Date is ',args.startdate)
    print ('End Date is ',args.enddate)

    # Get current date, then yesterday, put it at format YYYY-MM-DD
    Aujourdhui = date.today()
    Hier = Aujourdhui-timedelta(1)
    #dashyesterday = Hier.strftime("%Y-%m-%d")
    print("Yesterday's date:", Hier)
    
    start_date = args.startdate
    end_date = args.enddate
    dbpassword = args.dbpassword
    delta = timedelta(days=1)

    # if -Y process only Yesterday: Start and end date are equal to yesterday
    if args.yesterday:
        start_date = Hier
        end_date = Hier

    if args.today:
        start_date = Aujourdhui
        end_date = Aujourdhui
       

    # Create new WeatherConditions instance for ILEDEFRA131 weather station
    # ----------------------------------------------------------------------
    while start_date <= end_date:
  
        DateDash=start_date.strftime("%Y-%m-%d")

        print ("== Process with :",DateDash)

        wc=DayWeatherConditions('ILEDEFRA131', '192.168.17.10', '
        ','admin',args.dbpassword)
        wcIDFRA=wc.GetDayWCFromDB('admin',args.dbpassword,DateDash)
        if wcIDFRA is not None:
            if args.display:
                wc.DisplayWeatherConditions(wcIDFRA,DateDash)
            else:
                wc.InsertDayWeatherCondtions('admin',args.dbpassword,DateDash,wcIDFRA)
        
        print (wcIDFRA)
        start_date += delta

