#!/bin/bash

# MV_csvinitConv.sh

# Steps to transform the CSV file exported from weather station data
# 1.
# 	Replace first '-' of dates: YYYY-MM-DD by '/'
# 	sed 's/-/\//' meteo20190501-20190823.csv  | sed 's/-/\//' > meteo20190501.csv
# 2.
# 	Convert UTF-16 format of CSV file in ISO-8859-1
#  	iconv -f UTF-16 -t ISO-8859-1 meteo20190501.csv
# 3.
#	Remove first field, and convert Dates YYYY/MM/DD in DD/MM/YYYY
# NOM DE L'OUTIL
#
TOOL=`basename $0`

#
# FICHIERS TEMPORAIRES
#
TMP=/tmp/tmp_${TOOL}.$$

# COMMANDE DE DESTRUCTION DES TEMPORAIRES
#
RMTMP="rm -f $TMP"


# iconv -f UTF-16 -t ISO-8859-1 $1 | sed 's/-/\//' | sed 's/-/\//' | sed 's/--\.-//g' | sed 's/--//g' >$TMP
cat $1 |  sed 's/--\.-//g' | sed 's/--//g' >$TMP
#cat $1 | sed 's/-/\//' | sed 's/-/\//' | sed 's/--\.-//g' | sed 's/--//g' >$TMP
awk -F',' '{
		PosSpace=index($2," ")
		long=length($2)
		date=substr($2,0,PosSpace)
 		time=substr($2,PosSpace+1,long-length(date))
		if ( time !~ /^[0-9][0-9]?:[0-9][0-9]$/ ) {
			newdate=time
			newtime=date
		} else {
			newdate=date
			newtime=time
		}
		cmd=sprintf("echo -n \"`date -d %s +%%d/%%m/%%Y`\"",newdate)
		system(cmd)
        	printf " %s,",newtime	
		for (i=3; i<=NF;i++) { 
		
			printf "%s",$i
			if ( i != NF) 
				printf ","
			else
				printf "\n"
		}
	}' < $TMP 

eval $RMTMP
