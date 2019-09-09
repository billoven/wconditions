
#!/usr/bin/env python
from __future__ import print_function

# -*- coding: utf-8 -*-

print ('Mon premier pas en python')

# Get date of the day

# Example of Wunderground URL : 'https://www.wunderground.com/weatherstation/WXDailyHistory.asp?ID=ILEDEFRA131&day=2&month=12&year=2017&dayend=1&monthend=1&yearend=2018&graphspan=custom&format=1
# Compose the Wunderground URL

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
