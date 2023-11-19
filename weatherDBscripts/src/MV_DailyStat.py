#!/usr/bin/env python3

import csv
import pandas as pd
from dateutil.parser import *
import datetime


# Python program to get average of a list
# Using mean()
# importing mean()
from statistics import mean

import sys
import argparse


inputfile = ''
outputfile = ''

# ------------------------------------------------------------------------------
# Arguments management
# ------------------------------------------------------------------------------
def getArgs(argv=None):


    parser = argparse.ArgumentParser(formatter_class=argparse.RawDescriptionHelpFormatter,
    description='''\
        Transform csv file exported from EasyWeatherIP Software
        for WS-1001-WiFi Weather Station for PC-Win
        (Ambient Weather Company)
        --------------------------------------------------------
             The csv file is exported manualy from the application.
             Then stored in a dedicated directory.

         ''',
    epilog='''
    --------------------------------------------------------------''')

    group = parser.add_mutually_exclusive_group()
    group.add_argument("-v", "--verbose", action="store_true")
    group.add_argument("-q", "--quiet", action="store_true")
    parser.add_argument('--infile', "-i", nargs='?', type=argparse.FileType('r'),
        default=sys.stdin, help="csv file pathname to tranform")
    parser.add_argument('--outfile', "-o", nargs='?', type=argparse.FileType('w'),
        default=sys.stdout, help="transformed csv file pathname")
    parser.add_argument('--version', action='version', version='[%(prog)20s] 2.0')

    return parser.parse_args(argv)

def Average(lst):
    return mean(lst)

# Transform <date> <time> or <time> <date> in format DD/MM/YYYY
# ------------------------------------------------------------------------------
# initializing the titles and rows list
# ------------------------------------------------------------------------------
def DateDDMMYY(Date):


    d=parse(Date, yearfirst=True)
    NewDate=d.strftime("%d/%m/%Y")

    return(NewDate)

# ------------------------------------------------------------------------------
# initializing the titles and rows list
# ------------------------------------------------------------------------------
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

# ------------------------------------------------------------------------------
# initializing the titles and rows list
# ------------------------------------------------------------------------------
def ConvertList(list):

    rowtobewritten=''
    row=''

    # Convert first WeatherData List with mixed types in a str list
    list = [str(i) for i in list]

    row=';'.join(field for field in list)
    rowtobewritten=row.replace(".",",")

    return rowtobewritten


# ------------------------------------------------------------------------------
# initializing the titles and rows list
# ------------------------------------------------------------------------------
fields = []
rows = []

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


print ('Input file is ', args.infile.name)
print ('Output file is ', args.outfile.name)


inputfile=args.infile.name
outputfile=args.outfile.name

if inputfile == '<stdin>':
    inputfile=sys.stdin

# ------------------------------------------------------------------------------
# In French
#Non., temps, température ambiante, humidité intérieure, température extérieure,
#humidité extérieure, moyenne du vent, rafales de vent, Point de rosée,
#refroidissement éolien, direction du vent, pression absolue, pression relative,
#intensité de précipitation, pluies journalières, pluies semaine ,
#pluies mensuelles, pluies année, solaire, indice de chaleur, UVI
# ------------------------------------------------------------------------------
# In English
#"No."   "Time"  "Indoor Temperature (°C)"       "Indoor Humidity (%)"   "Outdoor Temperature (°C)"
# "Outdoor Humidity (%)"  "Wind (km/h)"   "Gust (km/h)"   "Dew Point (°C)"
#"Wind Chill (°C)"       "Wind Direction (°)"    "ABS Barometer (hpa)"   "REL Barometer (hpa)"
#"Rain Rate (mm/h)"      "Daily Rain (mm)"       "Weekly Rain (mm)"
#"Monthly Rain (mm)"     "Yearly Rain (mm)"      "Solar Rad. (w/㎡)"     "Heat index (°C)"       "UV (uW/c㎡)"   "UVI"
#
#No.	Time	Indoor temperature	Indoor humidity	Outdoor temperature	Outdoor humidity	Average speed	Gust speed	Dewpoint	Wind chill	Wind direction	Absolute Pressure	Relative pressure	Rainfall intensity	Daily rainfall	Weekly rainfallMonthly rainfall	Yearly rainfall	Solar	Heat Index	UVI
#1	2019-09-01 00:00	25.9	48	19.5	71	0.0	0.0	14.1	19.5	158	1006.5	1012.9	0.0	0.0	0.0	0.0	408.9	0.0	--	0
#2	2019-09-01 00:04	25.9	48	19.5	71	0.0	0.0	14.1	19.5	159	1006.6	1013.0	0.0	0.0	0.0	0.0	408.9	0.0	--	0
# ------------------------------------------------------------------------------


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
# Field names are:No., Time, Indoor temperature, Indoor humidity, Outdoor temperature,
# Outdoor humidity, Average speed, Gust speed, Dewpoint, Wind chill, Wind direction,
# Absolute Pressure, Relative pressure, Rainfall intensity, Daily rainfall, Weekly rainfall,
# Monthly rainfall, Yearly rainfall, Solar, Heat Index, UVI,
colnames = ['No','Time','IndoorTemperature','IndoorHumidity','OutdoorTemperature','OutdoorHumidity','Wind','Gust','DewPoint','WindChill','WindDirection','ABSBarometer','RELBarometer','RainRate','DailyRain','WeeklyRain','MonthlyRain','YearlyRain',	'Solar, Rad','Heatindex','UV','UVI']

# Read Transformed Meteo csv file
# from Ambient weather software export the meteo csv file is exported through
# the History menu for a given period
data = pd.read_csv(inputfile, names=colnames, encoding='utf-16',sep='\t',skiprows=1)

print("--------------------------------")
print (data.Time)



# open a file for writing
if outputfile != '<stdout>':
    weather_data = open(outputfile, 'w')
else:
    weather_data = sys.stdout

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
PreviousDate=DateDDMMYY(Dates[0])

for DateTime in Dates:

    # List of Daily stats initialized to empty
    ListOfDailyStat=[]

    # Validate dates Time and correct some fields with "<time> <date>" to "<date> <time>"
    DayDate=DateDDMMYY(DateTime)

    if DayDate != PreviousDate :
        #print  (WeatherDataDay)
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
    if OutdoorTemps[i] != "--.-" and OutdoorTemps[i] != "--":
        WeatherDataDay['OutdoorTemperature'].append(float(OutdoorTemps[i]))
    if OutdoorHums[i] != "--.-" and OutdoorHums[i] != "--":
        WeatherDataDay['OutdoorHumidity'].append(float(OutdoorHums[i]))
    if Winds[i] != "--.-" and Winds[i] != "--":
        WeatherDataDay['Wind'].append(float(Winds[i]))
    if Gusts[i] != "--.-" and Gusts[i] != "--":
        WeatherDataDay['Gust'].append(Gusts[i])
    if DewPoints[i] != "--.-" and DewPoints[i] != "--":
        WeatherDataDay['DewPoint'].append(float(DewPoints[i]))
    if RELBarometers[i] != "--.-" and RELBarometers[i] != "--":
                WeatherDataDay['Pressure'].append(float(RELBarometers[i]))
    if DailyRains[i] != "--.-" and DailyRains[i] != "--":
        WeatherDataDay['DailyRain'].append(DailyRains[i])

    PreviousDate = DayDate

    # Increment Loop index
    i+=1

# Last Day
ListOfDailyStat=DayStat(WeatherDataDay)
row=ConvertList(ListOfDailyStat)
weather_data.write("%s\n" % row)

weather_data.close()
