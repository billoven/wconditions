#!/bin/bash

# Vérifier si le nombre correct d'arguments est fourni
if [ "$#" -ne 3 ]; then
    echo "Usage: $0 <input_file> <output_file>"
    exit 1
fi

# Assigner les arguments aux variables
input_file="$1"
output_file="$2"

# Supprimer le fichier de sortie s'il existe déjà
rm -f "$output_file"

# En-tête du fichier SQL
echo "INSERT INTO votre_table_sql (WC_Date, WC_TempAvg, WC_TempHigh, WC_TempLow, WC_HumidityAvg, WC_HumidityHigh, WC_HumidityLow, WC_PressureAvg, WC_PressureHigh, WC_PressureLow, WC_WindSpeedMax, WC_GustSpeedMax, WC_PrecipitationSum) VALUES" >> "$output_file"

# Lecture du fichier CSV ligne par ligne
tail -n +2 "$input_file" | while IFS=';' read -r AAAAMMJJ NUM_POSTE NOM_USUEL LAT LON ALTI RR QRR TN QTN HTN QHTN TX QTX HTX QHTX TM QTM TNTXM QTNTXM TAMPLI QTAMPLI TNSOL QTNSOL TN50 QTN50 DG QDG FFM QFFM FF2M QFF2M FXY QFXY DXY QDXY HXY QHXY FXI QFXI DXI QDXI HXI QHXI FXI2 QFXI2 DXI2 QDXI2 HXI2 QHXI2 FXI3S QFXI3S DXI3S QDXI3S HXI3S QHXI3S NUM_POSTE NOM_USUEL LAT LON ALTI DHUMEC QDHUMEC PMERM QPMERM PMERMIN QPMERMIN INST QINST GLOT QGLOT DIFT QDIFT DIRT QDIRT INFRART QINFRART UV QUV UV_INDICEX QUV_INDICEX SIGMA QSIGMA UN QUN HUN QHUN UX QUX HUX QHUX UM QUM DHUMI40 QDHUMI40 DHUMI80 QDHUMI80 TSVM QTSVM ETPMON QETPMON ETPGRILLE QETPGRILLE ECOULEMENTM QECOULEMENTM HNEIGEF QHNEIGEF NEIGETOTX QNEIGETOTX NEIGETOT06 QNEIGETOT06 NEIG QNEIG BROU QBROU ORAG QORAG GRESIL QGRESIL GRELE QGRELE ROSEE QROSEE VERGLAS QVERGLAS SOLNEIGE QSOLNEIGE GELEE QGELEE FUMEE QFUMEE BRUME QBRUME ECLAIR QECLAIR NB300 QNB300 BA300 QBA300 TMERMIN QTMERMIN TMERMAX QTMERMAX; do
    # Convertir le format de la date de AAAAMMJJ à YYYY-MM-DD
    formatted_date=$(date -d "$AAAAMMJJ" "+%Y-%m-%d")

    # Calculer la pression maximale manquante
    if [ -z "$PMERMIN" ] || [ -z "$PMERM" ]; then
        # Au moins une des deux valeurs manque
        PRESSURE_HIGH=NULL
    else
        # Les deux valeurs sont présentes, calculer la pression maximale manquante
        PRESSURE_HIGH=$(echo "scale=1; 2 * $PMERM - $PMERMIN" | bc)
    fi
   
    # Vérifier et traiter les champs sans valeur
    if [ -z "$UM" ]; then UM=NULL; fi
    if [ -z "$UN" ]; then UN=NULL; fi
    if [ -z "$UX" ]; then UX=NULL; fi
    if [ -z "$PMERM" ]; then PMERM=NULL; fi
    if [ -z "$PMERMIN" ]; then PMERMIN=NULL; fi

    # Vent en m/s calculé et stocké en Km/h
    if [ -z "$FXY" ]; then FXY=NULL; else FXY=$(echo "scale=1; $FXY * 3.6" | bc); fi
    if [ -z "$FXI" ]; then FXI=NULL; else FXI=$(echo "scale=1; $FXI * 3.6" | bc); fi



    # Écrire la ligne SQL dans le fichier de sortie
    echo "('$formatted_date', $TM, $TX, $TN, $UM, $UX, $UN, $PMERM, $PRESSURE_HIGH, $PMERMIN, $FXY, $FXI, $RR)," >> "$output_file"
done

# Supprimer la dernière virgule de la dernière ligne
sed -i '$s/,$//' "$output_file"

# Ajouter un point-virgule à la fin de la requête SQL
echo ";" >> "$output_file"

echo "Transformation terminée. Le fichier SQL a été créé : $output_file"
