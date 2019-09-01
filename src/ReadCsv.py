#!/usr/bin/env python3

import csv
import pandas as pd
from dateutil.parser import *
import datetime

# Python program to get average of a list
# Using mean()
# importing mean()
from statistics import mean

def Average(lst):
    return mean(lst)

# Transform <date> <time> or <time> <date> in format DD/MM/YYYY
def DateDDMMYY(Date):


    d=parse(Date, dayfirst=True)
    NewDate=d.strftime("%d/%m/%Y")

    return(NewDate)

def DayStat(Data):

    DataList=[]

    # Statistic of the day's outdoor temperatures
    DayMaxOutdoorTemp=max(Data.get('OutdoorTemperature'))
    DayMinOutdoorTemp=min(Data.get('OutdoorTemperature'))
    DayAvgOutdoorTemp=round(Average(Data.get('OutdoorTemperature')),1)

    # Statistic of the day Dewpoint
    DayMaxOutdoorHum=max(Data['OutdoorHumidity'])
    DayMinOutdoorHum=min(Data['OutdoorHumidity'])
    DayAvgOutdoorHum=round(Average(Data['OutdoorHumidity']),1)

    # Statistic of the day Dewpoint
    DayMaxDewPoint=max(Data['DewPoint'])
    DayMinDewPoint=min(Data['DewPoint'])
    DayAvgDewPoint=round(Average(Data['DewPoint']),1)

    # Statistic of the day's Pressure
    DayMaxPressure=max(Data.get('Pressure'))
    DayMinPressure=min(Data.get('Pressure'))

    # Statistic of the day's WindSpeed
    DayMaxWindSpeed=max(Data.get('Wind'))
    DayAvgWindSpeed=round(Average(Data.get('Wind')),1)

    # Statistic of the day's GustSpeed
    DayMaxGustSpeed=max(Data.get('Gust'))

    # Statistic of the day's Precipitations in CM/M²
    DayMaxPrecipitationCum=round(max(Data.get('DailyRain'))/10,2)

    DataList.append(Data['Time'][0])
    DataList.append(DayMaxOutdoorTemp)
    DataList.append(DayAvgOutdoorTemp)
    DataList.append(DayMinOutdoorTemp)
    DataList.append(DayMaxOutdoorHum)
    DataList.append(DayAvgOutdoorHum)
    DataList.append(DayMinOutdoorHum)
    DataList.append(DayMaxDewPoint)
    DataList.append(DayAvgDewPoint)
    DataList.append(DayMinDewPoint)
    DataList.append(DayMaxPressure)
    DataList.append(DayMinPressure)
    DataList.append(DayMaxWindSpeed)
    DataList.append(DayAvgWindSpeed)
    DataList.append(DayMaxGustSpeed)
    DataList.append(DayMaxPrecipitationCum)

    return DataList

def ConvertList(list):

    rowtobewritten=''
    row=''

    # Convert first WeatherData List with mixed types in a str list
    list = [str(i) for i in list]

    row=';'.join(field for field in list)
    rowtobewritten=row.replace(".",",")

    return rowtobewritten


# Dictionary ==> list = {"temperature" : [ 11, 13, 14 ]}
# print list.get("temperature")
# [11, 13, 14]
#
# >>> temps = [ 10, 12, 11 ]
# >>> list = {"temperature" : temps}
# >>> print list.get('temperature')
# [10, 12, 11]
#>>> humidity = [ 55, 47, 89 ]
#>>> list = {"temperature" : temps, "humidite" : humidity}
#>>> print list.get('humidity')
#None
#>>> print list.get('humidite')
# [55, 47, 89]

# Date,
# TemperatureHighC,
# TemperatureAvgC,
# TemperatureLowC,
# DewpointHighC,
# DewpointAvgC,
# DewpointLowC,
# HumidityHigh,
# HumidityAvg
# HumidityLow
# PressureMaxhPa
# PressureMinhPa
# WindSpeedMaxKMH
# WindSpeedAvgKMH
# GustSpeedMaxKMH
# PrecipitationSumCM

#
# initializing the titles and rows list
i=0
IndexDay=0

WeatherDataDay =    { 'Time' : [],
                      'OutdoorTemperature' : [],
                      'OutdoorHumidity' : [],
                      'Wind' : [],
                      'Gust' : [],
                      'DewPoint' : [],
                      'Pressure' : [],
                      'DailyRain' : []
                    }

# Weather Date list
colnames = ['Time','IndoorTemperature','IndoorHumidity','OutdoorTemperature','OutdoorHumidity','Wind','Gust','DewPoint','WindChill','WindDirection','ABSBarometer','RELBarometer','RainRate','DailyRain','WeeklyRain',	'MonthlyRain','YearlyRain',	'Solar, Rad','Heatindex','UV','UVI']

# Read Transformed Meteo csv file
# from Ambient weather software export the meteo csv file :
#  - has its field separator changed from '<tab>' to ','
#  - has all its double quote removed
data = pd.read_csv('res1', names=colnames, encoding='iso-8859-1',sep=',')

# open a file for writing
weather_data = open('/tmp/NewMeteo2019.csv', 'w')

# create the csv writer object
csvwriter = csv.writer(weather_data)


# Create lists, one per type of weather data
Dates = data.Time.tolist()
OutdoorTemps = data.OutdoorTemperature.tolist()
OutdoorHums = data.OutdoorHumidity.tolist()
Winds = data.Wind.tolist()
Gusts = data.Gust.tolist()
DewPoints = data.DewPoint.tolist()
RELBarometers = data.RELBarometer.tolist()
DailyRains = data.DailyRain.tolist()

# At start the "Previous Date "
print(Dates)
PreviousDate=DateDDMMYY(Dates[0])

for DateTime in Dates:

    # List of Daily stats initialized to empty
    ListOfDailyStat=[]

    # Validate dates Time and correct some fields with "<time> <date>" to "<date> <time>"
    DayDate=DateDDMMYY(DateTime)

    if DayDate != PreviousDate :
        ListOfDailyStat=DayStat(WeatherDataDay)

        row=ConvertList(ListOfDailyStat)
        weather_data.write("%s\n" % row)

        WeatherDataDay['Time'].clear()
        WeatherDataDay['OutdoorTemperature'].clear()
        WeatherDataDay['OutdoorHumidity'].clear()
        WeatherDataDay['Wind'].clear()
        WeatherDataDay['Gust'].clear()
        WeatherDataDay['DewPoint'].clear()
        WeatherDataDay['Pressure'].clear()
        WeatherDataDay['DailyRain'].clear()

    WeatherDataDay['Time'].append(DayDate)
    WeatherDataDay['OutdoorTemperature'].append(OutdoorTemps[i])
    WeatherDataDay['OutdoorHumidity'].append(OutdoorHums[i])
    WeatherDataDay['Wind'].append(Winds[i])
    WeatherDataDay['Gust'].append(Gusts[i])
    WeatherDataDay['DewPoint'].append(DewPoints[i])
    if RELBarometers[i] != "--.-" and RELBarometers[i] != "--":
                WeatherDataDay['Pressure'].append(float(RELBarometers[i]))
    WeatherDataDay['DailyRain'].append(DailyRains[i])

    PreviousDate = DayDate

    # Increment Loop index
    i+=1

# Last Day
ListOfDailyStat=DayStat(WeatherDataDay)
row=ConvertList(ListOfDailyStat)
weather_data.write("%s\n" % row)

weather_data.close()
