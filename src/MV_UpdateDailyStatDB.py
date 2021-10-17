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
                MAX(WC_precipTotal) as WC_PrecipitationSum
                FROM `WeatherConditions` WHERE DATE(WC_Datetime) = %s"""
                #sql = "SELECT ROUND(AVG(WC_temp),1) as WC_TempAvg, MAX(WC_temp) as WC_TempHigh, MIN(WC_Temp) as WC_TempLow FROM `WeatherConditions` WHERE DATE(WC_Datetime) = '2021-10-15'" 
                print (sql)
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
        print("Température Moyenne : ",wc['WC_TempAvg'])
        print("Température Maxi    : ",wc['WC_TempHigh'])
        print("Température Mini    : ",wc['WC_TempLow'])
        print("Point de rosée Moyen: ",wc['WC_DewPointAvg'])
        print("Point de rosée Maxi : ",wc['WC_DewPointHigh'])
        print("Point de rosée Mini : ",wc['WC_DewPointLow'])
        print("Taux d'humidité Moy.: ",wc['WC_HumidityAvg'],"%",sep="")
        print("Taux d'humidité Maxi: ",wc['WC_HumidityHigh'])
        print("Taux d'humidité Mini: ",wc['WC_HumidityLow'])
        print("Pression Atmos. Moy : ",wc['WC_PressureAvg']," hpa",sep="")
        print("Pression Atmos. Maxi: ",wc['WC_PressureHigh']," hpa",sep="")
        print("Pression Atmos. Mini: ",wc['WC_PressureLow']," hpa",sep="")
        print("Vitesse du Vent Maxi: ",wc['WC_WindSpeedMax']," Km/h",sep="")
        print("Vitesse Rafale Maxi : ",wc['WC_GustSpeedMax']," Km/h",sep="")
        print("Précipiation jour   : ",wc['WC_PrecipitationSum']," mm",sep="")
        #print("Software Station   : ",wc['observations'][0].softwareType)
        #print("Pays               : ",wc['observations'][0].country)
        #print("Radiations Solaire : ",wc['observations'][0].solarRadiation)
        #print("Longitude Station  : ",wc['observations'][0].lon)
        #print("Latitude Station   : ",wc['observations'][0].lat)
        #print("Realtime Frequency : ",wc['observations'][0].realtimeFrequency)
        #print("Epoch              : ",wc['observations'][0].epoch)
        #print("Direction Vent     : ",wc['observations'][0].winddir)
        #print("Taux d'humidité    : ",wc['observations'][0].humidity,"%",sep="")
        #print("qcStatus           : ",wc['observations'][0].qcStatus)
        #print("Température        : ",wc['observations'][0].metric.temp,"°",sep="")
        #print("Indice de Chaleur  : ",wc['observations'][0].metric.heatIndex,"°",sep="")
        #print("Point de rosée     : ",wc['observations'][0].metric.dewpt,"°",sep="")
        #print("Refroidissement    : ",wc['observations'][0].metric.windChill,"°",sep="")
        #print("Vitesse du Vent    : ",wc['observations'][0].metric.windSpeed," Km/h",sep="")
        #print("Vitesse en Rafale  : ",wc['observations'][0].metric.windGust," Km/h",sep="")
        #print("Pression Atmos.    : ",wc['observations'][0].metric.pressure," hpa",sep="")
        #print("Taux de précipit.  : ",wc['observations'][0].metric.precipRate," mm/h",sep="")
        #print("Précipiation jour  : ",wc['observations'][0].metric.precipTotal," mm",sep="")
        #print("Altitude Station   : ",wc['observations'][0].metric.elev," m",sep="")
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
        msg = "Given Date ({0}) not valid! Expected format, YYYYMMDD !".format(arg_date_str)
        raise argparse.ArgumentTypeError(msg)

# ------------------------------------------------------------------------------
# Arguments management
# ------------------------------------------------------------------------------
def getArgs(argv=None):


    parser = argparse.ArgumentParser(formatter_class=argparse.RawDescriptionHelpFormatter,
    description='''\
        Update MYSQL DB with Current conditions
        of WS-1001-WiFi Weather Station exported to WeatherUnderground site.
            --------------------------------------------------------
             ''',
    epilog='''
    --------------------------------------------------------------''')

    # Use here to check the date a type function
    #parser.add_argument("-SD", "--startdate", required='-ed' in sys.argv, dest="startdate", nargs='?', type=valid_date, help="Start date YYYYMMDD for the Weather Data selection")
    parser.add_argument('-SD', '--startdate',
                        dest='startdate',
                        type=valid_date_type,
                        default=None,
                        required=True,
                        help='start date in format "YYYY-MM-DD"')
    #parser.add_argument("-ed", "--enddate", required='-sd' in sys.argv, dest="enddate", nargs='?', type=valid_date, help="End date YYYYMMDD for the Weather Data selection")
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

    
    #parser.add_argument('-D', '--day',
    #                   dest='date',
    #                    default=None,
    #                    required=False,
    #                    help='Day of Weather Conditions to Get')

    parser.add_argument('--version', action='version', version='[%(prog)20s] 2.0')
   
    return parser.parse_args(argv)

if __name__ == "__main__":

    argvals = None             # init argv in case not testing

    args = getArgs(argvals)

    print ('display is ',args.display)
    print ('dbpassword is ',args.dbpassword)
    print ('Start Date is ',args.startdate)
    print ('End Date is ',args.enddate)

    # Get current date , put it at format YYYYMMDD
    yesterday = date.today()-timedelta(1)
    DateYYYYMMDD = yesterday.strftime("%Y%m%d")
    print("Yesterday's date:", DateYYYYMMDD)
    DateDash = yesterday.strftime("%Y-%m-%d")

    start_date = args.startdate
    end_date = args.enddate
    dbpassword = args.dbpassword
    delta = timedelta(days=1)

    # Create new WeatherConditions instance for ILEDEFRA131 weather station
    # ----------------------------------------------------------------------
    while start_date <= end_date:

        WeatherDict = {}
    
        DateDash=start_date.strftime("%Y-%m-%d")
        DateYYYYMMDD=start_date.strftime("%Y%m%d")

        wc=DayWeatherConditions('ILEDEFRA131', '192.168.17.10', 'VillebonWeatherReport','admin',args.dbpassword)
        wcIDFRA=wc.GetDayWCFromDB('admin',args.dbpassword,DateDash)
        if args.display:
            wc.DisplayWeatherConditions(wcIDFRA,DateDash)
        else:
            wc.InsertDayWeatherCondtions('admin',args.dbpassword,DateDash,wcIDFRA)
        
        print (wcIDFRA)
        start_date += delta

