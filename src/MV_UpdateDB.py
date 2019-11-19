#!/usr/bin/env python
from __future__ import print_function

# -*- coding: utf-8 -*-

print ('Mon premier pas en python')

import urllib.request

fp = urllib.request.urlopen("https://api.weather.com/v2/pws/history/daily?stationId=ILEDEFRA131&format=json&units=m&date=20191101&apiKey=43de0fca7f6f49a79e0fca7f6f29a708&numericPrecision=decimal")
mybytes = fp.read()

mystr = mybytes.decode("utf8")
fp.close()

print(mystr)

# Get date of the day

# Example of Wunderground URL : 'https://www.wunderground.com/weatherstation/WXDailyHistory.asp?ID=ILEDEFRA131&day=2&month=12&year=2017&dayend=1&monthend=1&yearend=2018&graphspan=custom&format=1
# Compose the Wunderground URL https://api.weather.com/v2/pws/observations/current?stationId=ILEDEFRA131&format=json&numericPrecision=decimal&units=m&apiKey=43de0fca7f6f49a79e0fca7f6f29a708
# API WU : https://docs.google.com/document/d/1eKCnKXI9xnoMGRRzOL1xPCBihNV2rOet08qpE_gArAY/edit
# https://api.weather.com/v2/pws/history/daily?stationId=ILEDEFRA131&format=json&units=m&date=20191101&apiKey=43de0fca7f6f49a79e0fca7f6f29a708&numericPrecision=decimal
# Once a day a query to get the daily forecast summary via a cron
# Result are put in a JSON stucture
# Then the MYSQL Data is updated

# Get csv format result of URL

# Connect to mysql
# import MySQLdb
import pymysql.cursors

# Connect to the database
connection = pymysql.connect(host='192.168.17.10',
                             user='admin',
                             password='Z--Z--1-',
                             db='meteovillebon',
                             charset='utf8mb4',
                             cursorclass=pymysql.cursors.DictCursor)


# Create a Cursor object to execute queries.
cur = connection.cursor()

# Select data from table using SQL query.
cur.execute("SELECT * FROM RelevesMeteo")

# print the first and second columns
for row in cur:
    print (row, " ")


cur.close()

def print_two(*args):
    arg1, arg2 = args
    print(f"arg1= {arg1}, arg2= {arg2}")

print_two("pierre","maya")
