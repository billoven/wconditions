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



#Non., temps, température ambiante, humidité intérieure, température extérieure,
#humidité extérieure, moyenne du vent, rafales de vent, Point de rosée,
#refroidissement éolien, direction du vent, pression absolue, pression relative,
#intensité de précipitation, pluies journalières, pluies semaine ,
#pluies mensuelles, pluies année, solaire, indice de chaleur, UVI

#"No."   "Time"  "Indoor Temperature (°C)"       "Indoor Humidity (%)"   "Outdoor Temperature (°C)"
# "Outdoor Humidity (%)"  "Wind (km/h)"   "Gust (km/h)"   "Dew Point (°C)"
#"Wind Chill (°C)"       "Wind Direction (°)"    "ABS Barometer (hpa)"   "REL Barometer (hpa)"
#"Rain Rate (mm/h)"      "Daily Rain (mm)"       "Weekly Rain (mm)"
#"Monthly Rain (mm)"     "Yearly Rain (mm)"      "Solar Rad. (w/㎡)"     "Heat index (°C)"       "UV (uW/c㎡)"   "UVI"

# 0 [         1]      ==> Non.
# 1 [2019-08-23 00:00] ==> temps.
# 2 [      23.4] ==> température ambiante
# 3 [        46] ==> humidité intérieure.
# 4 [      18.1] ==> température extérieure.
# 5 [        66] ==> humidité extérieure.
# 6 [       0.0] ==> moyenne du vent.
# 7 [       0.0] ==> rafales de vent.
# 8 [      11.7] ==> Point de rosée.
# 9 [      18.1] ==> refroidissement éolien.
# 10 [       128] ==> direction du vent.
# 11 [    1017.8] ==> pression absolue.
# 12 [    1024.2] ==> pression relative.
# 13 [       0.0] ==> intensité de précipitation.
# 14 [       0.0] ==> pluies journalières.
# 15 [       1.8] ==> luies semaine.
# 16 [      57.0] ==> pluies mensuelles.
# 17 [     408.9] ==> pluies année.
# 18 [       0.0] ==> solaire.
# 19 [        --] ==> indice de chaleur.
# 20 [         0] ==> UVI.
# 21 [          ] ==> Non.

#with open('Meteo2019.csv', newline='', encoding='iso-8859-15') as f:
#with open('Meteo20190823-20190831.csv', 'r', encoding='iso-8859-15') as csvfile:
#if inputfile == '<stdin>':
#    csvfile=sys.stdin
#    csvreader = csv.reader(csvfile, delimiter='\t', quotechar='"', lineterminator="\r\n")
#
#        # extracting field names through first row
#    fields = next(csvreader)
#
#    for row in csvreader:
#        ##        # extracting each data row one by one
#        rows.append(row)
#
#    # get total number of rows
#    print(" STDIN Total no. of rows: %d"%(csvreader.line_num))
#else:
#    with open(inputfile, 'r', encoding='utf-16') as csvfile:
#
##    # creating a csv reader object
#    #1  2019-08-23 00:00    23.4    46  18.1    66  0.0 0.0 11.7    18.1    128
#    #   1017.8  1024.2  0.0 0.0 1.8 57.0    408.9   0.0 --  0   ^M
#        csvreader = csv.reader(csvfile, delimiter='\t', quotechar='"', lineterminator="\r\n")

#    # extracting field names through first row
#        fields = next(csvreader)

    # extracting each data row one by one
#        for row in csvreader:
#            rows.append(row)

    # get total number of rows
#        print("Total no. of rows: %d"%(csvreader.line_num))



# printing the field names
#print('Field names are:' + ', '.join(field for field in fields))


#  printing first 5 rows
#print('\nFirst 5 rows are:\n')
#for row in rows[:5]:
    # parsing each column of a row
#    for col in row:
#        print("[%10s]"%col),
#    print('\n')
#    print (rows[0])
#    print (rows[3114])

#    print (len(rows))

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
# from Ambient weather software export the meteo csv file :
#  - has its field separator changed from '<tab>' to ','
#  - has all its double quote removed
#data = pd.read_csv(sys.stdin, names=colnames, encoding='utf-16',sep='\t',skiprows=1)
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
