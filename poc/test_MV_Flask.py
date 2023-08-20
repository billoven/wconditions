import unittest
from unittest.mock import MagicMock, patch
import mysql.connector
import datetime
from decimal import Decimal
from flask import Flask
from MV_Flask import TemperatureData, index

class TestTemperatureData(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        # Set up a mock database connection for testing
        cls.mock_db = MagicMock(spec=mysql.connector.MySQLConnection)
        cls.mock_cursor = MagicMock(spec=mysql.connector.cursor.MySQLCursor)
        cls.mock_db.cursor.return_value = cls.mock_cursor

    def test_get_data(self):
        # Mock the fetchall method a5.4, 23.8, 19.4nd return test data
        test_data = [(datetime.date(2023, 7, 1), Decimal('15.4'), Decimal('23.8'), Decimal('19.4')), 
                     (datetime.date(2023, 7, 2), Decimal('12.7'), Decimal('24.3'), Decimal('18.7')),
                     (datetime.date(2023, 7, 3), Decimal('15.7'), Decimal('24.5'), Decimal('19.6')),
                     (datetime.date(2023, 7, 4), Decimal('12.7'), Decimal('24.4'), Decimal('17.9')),
                     (datetime.date(2023, 7, 5), Decimal('11.1'), Decimal('21.6'), Decimal('16.6')),
                     (datetime.date(2023, 7, 6), Decimal('11.1'), Decimal('26.0'), Decimal('19.5')),
                     (datetime.date(2023, 7, 7), Decimal('12.7'), Decimal('30.3'), Decimal('22.5'))]
        self.mock_cursor.fetchall.return_value = test_data

        temp_data = TemperatureData({'user': 'admin', 'password': 'Z0uZ0u0!', 'host': '192.168.17.10', 'database': 'TestVillebonWeatherReport'})
        result = temp_data.get_data()

        self.assertEqual(result, test_data)
        self.mock_cursor.execute.assert_called_once_with("SELECT WC_Date, WC_TempLow, WC_TempHigh, WC_TempAvg FROM DayWeatherConditions")

    def test_close(self):
        temp_data = TemperatureData({'user': 'admin', 'password': 'Z0uZ0u0!', 'host': '192.168.17.10', 'database': 'TestVillebonWeatherReport'})
        temp_data.close()
        self.mock_cursor.close.assert_called_once()
        self.mock_db.close.assert_called_once()

class TestFlaskApp(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        # Create a test Flask app
        cls.app = Flask(__name__)
        cls.app.config['TESTING'] = True
        cls.app.config['SERVER_NAME'] = "127.0.0.1:5000"

    @patch('MV_Flask.render_template')
    @patch('MV_Flask.json.dumps')
    @patch('MV_Flask.TemperatureData')
    def test_index(self, mock_temp_data, mock_json_dumps, mock_render_template):
        # Mock dependencies and data
        mock_temp_data_instance = MagicMock(spec=TemperatureData)
        mock_temp_data.return_value = mock_temp_data_instance
        test_data = [(datetime.date(2023, 7, 1), Decimal('15.4'), Decimal('23.8'), Decimal('19.4')), 
                     (datetime.date(2023, 7, 2), Decimal('12.7'), Decimal('24.3'), Decimal('18.7')),
                     (datetime.date(2023, 7, 3), Decimal('15.7'), Decimal('24.5'), Decimal('19.6')),
                     (datetime.date(2023, 7, 4), Decimal('12.7'), Decimal('24.4'), Decimal('17.9')),
                     (datetime.date(2023, 7, 5), Decimal('11.1'), Decimal('21.6'), Decimal('16.6')),
                     (datetime.date(2023, 7, 6), Decimal('11.1'), Decimal('26.0'), Decimal('19.5')),
                     (datetime.date(2023, 7, 7), Decimal('12.7'), Decimal('30.3'), Decimal('22.5'))]
        mock_temp_data_instance.get_data.return_value = test_data
        mock_json_dumps.return_value = '[{"name": "Min Temps"}, {"name": "Max Temps"}, {"name": "Avg Temps"}]'

        print(self.app.config)
        print(self.app.config['TESTING'])

        # Make a test request to the index route
        with self.app.test_client() as client:
            response = client.get('/')

        print("Response Status Code:", response.status_code)

        # Assert the expected behavior
        self.assertEqual(response.status_code, 200)
        mock_temp_data.assert_called_once_with({'user': 'admin', 'password': 'Z0uZ0u0!', 'host': '192.168.17.10', 'database': 'TestVillebonWeatherReport'})
        mock_temp_data_instance.get_data.assert_called_once()
        mock_temp_data_instance.close.assert_called_once()
        mock_json_dumps.assert_called_once_with([{'name': 'Min Temps'}, {'name': 'Max Temps'}, {'name': 'Avg Temps'}], cls=MagicMock)
        mock_render_template.assert_called_once_with('index.html', graphJSON='[{"name": "Min Temps"}, {"name": "Max Temps"}, {"name": "Avg Temps"}]')

if __name__ == '__main__':
    unittest.main()
