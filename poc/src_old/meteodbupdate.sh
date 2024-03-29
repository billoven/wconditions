#!/bin/sh
#------------------------------------------------------------------------------
#
# @(#) %M% (%E% %U%) $ Version: %I% $
#
#------------+---------------+-----------+------------------------------------
# 2004/05/12 | P.STRANART    | V4.1      | Q00 : Creation of tool
#------------+---------------+-----------+------------------------------------
#
# Tool identification
# -------------------
TOOL_ID="%M% $ Version: %I% $\n(� 2004 - Copyrights Alcatel-Lucent iBTS UMTS Swe Methods and tools team)"

#
# Name of Tool
# ------------
TOOL=`basename $0`

#
# Temporary file
# --------------
TMP=/tmp/tmp_${TOOL}.$$

#
# Delete temporary file command
# -----------------------------
RMTMP="rm -f $TMP"

#
# Catch signals
# -------------
trap 'echo "" ; echo "$TOOL : ABANDON OPERATEUR..." ; echo `date '+%d/%m_%H:%M:%S'` ; eval $RMTMP ; exit 1' 1 2 3 4 5 6 7 8 10 12 13 14 15 

#
# Display usage
# -------------
if [ $# = 0 ]
then
	echo ""
        echo " *** $TOOL_ID ***"
	echo ""
	echo "Usage : $TOOL -c <Card composite component> [ -v | -e | -mv | -me | -nv | -ne | -b | -all ]"
	echo
	echo " With:"
	echo " ----"
	echo ""
	echo "	<Card composite component> : Name of card composite component (Ex: uccm_comp)"
	echo ""
	echo "	-b  : Display baseline of Card composite component"
	echo "	-v  : Display version of Card composite component"
	echo "	-e  : Display edition of Card composite component"
	echo "	-mv : Display marking version of Card composite component"
	echo "	-me : Display marking edition of Card composite component"
	echo "	-nv : Display hexa digit version"
	echo "	-ne : Display hexa digit edition"
	echo "	-all : Display all information"
	echo ""
	exit
fi

# Display error message functions
# -------------------------------
err() {
    echo "" >&2
    echo >&2 "$TOOL - ERROR: $*"
    echo "" >&2
    eval $RMTMP
    exit 1
}
warn() {
    echo "" >&2
    echo >&2 "$TOOL - WARNING: $*"
    echo "" >&2
}

card_comp=""
allout="false"
versionout="false"
editionout="false"
markedout="false"
markverout="false"
namedout="false"
nameverout="false"
baselineout="false"
  
# Get Arguments
# -------------
while [ $# -ge 1 ]; do
    case "$1" in
         -c)  [ $# -ge 2 ] || err "Must specify a component name with '-c'"
              shift; card_comp="$1";;
         -c*) card_comp=`expr "$1" : '-c\(.*\)'`;;
	 -v) versionout=true 
            if [ $editionout = "true" ] || [ $markedout = "true" ] || [  $markverout = "true" ] || [ $namedout = "true" ] || [ $nameverout = "true" ] || [ $baselineout = "true" ]
            then
                err "'-v' incompatible option with '-e' | '-me' | '-mv' | '-ne' | '-nv' | '-b' !" ;
            fi ;;
	 -e) editionout=true 
            if [ $versionout = "true" ] || [ $markedout = "true" ] || [  $markverout = "true" ] || [ $namedout = "true" ] || [ $nameverout = "true" ] || [ $baselineout = "true" ]
            then
                err "'-e' incompatible option with '-v' | '-me' | '-mv' | '-ne' | '-nv' | '-b' !" ;
            fi ;;
	 -mv) markverout=true 
            if [ $versionout = "true" ] || [ $markedout = "true" ] || [  $editionout = "true" ] || [ $namedout = "true" ] || [ $nameverout = "true" ] || [ $baselineout = "true" ]
            then
                err "'-mv' incompatible option with '-v' | '-me' | '-e' | '-ne' | '-nv' | '-b' !" ;
            fi ;;
	 -me) markedout=true 
            if [ $versionout = "true" ] || [ $markverout = "true" ] || [  $editionout = "true" ] || [ $namedout = "true" ] || [ $nameverout = "true" ] || [ $baselineout = "true" ]
            then
                err "'-me' incompatible option with '-v' | '-e' | '-mv' | '-ne' | '-nv' | '-b' !" ;
            fi ;;
	 -ne) namedout=true 
            if [ $versionout = "true" ] || [ $markverout = "true" ] || [  $editionout = "true" ] || [ $markedout = "true" ] || [ $nameverout = "true" ] || [ $baselineout = "true" ]
            then
                err "'-ne' incompatible option with '-v' | '-e' | '-mv' | '-me' | '-nv' | '-b' !" ;
            fi ;;
	 -nv) nameverout=true 
            if [ $versionout = "true" ] || [ $markverout = "true" ] || [  $editionout = "true" ] || [ $markedout = "true" ] || [ $namedout = "true" ] || [ $baselineout = "true" ]
            then
                err "'-nv' incompatible option with '-v' | '-e' | '-mv' | '-ne' | '-me' | '-b' !" ;
            fi ;;
	 -b) baselineout=true 
            if [ $versionout = "true" ] || [ $markverout = "true" ] || [  $editionout = "true" ] || [ $markedout = "true" ] || [ $nameverout = "true" ] || [ $namedout = "true" ]
            then
                err "'-b' incompatible option with '-v' | '-me' | '-mv' | '-ne' | '-nv' | '-e' !" ;
            fi ;;
	 -all) allout=true 
            if [ $versionout = "true" ] || [ $markedout = "true" ] || [ $markverout = "true" ] || [ $namedout = "true" ] || [ $nameverout = "true" ] || [ $editionout = "true" ] || [ $baselineout = "true" ]
            then
                err "'-all' incompatible option with '-v' | '-me' | '-mv' | '-ne' | '-nv' | '-e' | '-b' !" ;
            fi ;;
         *)   err "Unrecognized option: '$1'";;
     esac
     shift
