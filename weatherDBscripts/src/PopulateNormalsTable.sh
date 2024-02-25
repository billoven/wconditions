#!/bin/bash

# Database connection parameters
DB_USER="admin"
DB_HOSTNAME="192.168.17.10"
# DB_PASSWORD="your_password"
DB_NAME="VillebonWeatherReport"

# Table names
SOURCE_TABLE="DayWeatherConditions"
DEST_TABLE="Normals_2016_2023"

# MySQL command to execute SQL statements
MYSQL_CMD="mysql -u$DB_USER -p -h$DB_HOSTNAME -D$DB_NAME -e"

# SQL statements to create and populate the new table
SQL_CREATE_TABLE="CREATE TABLE $DB_NAME.$DEST_TABLE (
    DayOfYear           VARCHAR(5)   NOT NULL PRIMARY KEY,
    AvgTempAvg          DECIMAL(3,1) NULL,
    AvgTempHigh         DECIMAL(3,1) NULL,
    AvgTempLow          DECIMAL(3,1) NULL,
    MinTempAvg          DECIMAL(3,1) NULL,
    MaxTempAvg          DECIMAL(3,1) NULL,
    MinTempHigh         DECIMAL(3,1) NULL,
    MaxTempHigh         DECIMAL(3,1) NULL,
    MinTempLow          DECIMAL(3,1) NULL,
    MaxTempLow          DECIMAL(3,1) NULL,
    AvgDewPointAvg      DECIMAL(3,1) NULL,
    AvgDewPointHigh     DECIMAL(3,1) NULL,
    AvgDewPointLow      DECIMAL(3,1) NULL,
    MinDewPointAvg      DECIMAL(3,1) NULL,
    MaxDewPointAvg      DECIMAL(3,1) NULL,
    MinDewPointHigh     DECIMAL(3,1) NULL,
    MaxDewPointHigh     DECIMAL(3,1) NULL,
    MinDewPointLow      DECIMAL(3,1) NULL,
    MaxDewPointLow      DECIMAL(3,1) NULL,
    AvgHumidityAvg      INT(3)       NULL,
    AvgHumidityHigh     INT(3)       NULL,
    AvgHumidityLow      INT(3)       NULL,
    MinHumidityAvg      INT(3)       NULL,
    MaxHumidityAvg      INT(3)       NULL,
    MinHumidityHigh     INT(3)       NULL,
    MaxHumidityHigh     INT(3)       NULL,
    MinHumidityLow      INT(3)       NULL,
    MaxHumidityLow      INT(3)       NULL,
    AvgPressureAvg      DECIMAL(5,1) NULL,
    AvgPressureHigh     DECIMAL(5,1) NULL,
    AvgPressureLow      DECIMAL(5,1) NULL,
    MinPressureAvg      DECIMAL(5,1) NULL,
    MaxPressureAvg      DECIMAL(5,1) NULL,
    MinPressureHigh     DECIMAL(5,1) NULL,
    MaxPressureHigh     DECIMAL(5,1) NULL,
    MinPressureLow      DECIMAL(5,1) NULL,
    MaxPressureLow      DECIMAL(5,1) NULL,
    AvgWindSpeedMax     DECIMAL(4,1) NULL,
    MaxWindSpeedMax     DECIMAL(4,1) NULL,
    AvgGustSpeedMax     DECIMAL(4,1) NULL,
    MaxGustSpeedMax     DECIMAL(4,1) NULL,
    AvgPrecipitationSum DECIMAL(4,1) NULL,
    MinPrecipitationSum DECIMAL(4,1) NULL,
    MaxPrecipitationSum DECIMAL(4,1) NULL
);"

SQL_INSERT_DATA="INSERT INTO $DB_NAME.$DEST_TABLE (DayOfYear, AvgTempAvg, AvgTempHigh, AvgTempLow, MinTempAvg, MaxTempAvg, MinTempHigh, MaxTempHigh, MinTempLow, MaxTempLow,
    AvgDewPointAvg, AvgDewPointHigh, AvgDewPointLow, MinDewPointAvg, MaxDewPointAvg, MinDewPointHigh, MaxDewPointHigh, MinDewPointLow, MaxDewPointLow,
    AvgHumidityAvg, AvgHumidityHigh, AvgHumidityLow, MinHumidityAvg, MaxHumidityAvg, MinHumidityHigh, MaxHumidityHigh, MinHumidityLow, MaxHumidityLow,
    AvgPressureAvg, AvgPressureHigh, AvgPressureLow, MinPressureAvg, MaxPressureAvg, MinPressureHigh, MaxPressureHigh, MinPressureLow, MaxPressureLow,
    AvgWindSpeedMax, MaxWindSpeedMax, AvgGustSpeedMax, MaxGustSpeedMax, AvgPrecipitationSum, MinPrecipitationSum, MaxPrecipitationSum)
