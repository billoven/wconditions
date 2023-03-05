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

    parser.add_argument('-p', '--password',
                        dest='dbpassword',
                        default=None,
                        required=True,
                        help='Mysql admin user password')
    
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

    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument("-y", "--yesterday", action="store_true", required=False, help="Process Yesterday's data")
    group.add_argument("-t", "--today", action="store_true", required=False, help="Process Today's data")
    


    parser.add_argument('--version', action='version', version='[%(prog)20s] 2.0')
   
    return parser.parse_args(argv)

if __name__ == "__main__":

    argvals = None             # init argv in case not testing

    args = getArgs(argvals)

    print ('display is ',args.display)
    print ('dbpassword is ',args.dbpassword)



