#!/usr/bin/env python3

from __future__ import print_function
import json
import sys

# https://pypi.org/project/easydict/ Access easily to Dictionnary values
from easydict import EasyDict as edict

import urllib.request, urllib.error
import pymysql.cursors
from datetime import date, timedelta, datetime
import argparse

# ------------------------------------------------------------------------------
# Arguments management
# ------------------------------------------------------------------------------
def getArgs(argv=None):


    parser = argparse.ArgumentParser(formatter_class=argparse.RawDescriptionHelpFormatter,
    description='''\
        Update MYSQL DB with Daily weather data
        of WS-1001-WiFi Weather Station exported to WeatherUnderground site.
            --------------------------------------------------------
             ''',
    epilog='''
    --------------------------------------------------------------''')

    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument("-i", "--insert", action="store_true", help="INSERT Selected data in DB (New)")
    group.add_argument("-u", "--update", action="store_true", help="UPDATE Selected data in DB")
    group.add_argument("-d", "--display", action="store_true", help="Only Display Selected data")

    # Use here to check the date a type function
    #parser.add_argument("-sd", "--startdate", required='-ed' in sys.argv, dest="startdate", nargs='?', type=valid_date, help="Start date YYYYMMDD for the Weather Data selection")
    parser.add_argument('-sd', '--startdate',
                        dest='startdate',
                        type=valid_date_type,
                        default=None,
                        required=True,
                        help='start date in format "YYYYMMDD"')
    #parser.add_argument("-ed", "--enddate", required='-sd' in sys.argv, dest="enddate", nargs='?', type=valid_date, help="End date YYYYMMDD for the Weather Data selection")
    parser.add_argument('-ed', '--enddate',
                        dest='enddate',
                        type=valid_date_type,
                        default=None,
                        required=True,
                        help='End date in format "YYYYMMDD"')

    parser.add_argument('-p', '--password',
                        dest='dbpassword',
                        default=None,
                        required=True,
                        help='Mysql admin user password')


    parser.add_argument('--version', action='version', version='[%(prog)20s] 2.0')

    return parser.parse_args(argv)

