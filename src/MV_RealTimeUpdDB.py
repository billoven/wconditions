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

# Class WeatherConditions
class WeatherConditions:
    """This class is describing the WeatherConditions in WeatherUnderground
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

    def GetWeatherConditions(self):
        # ----------------------------------------------------------------
        # Build URL to access to current daily observations of ILEDEFRA131
        mystr_dict = {}
        mystr = ""

        # Wunderground API Key Expires: Sat, 14 Aug 2021 17:48:33 GMT.
        # https://www.wunderground.com/member/api-keys
        # key = '93f1717ea6714de3b1717ea671ade338'
        # RealTimeURL : https://api.weather.com/v2/pws/observations/current?stationId=ILEDEFRA131
        # &format=json&units=m&numericPrecision=decimal&apiKey=93f1717ea6714de3b1717ea671ade338
        key = '93f1717ea6714de3b1717ea671ade338'
        BASE_URL = 'https://api.weather.com/v2/pws/observations/current'
        FEATURE_URL = BASE_URL + f"?stationId={self.stationID}&format=json&units=m&numericPrecision=decimal&apiKey={key}"

        print (FEATURE_URL) 
        # Execute the HTTPS request to get JSON Result
        try:
            fp = urllib.request.urlopen(FEATURE_URL)
        except urllib.error.HTTPError as e:
            # Return code error (e.g. 404, 501, ...)
            # ...
            print('HTTPError: {}'.format(e.code))
        except urllib.error.URLError as e:
            # Not an HTTP-specific error (e.g. connection refused)
            # ...
            print('URLError: {}'.format(e.reason))
        else:
            # 200
            # ...
            print('good')
            mybytes = fp.read()
            # -*- decoding: utf-8 -*-
            mystr = mybytes.decode("utf8")
            print ("[",mystr,"]",sep="")
            fp.close()
            if mystr:
                # used edict ==> Very useful when exploiting parsed JSON content !
                mystr_dict = edict(json.loads(mystr))
            else:
                mystr_dict = {}
                print ("FALSE")
        return(mystr_dict)
   
    def ChckWeatherConditions(self):
        return 

    def InsertDBWeatherCondtions(self,user,dbpassword,wc):
        
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
                sql = "SELECT * FROM `WeatherConditions` WHERE `WC_Datetime`=%s"
                cursor.execute(sql, (wc['observations'][0].obsTimeLocal))
                result = cursor.fetchone()

                if result is None:
                    # Insert a new record
                    sql = """INSERT INTO WeatherConditions (
                        WC_Datetime,
                        WC_temp, 
                        WC_humidity,
                        WC_precipRate,
                        WC_precipTotal,
                        WC_pressure,
                        WC_heatIndex,
                        WC_windSpeed,
                        WC_windGust,
                        WC_windChill,
                        WC_winddir,
                        WC_dewpt,
                        WC_elev,
                        WC_solarRadiation,
                        WC_uv) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""
                    
                    cursor.execute(sql,
                        (
                            wc['observations'][0].obsTimeLocal,
                            wc['observations'][0].metric.temp,
                            wc['observations'][0].humidity,
                            wc['observations'][0].metric.precipRate,
                            wc['observations'][0].metric.precipTotal,
                            wc['observations'][0].metric.pressure,
                            wc['observations'][0].metric.heatIndex,
                            wc['observations'][0].metric.windSpeed,
                            wc['observations'][0].metric.windGust,
                            wc['observations'][0].metric.windChill,
                            wc['observations'][0].winddir,
                            wc['observations'][0].metric.dewpt,
                            wc['observations'][0].metric.elev,
                            wc['observations'][0].solarRadiation,
                            wc['observations'][0].uv))
            
                    # connection is not autocommit by default. So you must commit to save
                    # your changes.
                    connection.commit()
                    print(cursor.rowcount, "record(s) affected") 


        finally:

            connection.close()

        return

    def DisplayWeatherConditions(self,wc):    
        print("-----------------------------------------")
        print("Date/Heure          :",wc['observations'][0].obsTimeLocal)
        print("ID Station météo   : ",wc['observations'][0].stationID)
        print("Date/Heure UTC     : ",wc['observations'][0].obsTimeUtc)
        print("Localisation       : ",wc['observations'][0].neighborhood)
        print("Software Station   : ",wc['observations'][0].softwareType)
        print("Pays               : ",wc['observations'][0].country)
        print("Radiations Solaire : ",wc['observations'][0].solarRadiation)
        print("Longitude Station  : ",wc['observations'][0].lon)
        print("Latitude Station   : ",wc['observations'][0].lat)
        print("Realtime Frequency : ",wc['observations'][0].realtimeFrequency)
        print("Epoch              : ",wc['observations'][0].epoch)
        print("Direction Vent     : ",wc['observations'][0].winddir)
        print("Taux d'humidité    : ",wc['observations'][0].humidity,"%",sep="")
        print("qcStatus           : ",wc['observations'][0].qcStatus)
        print("Température        : ",wc['observations'][0].metric.temp,"°",sep="")
        print("Indice de Chaleur  : ",wc['observations'][0].metric.heatIndex,"°",sep="")
        print("Point de rosée     : ",wc['observations'][0].metric.dewpt,"°",sep="")
        print("Refroidissement    : ",wc['observations'][0].metric.windChill,"°",sep="")
        print("Vitesse du Vent    : ",wc['observations'][0].metric.windSpeed," Km/h",sep="")
        print("Vitesse en Rafale  : ",wc['observations'][0].metric.windGust," Km/h",sep="")
        print("Pression Atmos.    : ",wc['observations'][0].metric.pressure," hpa",sep="")
        print("Taux de précipit.  : ",wc['observations'][0].metric.precipRate," mm/h",sep="")
        print("Précipiation jour  : ",wc['observations'][0].metric.precipTotal," mm",sep="")
        print("Altitude Station   : ",wc['observations'][0].metric.elev," m",sep="")
        print("-----------------------------------------")
        return

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

    parser.add_argument('-d', '--display', action = "store_true",
                        help='Only Display Current Conditions')
    parser.add_argument('-p', '--password',
                        dest='dbpassword',
                        default=None,
                        required=True,
                        help='Mysql admin user password')
    


    parser.add_argument('--version', action='version', version='[%(prog)20s] 2.0')
   
    return parser.parse_args(argv)

if __name__ == "__main__":

    argvals = None             # init argv in case not testing

    args = getArgs(argvals)

    print ('display is ',args.display)
    print ('dbpassword is ',args.dbpassword)

    # Create new WeatherConditions instance for ILEDEFRA131 weather station
    # ----------------------------------------------------------------------
    wc=WeatherConditions('ILEDEFRA131', '192.168.17.10', 'VillebonWeatherReport','admin',args.dbpassword)
    wcIDFRA=wc.GetWeatherConditions()
    if args.display:
        wc.DisplayWeatherConditions(wcIDFRA)
    else:
        wc.InsertDBWeatherCondtions('admin',args.dbpassword,wcIDFRA)

    print (wcIDFRA)

