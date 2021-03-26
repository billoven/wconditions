import pytest
from src.MV_UpdateDB import *

def test_DateYYYYMMDD():
	assert DateYYYYMMDD(datetime(2021, 3, 13)) == '2021/03/13'	
	

def test_valid_date_type():
	assert valid_date_type('20210203') == datetime(2021, 2, 3)
	
def test_valid_date_type_exception():
    """test that exception is raised for invalid date type"""
    with pytest.raises(Exception):
        assert valid_date_type('22210xxx')