SELECT
    DATE_FORMAT(WC_Date, '%m-%d') AS DayOfYear,
    AVG(WC_TempAvg) AS AvgTempAvg,
    AVG(WC_TempHigh) AS AvgTempHigh,
    AVG(WC_TempLow) AS AvgTempLow,
    MIN(WC_TempAvg) AS MinTempAvg,
    MAX(WC_TempAvg) AS MaxTempAvg,
    MIN(WC_TempHigh) AS MinTempHigh,
    MAX(WC_TempHigh) AS MaxTempHigh,
    MIN(WC_TempLow) AS MinTempLow,
    MAX(WC_TempLow) AS MaxTempLow,
    AVG(WC_DewPointAvg) AS AvgDewPointAvg,
    AVG(WC_DewPointHigh) AS AvgDewPointHigh,
    AVG(WC_DewPointLow) AS AvgDewPointLow,
    MIN(WC_DewPointAvg) AS MinDewPointAvg,
    MAX(WC_DewPointAvg) AS MaxDewPointAvg,
    MIN(WC_DewPointHigh) AS MinDewPointHigh,
    MAX(WC_DewPointHigh) AS MaxDewPointHigh,
    MIN(WC_DewPointLow) AS MinDewPointLow,
    MAX(WC_DewPointLow) AS MaxDewPointLow,
    AVG(WC_HumidityAvg) AS AvgHumidityAvg,
    AVG(WC_HumidityHigh) AS AvgHumidityHigh,
    AVG(WC_HumidityLow) AS AvgHumidityLow,
    MIN(WC_HumidityAvg) AS MinHumidityAvg,
    MAX(WC_HumidityAvg) AS MaxHumidityAvg,
    MIN(WC_HumidityHigh) AS MinHumidityHigh,
    MAX(WC_HumidityHigh) AS MaxHumidityHigh,
    MIN(WC_HumidityLow) AS MinHumidityLow,
    MAX(WC_HumidityLow) AS MaxHumidityLow,
    AVG(WC_PressureAvg) AS AvgPressureAvg,
    AVG(WC_PressureHigh) AS AvgPressureHigh,
    AVG(WC_PressureLow) AS AvgPressureLow,
    MIN(WC_PressureAvg) AS MinPressureAvg,
    MAX(WC_PressureAvg) AS MaxPressureAvg,
    MIN(WC_PressureHigh) AS MinPressureHigh,
    MAX(WC_PressureHigh) AS MaxPressureHigh,
    MIN(WC_PressureLow) AS MinPressureLow,
    MAX(WC_PressureLow) AS MaxPressureLow,
    AVG(WC_WindSpeedMax) AS AvgWindSpeedMax,
    MAX(WC_WindSpeedMax) AS MaxWindSpeedMax,
    AVG(WC_GustSpeedMax) AS AvgGustSpeedMax,
    MAX(WC_GustSpeedMax) AS MaxGustSpeedMax,
    AVG(WC_PrecipitationSum) AS AvgPrecipitationSum,
    MIN(WC_PrecipitationSum) AS MinPrecipitationSum,
    MAX(WC_PrecipitationSum) AS MaxPrecipitationSum
FROM
    $DB_NAME.$SOURCE_TABLE
WHERE
    WC_Date BETWEEN '2016-01-01' AND '2023-12-31'
GROUP BY
    DayOfYear;"

# Execute SQL statements with error handling
if $MYSQL_CMD "$SQL_CREATE_TABLE"; then
    echo "Table $DEST_TABLE created."
else
    echo "Error creating table $DEST_TABLE."
    exit 1
fi

if $MYSQL_CMD "$SQL_INSERT_DATA"; then
    echo "Table $DEST_TABLE populated with averages, minimum, and maximum values."
else
    echo "Error populating table $DEST_TABLE."
    exit 1
fi
