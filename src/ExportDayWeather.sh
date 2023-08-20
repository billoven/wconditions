#! /bin/bash


SHORT=sd:,ed:,h
LONG=startdate:,enddate:,help
OPTS=$(getopt -a -n weather --options $SHORT --longoptions $LONG -- "$@")

eval set -- "$OPTS"

while :
do
  case "$1" in
    -sd | --startdate )
      STARTDATE="$2"
      shift 2
      ;;
    -ed | --enddate )
      ENDDATE="$2"
      shift 2
      ;;
    -h | --help)
      "This is a weather script"
      exit 2
      ;;
    --)
      shift;
      break
      ;;
    *)
      echo "Unexpected option: $1"
      ;;
  esac
done

echo $STARTDATE, $ENDDATE
START_DATE="2023-07-02"
END_DATE="2023-07-29"
mysql -h 192.168.17.10 -SN -u 'admin' -p --database="VillebonWeatherReport" >/tmp/ExportedWeatherConditions.csv <<QUERY
CONNECT VillebonWeatherReport;
SELECT DATE_FORMAT(WC_Date, '%d/%m/%Y'),WC_TempHigh,WC_TempAvg,WC_TempLow,WC_HumidityHigh,WC_HumidityAvg,WC_HumidityLow,WC_DewPointHigh,WC_DewPointAvg,WC_DewPointLow,WC_PressureHigh,WC_PressureLow,WC_WindSpeedMax,'NULL',WC_GustSpeedMax,WC_PrecipitationSum FROM DayWeatherConditions WHERE WC_Date >= '$START_DATE' AND WC_Date <= '$END_DATE';
QUERY
sed 's/	/;/g;s/NULL//g;s/\./,/g' </tmp/ExportedWeatherConditions.csv