def UpdateDB(DateDash,WeatherRecord,dbpassword):
    # Example of Wunderground URL : 'https://www.wunderground.com/weatherstation/WXDailyHistory.asp?ID=ILEDEFRA131&day=2&month=12&year=2017&dayend=1&monthend=1&yearend=2018&graphspan=custom&format=1
    # Compose the Wunderground URL https://api.weather.com/v2/pws/observations/current?stationId=ILEDEFRA131&format=json&numericPrecision=decimal&units=m&apiKey=43de0fca7f6f49a79e0fca7f6f29a708
    # API WU : https://docs.google.com/document/d/1eKCnKXI9xnoMGRRzOL1xPCBihNV2rOet08qpE_gArAY/edit
    # https://api.weather.com/v2/pws/history/daily?stationId=ILEDEFRA131&format=json&units=m&date=20191101&apiKey=43de0fca7f6f49a79e0fca7f6f29a708&numericPrecision=decimal
    # Once a day a query to get the daily forecast summary via a cron
    # Result are put in a JSON stucture
    # Then the MYSQL Data is updated
    # Connect to mysql
    # import MySQLdb
    # Date 	                date 		 Oui 	NULL
    # TemperatureHighC 	    decimal(3,1) Oui 	NULL
    # TemperatureAvgC   	decimal(3,1) Oui 	NULL
    # TemperatureLowC 	    decimal(3,1) Oui 	NULL
    # DewpointHighC 	    decimal(4,1) Oui 	NULL
    # DewpointAvgC 	        decimal(4,1) Oui 	NULL
    # DewpointLowC 	        decimal(4,1) Oui 	NULL
    # HumidityHigh 	        int(3) 		 Oui 	NULL
    # HumidityAvg 	        int(2) 		 Oui 	NULL
    # HumidityLow 	        int(2) 		 Oui 	NULL
    # PressureMaxhPa 	    int(4) 		 Oui 	NULL
    # PressureMinhPa 	    int(4) 		 Oui 	NULL
    # WindSpeedMaxKMH 	    int(3) 		 Oui 	NULL
    # WindSpeedAvgKMH 	    int(2) 		 Oui 	NULL
    # GustSpeedMaxKMH 	    int(3) 		 Oui 	NULL
    # PrecipitationSumCM 	decimal(3,2) Oui 	NULL

    # Connect to the database
    connection = pymysql.connect(host='192.168.17.10',
                                user='admin',
                                password=dbpassword,
                                db='meteovillebon',
                                charset='utf8mb4',
                                cursorclass=pymysql.cursors.DictCursor,
                                autocommit=True)

    try:

        #with connection.cursor() as cursor:

            # Create a new record
            #sql = "INSERT INTO `RelevesMeteo` ( `Date`,`TemperatureHighC`,`TemperatureAvgC`,`TemperatureLowC`,`DewpointHighC`,`DewpointAvgC`,`DewpointLowC`,`HumidityHigh`,`HumidityAvg`,`HumidityLow`,`PressureMaxhPa`,`PressureMinhPa`,`WindSpeedMaxKMH`,`WindSpeedAvgKMH`,`GustSpeedMaxKMH`,`PrecipitationSumCM`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"

            #cursor.execute(sql,(DateDash,WeatherRecord['observations'][0].metric.tempHigh,WeatherRecord['observations'][0].metric.tempAvg,WeatherRecord['observations'][0].metric.tempLow,WeatherRecord['observations'][0].metric.dewptHigh,WeatherRecord['observations'][0].metric.dewptAvg,WeatherRecord['observations'][0].metric.dewptLow,WeatherRecord['observations'][0].humidityHigh,WeatherRecord['observations'][0].humidityAvg,WeatherRecord['observations'][0].humidityLow,WeatherRecord['observations'][0].metric.pressureMax,WeatherRecord['observations'][0].metric.pressureMin,WeatherRecord['observations'][0].metric.windspeedHigh,WeatherRecord['observations'][0].metric.windspeedAvg,WeatherRecord['observations'][0].metric.windgustHigh,WeatherRecord['observations'][0].metric.precipTotal))

            # connection is not autocommit by default. So you must commit to save
            # your changes.
            #connection.commit()

        with connection.cursor() as cursor:

            # Read a single record
            sql = "SELECT * FROM `RelevesMeteo` WHERE `Date`=%s"
            cursor.execute(sql, (DateDash))
            result = cursor.fetchone()
            print(result)

    #except Exception as e:
    #    print("Exeception occured:{}".format(e))

    finally:

        connection.close()

# ------------------------------------------------------------
# https://gist.github.com/monkut/e60eea811ef085a6540f
# Check if the format of the date given in Arguments is valid
# ------------------------------------------------------------
def valid_date_type(arg_date_str):
    """custom argparse *date* type for user dates values given from the command line"""
    try:
        return datetime.strptime(arg_date_str, "%Y%m%d")
    except ValueError:
        msg = "Given Date ({0}) not valid! Expected format, YYYYMMDD !".format(arg_date_str)
        raise argparse.ArgumentTypeError(msg)


# Transform <date> <time> or <time> <date> in format DD/MM/YYYY
# ------------------------------------------------------------------------------
# initializing the titles and rows list
# ------------------------------------------------------------------------------
def DateYYYYMMDD(Date):

    if Date == '' :
        Date = Date.today()-datetime.timedelta(1)

    NewDate=Date.strftime("%Y/%m/%d")

    print("Yesterday's date:", NewDate)


    return(NewDate)

# ----------------------------------------------------------------
# Build URL to access to current daily observations of ILEDEFRA131
def GetWeatherData(DateYYYYMMDD):

    mystr_dict = {}
    mystr = ""

    key = '43de0fca7f6f49a79e0fca7f6f29a708'
    BASE_URL = 'https://api.weather.com/v2/pws/history/daily?stationId=ILEDEFRA131&format=json&units=m'
    FEATURE_URL = BASE_URL + f"&date={DateYYYYMMDD}&apiKey={key}&numericPrecision=decimal"

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
            print ("TRUE")
            mystr_dict = edict(json.loads(mystr))
        else:
            mystr_dict = {}
            print ("FALSE")

    return(mystr_dict)

