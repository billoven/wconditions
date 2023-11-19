import unittest
import argparse
from datetime import datetime, timedelta
from unittest.mock import MagicMock
from unittest.mock import patch
from wc_daysWhereCondition import WeatherAnalyzer
from io import StringIO  # Importez StringIO pour capturer la sortie
import sys

class TestWeatherAnalyzer(unittest.TestCase):
    def setUp(self):
        self.analyzer = WeatherAnalyzer("test_config.json")

    def test_read_config(self):
        config = self.analyzer.read_config("test_config.json")
        self.assertEqual(config["database_host"], "test_host")
        self.assertEqual(config["database_user"], "test_user")
        self.assertEqual(config["database_password"], "test_password")
        self.assertEqual(config["database_name"], "test_db")
        self.assertEqual(config["table_name"], "test_table")

    def test_generate_condition_description(self):
        conditions = "WC_TempAvg >= 10, WC_TempAvg <= 25, WC_PrecipitationSum >= 5"
        description = self.analyzer.generate_condition_description(conditions)
        self.assertEqual(description, "Conditions set: WC_TempAvg >= 10, WC_TempAvg <= 25, WC_PrecipitationSum >= 5")

    @patch('pymysql.connect')
    def test_count_days_with_conditions(self, mock_connect):
        connection = mock_connect.return_value
        cursor = connection.cursor.return_value
        cursor.fetchall.return_value = [{"WC_Date": datetime(2023, 1, 1)}, {"WC_Date": datetime(2023, 1, 2)}]

        start_date = datetime(2023, 1, 1)
        end_date = datetime(2023, 1, 2)
        conditions = "WC_TempAvg >= 10, WC_TempAvg <= 25, WC_PrecipitationSum >= 5"
        min_consecutive = 2

        consecutive_periods = self.analyzer.count_days_with_conditions(connection, start_date, end_date, conditions, min_consecutive)
        self.assertEqual(len(consecutive_periods), 1)
        self.assertEqual(consecutive_periods[0]['start_date'], datetime(2023, 1, 1))
        self.assertEqual(consecutive_periods[0]['end_date'], datetime(2023, 1, 2))
        self.assertEqual(consecutive_periods[0]['consecutive_count'], 2)

    @patch('pymysql.connect')
    def test_analyze_with_consecutive_periods(self, mock_connect):
        connection = mock_connect.return_value
        cursor = connection.cursor.return_value
        cursor.fetchall.return_value = [
            {"WC_Date": datetime(2023, 1, 1)},
            {"WC_Date": datetime(2023, 1, 2)},
            {"WC_Date": datetime(2023, 1, 3)},
            {"WC_Date": datetime(2023, 1, 5)}
        ]

        start_date = "2023-01-01"
        end_date = "2023-01-10"
        conditions = "WC_TempAvg >= 10, WC_TempAvg <= 25, WC_PrecipitationSum >= 5"
        min_consecutive = 3

        args = argparse.Namespace(
            conditions=conditions,
            start_date=start_date,
            end_date=end_date,
            min_consecutive=min_consecutive
        )

        with patch('sys.stdout', new_callable=StringIO) as mock_print:
            self.analyzer.analyze(args)
            output = mock_print.getvalue()
            
        # Supprimez la date et l'heure de la sortie (si nécessaire) pour l'adapter à la sortie réelle.
        cleaned_output = output.replace("Start Date: 2023-01-01 00:00:00, End Date: 2023-01-03 00:00:00, Consecutive Days: 3", "Start Date: 2023-01-01, End Date: 2023-01-03, Consecutive Days: 3")

        # Comparez la sortie nettoyée avec la chaîne attendue.
        expected_output = (
            "Summary of Consecutive Days with Conditions:\n"
            "Total consecutive days with conditions: 3\n"
            "Conditions set: WC_TempAvg >= 10, WC_TempAvg <= 25, WC_PrecipitationSum >= 5\n"
            "Consecutive Periods (sorted from greatest to lowest):\n"
            "Start Date: 2023-01-01, End Date: 2023-01-03, Consecutive Days: 3\n"
        )
        self.assertEqual(cleaned_output, expected_output)

    @patch('pymysql.connect')
    def test_analyze_no_consecutive_periods(self, mock_connect):
        connection = mock_connect.return_value
        cursor = connection.cursor.return_value
        cursor.fetchall.return_value = [
            {"WC_Date": datetime(2023, 1, 1)},
            {"WC_Date": datetime(2023, 1, 3)},
            {"WC_Date": datetime(2023, 1, 5)}
        ]

        start_date = "2023-01-01"
        end_date = "2023-01-10"
        conditions = "WC_TempAvg >= 10, WC_TempAvg <= 25, WC_PrecipitationSum >= 5"
        min_consecutive = 3

        # Define a mock args object
        args = argparse.Namespace(
            conditions=conditions,
            start_date=start_date,
            end_date=end_date,
            min_consecutive=min_consecutive
        )

        with patch('builtins.print') as mock_print:
            self.analyzer.analyze(args)
            mock_print.assert_called_with("No consecutive days with conditions found.")

if __name__ == '__main__':
    unittest.main()

 
