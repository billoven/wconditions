#!/usr/bin/env python
from __future__ import print_function
import json

# https://pypi.org/project/easydict/ Access easily to Dictionnary values
from easydict import EasyDict as edict

import urllib.request
import pymysql.cursors
from datetime import date, timedelta

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


# Get current date , put it at format YYYYMMDD
yesterday = date.today()-timedelta(1)
DateYYYYMMDD = yesterday.strftime("%Y%m%d")
print("Yesterday's date:", DateYYYYMMDD)

# Build URL to access to current daily observations of ILEDEFRA131
key = 'dd4018037c7740da8018037c7750da2e'
BASE_URL = 'https://api.weather.com/v2/pws/history/daily?stationId=ILEDEFRA131&format=json&units=m'
FEATURE_URL = BASE_URL + f"&date={DateYYYYMMDD}&apiKey={key}&numericPrecision=decimal"

print (FEATURE_URL)
# Execute the HTTPS request to get JSON Result
fp = urllib.request.urlopen(FEATURE_URL)
mybytes = fp.read()

# -*- decoding: utf-8 -*-
mystr = mybytes.decode("utf8")
fp.close()

# used edict ==> Very useful when exploiting parsed JSON content !
mystr_dict = edict(json.loads(mystr))


print(mystr_dict)
print("-----------------")
print("Température Moyenne:",mystr_dict['observations'][0].metric.tempAvg,"°")
print("Température Maxi   :",mystr_dict['observations'][0].metric.tempHigh,"°")
print("Température Mini   :",mystr_dict['observations'][0].metric.tempLow,"°")
print("DewPoint High      :",mystr_dict['observations'][0].metric.dewptHigh)
print("Dewpoint Moy       :",mystr_dict['observations'][0].metric.dewptLow,"°")
print("Dewpoint Mini      :",mystr_dict['observations'][0].metric.dewptAvg,"°")
print("Humidité Maxi      :",mystr_dict['observations'][0].humidityHigh,"%")
print("Humidité Moy.      :",mystr_dict['observations'][0].humidityAvg,"%")
print("Humidité Mini      :",mystr_dict['observations'][0].humidityLow,"%")
print("Pression Max       :",mystr_dict['observations'][0].metric.pressureMax,"Hpa")
print("Pression Mini      :",mystr_dict['observations'][0].metric.pressureMin,"Hpa")
print("Vent Max           :",mystr_dict['observations'][0].metric.windspeedHigh,"Km/h")
print("Vent Moyen         :",mystr_dict['observations'][0].metric.windspeedAvg,"Km/h")
print("Rafale VentMax     :",mystr_dict['observations'][0].metric.windgustHigh,"Km/h")
print("Précipitation      :",mystr_dict['observations'][0].metric.precipTotal,"mm")


#print(mystr)

# Load Json into a Python object





# Get date of the day


# Example of Wunderground URL : 'https://www.wunderground.com/weatherstation/WXDailyHistory.asp?ID=ILEDEFRA131&day=2&month=12&year=2017&dayend=1&monthend=1&yearend=2018&graphspan=custom&format=1
# Compose the Wunderground URL https://api.weather.com/v2/pws/observations/current?stationId=ILEDEFRA131&format=json&numericPrecision=decimal&units=m&apiKey=43de0fca7f6f49a79e0fca7f6f29a708
# API WU : https://docs.google.com/document/d/1eKCnKXI9xnoMGRRzOL1xPCBihNV2rOet08qpE_gArAY/edit
# https://api.weather.com/v2/pws/history/daily?stationId=ILEDEFRA131&format=json&units=m&date=20191101&apiKey=43de0fca7f6f49a79e0fca7f6f29a708&numericPrecision=decimal
# Once a day a query to get the daily forecast summary via a cron
# Result are put in a JSON stucture
# Then the MYSQL Data is updated


exit(0)
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
                             password='Z0-Z0-0-',
                             db='meteovillebon',
                             charset='utf8mb4',
                             cursorclass=pymysql.cursors.DictCursor,
                             autocommit=True)


# Create a Cursor object to execute queries.
cur = connection.cursor()

# Select data from table using SQL query.
cur.execute("SELECT * FROM RelevesMeteo")

# print the first and second columns
for row in cur:
    print (row, " ")

try:
    with connection.cursor() as cursor:
        # Create a new record
        sql = "INSERT INTO `users` (`email`, `password`) VALUES (%s, %s)"
        cursor.execute(sql, ('webmaster@python.org', 'very-secret'))

    # connection is not autocommit by default. So you must commit to save
    # your changes.
    connection.commit()

    with connection.cursor() as cursor:
        # Read a single record
        sql = "SELECT `id`, `password` FROM `users` WHERE `email`=%s"
        cursor.execute(sql, ('webmaster@python.org',))
        result = cursor.fetchone()
        print(result)
finally:
    connection.close()
