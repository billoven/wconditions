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

def valid_date_type(arg_date_str):
    """custom argparse *date* type for user dates values given from the command line"""
    try:
        return datetime.strptime(arg_date_str, "%Y-%m-%d")
    except ValueError:
        msg = "Given Date ({0}) not valid! Expected format, YYYY-MM-DD !".format(arg_date_str)
        raise argparse.ArgumentTypeError(msg)
    
#-----------------------------------------------------------------------------
# Arguments management
# ------------------------------------------------------------------------------
def getArgs(argv=None):


    parser = argparse.ArgumentParser(formatter_class=argparse.RawDescriptionHelpFormatter,
    description='''\
        Provide multiple Days Weather metrics
        from DaysWeatherConditions.
            --------------------------------------------------------
             ''',
    epilog='''
    --------------------------------------------------------------''')

   # Use here to check the date a type function
    parser.add_argument('-SD', '--startdate',
                        dest='startdate',
                        type=valid_date_type,
                        default='2016-01-01',
                        required=False,
                        help='start date in format "YYYY-MM-DD"')
    parser.add_argument('-ED', '--enddate',
                        dest='enddate',
                        type=valid_date_type,
                        default=None,
                        required=True,
                        help='End date in format "YYYY-MM-DD"')
    
    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument("-EQ", "--Equal", type=int, required=False, help="Search series with a WeatherData equal to")
    group.add_argument("-NEQ", "--NotEqual", type=int, required=False, help="Search series with a WeatherData not equal to")
    group.add_argument("-GE", "--GreaterEqual", type=int, required=False, help="Search series with a WeatherData greater or equal to")
    group.add_argument("-LE", "--LessEqual", type=int, required=False, help="Search series with a WeatherData less or equal to")
    
    parser.add_argument('-p', '--password',
                        dest='dbpassword',
                        default=None,
                        required=True,
                        help='Mysql user password')
    
    parser.add_argument('-TD', '--DataType',
                        choices=['Tmax','Tmin','Tavg','Hmax','Hmin','Havg','Pavg','Wg','Ws','Rain'],
                        dest='DataType',
                        default='Tavg',
                        required=False,
                        help='Type of Data to be searched in DaysWeatherCondition Table')


    parser.add_argument('-C', '--contiguous', action = "store_true",
                        help='Adjacent days values searched')
    
    parser.add_argument('-d', '--display', action = "store_true",
                        help='Only Display Current Conditions')

 
    parser.add_argument('--version', action='version', version='[%(prog)20s] 2.0')
    print (parser.parse_args(argv))   
    return parser.parse_args(argv)

# Class WC_DataMetrics
class WC_DataMetrics:
    """This class is handling a set of Day WeatherData for Metrics
        Weather Underground wheather station located in Villebon-sur-YVette 
    """
 
    def __init__(self, stationID, mysqlHost, mysqlDBname,user,dbpassword):
        self.stationID = stationID
        self.mysqlHost = mysqlHost
        self.mysqlDBname = mysqlDBname
        self.user = user
        self.dbpassword = dbpassword
    
    def SearchSeries():
        return
    
    def DisplaySeries():
        return
    
    def MaxSeries():
        return
    

if __name__ == "__main__":

    argvals = None             # init argv in case not testing

    args = getArgs(argvals)
    
    print ('display is ',args.display)
    print ('dbpassword is ',args.dbpassword)



