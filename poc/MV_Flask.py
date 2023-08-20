import mysql.connector
from flask import Flask, render_template
import plotly
import plotly.graph_objs as go
import json

app = Flask(__name__)

class TemperatureData:
    def __init__(self, db_config):
        self.db = mysql.connector.connect(**db_config)
        self.cursor = self.db.cursor()

    def get_data(self):
        query = "SELECT WC_Date, WC_TempLow, WC_TempHigh, WC_TempAvg FROM DayWeatherConditions"
        self.cursor.execute(query)
        data = self.cursor.fetchall()
        return data

    def close(self):
        self.cursor.close()
        self.db.close()

@app.route('/')
def index():
    db_config = {
        'user': 'admin',
        'password': 'xxxxxxxx',
        'host': '192.168.17.10',
        'database': 'VillebonWeatherReport'
    }
    temp_data = TemperatureData(db_config)
    data = temp_data.get_data()
    temp_data.close()

    dates = [row[0] for row in data]
    min_temps = [row[1] for row in data]
    max_temps = [row[2] for row in data]
    avg_temps = [row[3] for row in data]

    trace1 = go.Scatter(x=dates, y=min_temps, name='Min Temps')
    trace2 = go.Scatter(x=dates, y=max_temps, name='Max Temps')
    trace3 = go.Scatter(x=dates, y=avg_temps, name='Avg Temps')
    data = [trace1, trace2, trace3]
    graphJSON = json.dumps(data, cls=plotly.utils.PlotlyJSONEncoder)

    return render_template('index.html', graphJSON=graphJSON)

if __name__ == '__main__':
    app.run()