# ------------------------------------------------------------------------------
# initializing the titles and rows list
# ------------------------------------------------------------------------------
fields = []
rows = []
startdate = ""
enddate = ""

if __name__ == "__main__":

    argvals = None             # init argv in case not testing

    # example of passing test params to parser
    # argvals = '6 2 -v'.split()

    args = getArgs(argvals)

    #answer = args.x**args.y

    # if args.quiet:
    #    print (answer)
    # elif args.verbose:
    #    print ("{} to the power {} equals {}".format(args.x, args.y, answer))
    # else:
    #    print ("{}^{} == {}".format(args.x, args.y, answer))


startdate = args.startdate
enddate = args.enddate
today = datetime.today()
dbpassword=args.dbpassword

print ('today is ', today)

if startdate > enddate:
    print ("The end date [", enddate, "] must be greater or equal to the start date [", startdate, "] !", sep="")
    sys.exit(1)
if enddate >= today:
    print ("The end date [", enddate, "] must not be greater than yesterday !")
    sys.exit(1)

print ('Args=',args)
print ('startdate is ', startdate)
print ('enddate is ', enddate)
print ('update is ',args.update)
print ('insert is ',args.update)
print ('display is ',args.display)
print ('display is ',dbpassword)



sys.exit(0)


# Get current date , put it at format YYYYMMDD
yesterday = date.today()-timedelta(1)
DateYYYYMMDD = yesterday.strftime("%Y%m%d")
print("Yesterday's date:", DateYYYYMMDD)
DateDash = yesterday.strftime("%Y-%m-%d")

start_date = date(2015, 7, 1)
end_date = date(2019, 12, 19)
delta = timedelta(days=1)

while start_date <= end_date:

    WeatherDict = {}

    DateDash=start_date.strftime("%Y-%m-%d")
    DateYYYYMMDD=start_date.strftime("%Y%m%d")

    Weather_Dict=GetWeatherData(DateYYYYMMDD)
    if Weather_Dict:
        print("-----------------------------")
        print("Date               :",DateDash)
        print("Température Moyenne: ",Weather_Dict['observations'][0].metric.tempAvg,"°",sep="")
        print("Température Maxi   : ",Weather_Dict['observations'][0].metric.tempHigh,"°",sep="")
        print("Température Mini   : ",Weather_Dict['observations'][0].metric.tempLow,"°",sep="")
        print("DewPoint High      : ",Weather_Dict['observations'][0].metric.dewptHigh,"°",sep="")
        print("Dewpoint Moy       : ",Weather_Dict['observations'][0].metric.dewptLow,"°",sep="")
        print("Dewpoint Mini      : ",Weather_Dict['observations'][0].metric.dewptAvg,"°",sep="")
        print("Humidité Maxi      : ",Weather_Dict['observations'][0].humidityHigh,"%",sep="")
        print("Humidité Moy.      : ",Weather_Dict['observations'][0].humidityAvg,"%",sep="")
        print("Humidité Mini      : ",Weather_Dict['observations'][0].humidityLow,"%",sep="")
        print("Pression Max       : ",Weather_Dict['observations'][0].metric.pressureMax,"Hpa",sep="")
        print("Pression Mini      : ",Weather_Dict['observations'][0].metric.pressureMin,"Hpa",sep="")
        print("Vent Max           : ",Weather_Dict['observations'][0].metric.windspeedHigh,"Km/h",sep="")
        print("Vent Moyen         : ",Weather_Dict['observations'][0].metric.windspeedAvg,"Km/h",sep="")
        print("Rafale VentMax     : ",Weather_Dict['observations'][0].metric.windgustHigh,"Km/h",sep="")
        print("Précipitation      : ",Weather_Dict['observations'][0].metric.precipTotal,"mm",sep="")

    #    UpdateDB(DateDash,Weather_Dict,dbpassword)

    start_date += delta