done
 
if [ $versionout = "false" ] && [ $markedout = "false" ] && [ $markverout = "false" ] && [ $namedout = "false" ] && [ $nameverout = "false" ] && [ $editionout = "false" ] && [ $baselineout = "false" ] && [ $allout = "false" ]
then
	err  "one of -v' | '-me' | '-mv' | '-ne' | '-nv' | '-e' | '-b' | '-all' must be specified !"
fi
[ "$card_comp" != "" ] || err "Must specify a component name !"


CARD_COMP=_`echo $card_comp | tr '[:lower:]' '[:upper:]'`_

res=`cleartool lscomp -short component:${card_comp}@/btsu_pvob`
if [ "$res" = "" ]
then
	err "Card composite component: [$card_comp] does not exist !"
fi
 
#
# ------------------------------ Start main -----------------------------
#
#-------------------------------------------------
# Get the data from weather underground
WURL="https://www.wunderground.com/weatherstation/WXDailyHistory.asp?ID=ILEDEFRA131&day=1&month=7&year=2018&dayend=31&monthend=7&yea
rend=2018&graphspan=custom&format=1" 

# Get data and suppress <br> html tag and blank lines
# save in text temporary file
curl  $WURL | sed "/<br>/d" | grep -v '^$' >$TMP


# Transform the data in the good format for the future update of DB

# Check the integrity of data and conformity

# Check the access to the Database

# update the DB
edition_name=`echo $edition | sed "s/\.//g" | sed "s/E//"`
version_mark=${version_name}`expr "$edition_name" : "[0-9A-F][0-9A-F]\([0-9A-F]\)"`
edition_mark=`expr "$edition_name" : "\([0-9A-F][0-9A-F]\)[0-9A-F]"`


#
# ------------------------------ End main -----------------------------
#

#
# SUPPRESSION DES FICHIERS TEMPORAIRES
#
eval $RMTMP
