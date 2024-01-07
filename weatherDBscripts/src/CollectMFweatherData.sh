#!/bin/bash

# This Script is collecting Weather Data from meteo.data.gouv.fr for a specific Weather Station
# and prepare a SQL Insert request for building a table of Weather data for a period of years
# example of weather data : QUOT_departement_75_periode_1950-2021_RR-T-Vent.csv
# which are part of : "Données climatologiques de base - quotidiennes"
# Format is : UM_POSTE;NOM_USUEL;LAT;LON;ALTI;AAAAMMJJ;RR;QRR;TN;QTN;HTN;QHTN;TX;QTX;HTX;QHTX;TM;QTM;TNTXM;QTNTXM;TAMPLI;QTAMPLI;TNSOL;QTNSOL;TN50;QTN50;DG;QDG;FFM;QFFM;FF2M;QFF2M;FXY;QFXY;DXY;QDXY;HXY;QHXY;FXI;QFXI;DXI;QDXI;HXI;QHXI;FXI2;QFXI2;DXI2;QDXI2;HXI2;QHXI2;FXI3S;QFXI3S;DXI3S;QDXI3S;HXI3S;QHXI3S
# This fields are explained in the file:  CollectMFweatherData.txt

# Default values
input_file=""
output_file=""
station_name=""
start_year=""
end_year=""

# Function to display usage
usage() {
    echo "Usage: $0 -i <input_file> -o <output_file> -s <station_name> -y <start_year> -e <end_year>"
    exit 1
}

# Process command line options
while getopts ":i:o:s:y:e:" opt; do
    case ${opt} in
        i) input_file="${OPTARG}" ;;
        o) output_file="${OPTARG}" ;;
        s) station_name="${OPTARG}" ;;
        y) start_year="${OPTARG}" ;;
        e) end_year="${OPTARG}" ;;
        \?) echo "Invalid option: -$OPTARG" >&2; usage ;;
        :) echo "Option -$OPTARG requires an argument." >&2; usage ;;
    esac
done

# Check if all required options are provided
if [ -z "$input_file" ] || [ -z "$output_file" ] || [ -z "$station_name" ] || [ -z "$start_year" ] || [ -z "$end_year" ]; then
    echo "All options (-i, -o, -s, -y, -e) are required."
    usage
fi

# Supprimer le fichier de sortie s'il existe déjà
rm -f "$output_file"

# En-tête du fichier SQL
echo "INSERT INTO votre_table_sql (WC_Date, WC_TempAvg, WC_TempHigh, WC_TempLow, WC_DewPointAvg, WC_DewPointHigh, WC_DewPointLow, WC_HumidityAvg, WC_HumidityHigh, WC_HumidityLow, WC_PressureAvg, WC_PressureHigh, WC_PressureLow, WC_WindSpeedMax, WC_GustSpeedMax, WC_PrecipitationSum) VALUES" >> "$output_file"

# Lecture du fichier CSV ligne par ligne
awk -F';' -v station="$station_name" -v start_year="$start_year" -v end_year="$end_year" '
    BEGIN {OFS=";"; print "RR;TN;TX;TM;TNTXM;DG;FFM;WC_Date"}

    # Function to convert AAAAMMJJ to YYYY-MM-DD
    function convertDate(date) {
        year = substr(date, 1, 4)
        month = substr(date, 5, 2)
        day = substr(date, 7, 2)
        return year "-" month "-" day
    }

    # Process data lines
    NR > 1 {
        # Extract year from the date
        year = substr($6, 1, 4)

        # Check if station name matches and year is within the specified range
        if ($2 == station && year >= start_year && year <= end_year) {
            # Extract relevant fields and convert date format
            printf "DATE found: %s\n",$6
            wc_date = convertDate($6)
            wc_temp_avg = $16
            wc_temp_high = $14
            wc_temp_low = $18
            wc_precipitation_sum = $8
            wc_dew_point_avg = $20
            wc_dew_point_high = $22
            wc_dew_point_low = $24
            wc_humidity_avg = $26
            wc_humidity_high = $28
            wc_humidity_low = $30
            wc_pressure_avg = $32
            wc_pressure_high = $34
            wc_pressure_low = $36
            wc_wind_speed_max = $38
            wc_gust_speed_max = $40

            # Print the formatted output
            print $8, $10, $12, $14, $20, $22, $24, $26, $28, $30, $32, $34, $36, $38, $40, wc_date
        }
    }
' "$input_file" 
exit
#| while IFS=';' read -r RR TN TX TM TNTXM DG FFM WC_Date; do
    # Vérifier si TM est vide
    if [ -z "$TM" ]; then

    	# Set LC_NUMERIC to C to ensure dot as the decimal separator
    	LC_NUMERIC=C
	    export LC_NUMERIC

        # Vérifier si TN et TX existent et ne sont pas vides
        if [ -n "$TN" ] && [ -n "$TX" ]; then
            # Calculer la moyenne entre TN et TX
            TM=$(awk "BEGIN {printf \"%.1f\", ($TN + $TX) / 2}")
        else
            # Si TN ou TX est manquant, laisser TM vide (NULL)
            TM="NULL"
        fi
        
        # Reset LC_NUMERIC to its original value
    	unset LC_NUMERIC

    fi

    # Vérifier si TN est vide
    if [ -z "$TN" ]; then
        TN="NULL"
    fi

    # Vérifier si TX est vide
    if [ -z "$TX" ]; then
        TX="NULL"
    fi

    # Vérifier si TNTXM est vide
    if [ -z "$TNTXM" ]; then
        TNTXM="NULL"
    fi

    # Vérifier si DG est vide
    if [ -z "$DG" ]; then
        DG="NULL"
    fi

    # Vérifier si FFM est vide
    if [ -z "$FFM" ]; then
        FFM="NULL"
    fi

    # Convertir le format de la date de AAAAMMJJ à YYYY-MM-DD
    formatted_date=$(date -d "$WC_Date" "+%Y-%m-%d")

    # Écrire la ligne SQL dans le fichier de sortie
    echo "('$formatted_date', $TM, $TX, $TN, $TNTXM, $TNTXM, $TNTXM, $DG, $DG, $DG, NULL, NULL, NULL, NULL, NULL, $RR)," >> "$output_file"
done

# Supprimer la dernière virgule de la dernière ligne
sed -i '$s/,$//' "$output_file"

# Ajouter un point-virgule à la fin de la requête SQL
echo ";" >> "$output_file"

echo "Transformation terminée. Le fichier SQL a été créé : $output_file"
