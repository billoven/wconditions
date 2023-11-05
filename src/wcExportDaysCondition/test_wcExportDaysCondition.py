#!/usr/bin/env python3
import unittest
import filecmp
from unittest.mock import Mock, mock_open, patch
from wcExportDaysCondition import DataExporter, CSVExporter

class TestCSVExporter(unittest.TestCase):
    def setUp(self):
        self.data = [
            {'Date': '01/01/2023', 'WC_TempHigh': 10.0, 'WC_TempAvg': 8.0, 'WC_TempLow': 6.0},
            {'Date': '02/01/2023', 'WC_TempHigh': 12.0, 'WC_TempAvg': 9.0, 'WC_TempLow': 7.0}
        ]
        self.output_file = 'test.csv'
        self.csv_exporter = CSVExporter(self.data, self.output_file)
        self.expected_file = 'expected.csv'  # Specify the expected CSV file

    @patch('builtins.open', new_callable=mock_open)
    @patch('csv.writer', autospec=True)
    def test_export_to_csv(self, mock_csv_writer, mock_open):
        csv_writer = mock_csv_writer()
        
        with patch('builtins.open', mock_open) as mock_file:
            self.csv_exporter.export_to_csv()

        # Check that 'open' is called with the expected arguments
        mock_file.assert_called_once_with(self.output_file, 'w', newline='')

        # Modify the expected data format to match the source code's format
        expected_data = [
            {'Date', 'WC_TempHigh', 'WC_TempAvg', 'WC_TempLow'},
            {'Date': '01/01/2023', 'WC_TempHigh': 10.0, 'WC_TempAvg': 8.0, 'WC_TempLow': 6.0},
            {'Date': '02/01/2023', 'WC_TempHigh': 12.0, 'WC_TempAvg': 9.0, 'WC_TempLow': 7.0}
        ]

        csv_writer.writerow.assert_called_with(['Date', 'WC_TempHigh', 'WC_TempAvg', 'WC_TempLow'])
        csv_writer.writerows.assert_called_with([row.values() for row in expected_data])

        # Compare the generated CSV file with the expected CSV file
        is_identical = filecmp.cmp(self.output_file, self.expected_file)
        self.assertTrue(is_identical)

if __name__ == '__main__':
    unittest.main()

