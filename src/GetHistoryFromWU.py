#!/usr/bin/env python

import csv

{
  "summaries": [
    {
      "stationID": "KMAHANOV10",
      "tz": "America/New_York",
      "obsTimeUtc": "2016-09-28T03:59:54Z",
      "obsTimeLocal": "2016-09-27 23:59:54",
      "epoch": 1475035194,
      "lat": 42.09263229,
      "lon": -70.86485291,
      "solarRadiationHigh": null,
      "uvHigh": null,
      "winddirAvg": 0,
      "humidityHigh": 97,
      "humidityLow": 77,
      "humidityAvg": 88,
      "metric": {
        "tempHigh": 21,
        "tempLow": 11,
        "tempAvg": 17,
        "windspeedHigh": 0,
        "windspeedLow": 0,
        "windspeedAvg": 0,
        "windgustHigh": 0,
        "windgustLow": 0,
        "windgustAvg": 0,
        "dewptHigh": 18,
        "dewptLow": 9,
        "dewptAvg": 14,
        "windchillHigh": null,
        "windchillLow": null,
        "windchillAvg": null,
        "heatindexHigh": 21,
        "heatindexLow": 12,
        "heatindexAvg": 17,
        "pressureMax": null,
        "pressureMin": null,
        "pressureTrend": null,
        "precipRate": 0,
        "precipTotal": 10.41
      }
    },
 // Response Collapsed for Presentation Purposes
  ]
}


employee_parsed = json.loads(employee_data)

emp_data = employee_parsed['employee_details']

# open a file for writing

employ_data = open('/tmp/EmployData.csv', 'w')

# create the csv writer object

csvwriter = csv.writer(employ_data)

count = 0

for emp in emp_data:

      if count == 0:

             header = emp.keys()

             csvwriter.writerow(header)

             count += 1

      csvwriter.writerow(emp.values())

employ_data.close()
