import pytest
from src.MV_GenStats import *


def test_valid_date_type():
	assert valid_date_type('20210203') == datetime(2021, 2, 3)
	
def test_valid_date_type_exception():
    """test that exception is raised for invalid date type"""
    with pytest.raises(Exception):
        assert valid_date_type('22210xxx')

def test_getArgs(self):
        parsed = self.parser.getArgs(['-ED', '2023-01-01'])
        self.assertEqual(parsed.something, 'test')