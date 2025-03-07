#!/usr/bin/env python3
import os
import re
import base64
import pymysql
from pymysql.cursors import DictCursor
import json
from typing import Optional, List, Dict, Any
from datetime import datetime, timedelta
from email.mime.text import MIMEText

# Google OAuth2 libraries for Gmail API
from google.oauth2.credentials import Credentials
from google_auth_oauthlib.flow import InstalledAppFlow
from google.auth.transport.requests import Request
from googleapiclient.discovery import build

# Define the scope required for sending emails via Gmail API.
SCOPES = ['https://www.googleapis.com/auth/gmail.send']

class WeatherAlert:
    def __init__(self, db_config_file, alert_config_file):
        # Load the database configuration for weather stations.
        self.db_configs = self.load_config(db_config_file)
        self.load_alert_config(alert_config_file)
        # Dictionary to keep track of the last sent time for each alert-station combination.
        self.alert_last_sent = {}

    def load_config(self, config_file):
        """
        Load and return the JSON configuration from the given file.
        """
        with open(config_file, 'r') as file:
            return json.load(file)
    
    def load_alert_config(self, alert_config_file):
        with open(alert_config_file, 'r') as file:
            config = json.load(file)
        
        self.alerts = [alert for alert in config.get("alerts", []) if alert.get("enabled", False)]
    
    
    def connect_db(self, db_config):
        """
        Connect to a MySQL database using the provided database configuration via pymysql.
        Returns a database connection object with a dictionary cursor.
        """
        return pymysql.connect(
            host=db_config['host'],
            user=db_config['username'],
            password=db_config['password'],
            database=db_config['database'],
            cursorclass=DictCursor
        )
           
   
    def is_cooldown_active(self, alert_name: str, station_key: str, cooldown_period: int, connection) -> bool:
        """
        Checks if an alert is still in the cooldown period.

        :param alert_name: Name of the alert.
        :param station_key: Weather station key.
        :param cooldown_period: Cooldown duration in minutes.
        :return: True if the cooldown is active, False otherwise.
        """
        now = datetime.now()

        try:
            with connection.cursor() as cursor:
                query = """
                    SELECT last_sent 
                    FROM WeatherAlerts 
                    WHERE station_key = %s AND alert_name = %s
                    ORDER BY last_sent DESC
                    LIMIT 1
                """
                cursor.execute(query, (station_key, alert_name))
                result = cursor.fetchone()

                if result:
                    last_sent_time = result["last_sent"]
                    if now - last_sent_time < timedelta(minutes=cooldown_period):
                        return True  # Cooldown is still active

                return False  # Cooldown expired or no alert exists
        except pymysql.MySQLError as err:
            print(f"Database error: {err}")
            return False  # Default to cooldown expired in case of an error

    def register_alert(self, alert_name: str, station_key: str, cooldown_period: int, connection):
        """
        Registers a new alert after verifying the cooldown status.

        :param alert_name: Name of the alert.
        :param station_key: Weather station key.
        :param cooldown_period: Cooldown duration in minutes.
        """
        try:
            with connection.cursor() as cursor:
                update_query = """
                    INSERT INTO WeatherAlerts (alert_name, station_key, last_sent, cooldown_minutes)
                    VALUES (%s, %s, NOW(), %s)
                    ON DUPLICATE KEY UPDATE last_sent = VALUES(last_sent)
                """
                cursor.execute(update_query, (alert_name, station_key, cooldown_period))
                connection.commit()
                print(f"✅ Alert '{alert_name}' registered for station {station_key}.")
        except pymysql.MySQLError as err:
            print(f"Database error: {err}")  
   

    def parse_time_ago(self,time_ago):
        """Convert time_ago string (e.g., '1d', '2h', '30m') into (value, SQL unit)."""
        match = re.match(r"(\d+)([dhm])", time_ago.strip())
        if not match:
            raise ValueError(f"Invalid time_ago format: {time_ago}")
        
        value, unit = int(match.group(1)), match.group(2)
        
        if unit == "d":
            return value, "DAY"    # Return SQL-compatible time unit
        elif unit == "h":
            return value, "HOUR"
        elif unit == "m":
            return value, "MINUTE"
        else:
            raise ValueError(f"Unsupported time_ago unit (m, h, d authorized): {unit}")
       
    def check_alert(
        self,
        alert: Dict[str, Any],  # Remplacez les arguments par un seul paramètre 'alert'
        station_key: str,
        db_config: Optional[Dict[str, Any]] = None
    ) -> Optional[Dict[str, Any]]:
   
        """Checks if an alert should be triggered based on the alert configuration."""
        
        # Accéder aux valeurs du dictionnaire alert
        alert_name = alert["name"]
        cooldown_period = alert["cooldown"]
        alert_cooldown = alert["cooldown"]
        weather_field = alert["weather_field"]
        weather_unit = alert["weather_unit"]
        alert_type = alert["alert_type"]
        threshold = alert["threshold"]
        time_ago = alert["time_ago"]
        exclude_months = alert.get("exclude_months", [])
        exclude_hours = alert.get("exclude_hours", (None, None))
        
        try:
            # Connect to the station's database
            connection = self.connect_db(db_config)
            with connection.cursor() as cursor:
                
                # Check if the alert can be reactivated
                if not self.is_cooldown_active(alert_name, station_key, alert_cooldown, connection):          
                
                    now = datetime.now()
                    
                    # Get latest value
                    query_last = f"""
                    SELECT WC_Datetime AS last_datetime, {weather_field} AS last_value
                    FROM WeatherConditions
                    ORDER BY WC_Datetime DESC
                    LIMIT 1;
                    """
                    cursor.execute(query_last)
                    last_record = cursor.fetchone()

                    if not last_record:
                        return None  # No data available
                    
                    last_value = last_record["last_value"]
                    last_datetime = last_record["last_datetime"]

                    # Handle "no_update" alert type
                    if alert_type == "no_update":
                        # Convert threshold from minutes to a timedelta
                        time_threshold = timedelta(minutes=threshold)
                        time_since_last_update = now - last_datetime

                        if time_since_last_update >= time_threshold:
                            self.register_alert(alert_name, station_key, cooldown_period,connection)  # Register the alert
                            return {
                                "now": now,
                                "last_datetime": last_datetime,
                                "alert_reason": f"No data update in the last {threshold}{weather_unit}"
                            }
                        return None  # No alert triggered

                    # Case when time_ago is 0 minutes (check latest value directly)
                    time_ago_value, time_ago_unit = self.parse_time_ago(time_ago)

                    if time_ago_value == 0:  # Equivalent to "hours_ago == 0"
                        if (alert_type == "increase" and last_value >= threshold) or \
                        (alert_type == "decrease" and last_value <= threshold):
                            self.register_alert(alert_name, station_key, cooldown_period,connection)  # Register the alert
                            return {
                                "now": now,
                                "last_datetime": last_datetime,
                                "last_value": last_value,
                                "alert_reason": f"Threshold crossed: {weather_field} {alert_type} {threshold}{weather_unit}"
                            }
                        return None  # No alert triggered

                    query_past = f"""
                    SELECT WC_Datetime AS past_datetime, {weather_field} AS past_value
                    FROM WeatherConditions
                    WHERE WC_Datetime <= NOW() - INTERVAL %s {time_ago_unit}
                    """

                    query_params = [time_ago_value]  # Use parsed time value

                    # Handle excluded hours
                    if exclude_hours[0] is not None and exclude_hours[1] is not None:
                        query_past += " AND HOUR(WC_Datetime) NOT BETWEEN %s AND %s"
                        query_params.extend(exclude_hours)

                    # Handle excluded months
                    if exclude_months:
                        placeholders = ",".join(["%s"] * len(exclude_months))
                        query_past += f" AND MONTH(WC_Datetime) NOT IN ({placeholders})"
                        query_params.extend(exclude_months)

                    query_past += " ORDER BY WC_Datetime DESC LIMIT 1;"
                    
                    # Execute query with dynamic parameters
                    cursor.execute(query_past, query_params)
                    past_record = cursor.fetchone()

                    if not past_record:
                        return None  # No past data available

                    past_value = past_record["past_value"]
                    past_datetime = past_record["past_datetime"]
                    variation = last_value - past_value

                    if (alert_type == "increase" and variation >= threshold) or \
                    (alert_type == "decrease" and variation <= -threshold):
                        
                        alert_reason = f"Threshold crossed: {weather_field} {alert_type} {threshold}"
                        self.register_alert(alert_name, station_key, cooldown_period,connection)  # Register the alert
                        return {
                            "now": now,
                            "last_datetime": last_datetime,
                            "last_value": last_value,
                            "past_datetime": past_datetime,
                            "past_value": past_value,
                            "variation": variation,
                            "alert_reason": f"Variation crossed: {weather_field} {alert_type} of {threshold}{weather_unit} in the last {time_ago}"
                        }
                else:
                    print(f"Alert {alert_name} for {db_config['weatherStation']} is in cooldown, no reactivation for now.")
        except pymysql.MySQLError as err:
            print(f"MySQL Error: {err}")
        finally:
            connection.close()  # Ensure the connection is properly closed

    def run_alert_checks(self):

        """ Runs all enabled alerts defined in the JSON config file. """
        for alert in self.alerts:

            # Retrieve the list of station keys (e.g., ["db1", "db2"]).
            station_keys = alert.get('stations', [])
            if isinstance(station_keys, str):
                station_keys = [station_keys]

            # Retrieve the list of station keys (e.g., ["db1", "db2"]).
            station_keys = alert.get('stations', [])
            if isinstance(station_keys, str):
                station_keys = [station_keys]

            for station_key in station_keys:
                
                # Get the database configuration for the weather station.
                db_config = self.db_configs['dbConfigs'].get(station_key)
                if not db_config:
                    print(f"Warning: No database configuration found for station {station_key}")
                    continue

                result = self.check_alert(
                    alert=alert,       # Passez l'alerte complète
                    station_key=station_key,
                    db_config=db_config
                )

                if result:
                    timestamp = result.get("now") or datetime.now()
                    print(f"🚨 {timestamp} ({db_config['weatherStation']}) - {alert['name']} Alert!")
                    print(f"    alert['message'")
                    print(f"    Details: {result}")
                    
                    # Send an email notification if the alert condition is met
                    self.send_email(alert, result, db_config, station_key)
                else:
                    timestamp = datetime.now()
                    print(f"✅ {timestamp} ({db_config['weatherStation']}) - {alert['name']} - No alert triggered.")

    def get_gmail_service(self):
        """
        Obtain a Gmail API service using OAuth2 credentials.
        This function checks for an existing token in token.json and uses it if valid.
        Otherwise, it initiates the OAuth flow using the credentials.json file.
        """
        creds = None
        # token.json stores the user's access and refresh tokens.
        if os.path.exists('token.json'):
            creds = Credentials.from_authorized_user_file('token.json', SCOPES)
        # If there are no valid credentials available, let the user log in.
        if not creds or not creds.valid:
            if creds and creds.expired and creds.refresh_token:
                creds.refresh(Request())
            else:
                flow = InstalledAppFlow.from_client_secrets_file('/etc/wconditions/credentials.json', SCOPES)
                creds = flow.run_local_server(port=0)
            # Save the credentials for the next run.
            with open('token.json', 'w') as token:
                token.write(creds.to_json())
        # Build and return the Gmail service.
        service = build('gmail', 'v1', credentials=creds)
        return service

    def send_email(self, alert, data, db_config, station_key):
        """
        Send an email notification for the triggered alert using the Gmail API.
        The email includes details about the alert and the retrieved data.
        The subject line includes the alert name and the weather station name.
        Subject: ⚠️ Weather Alert: {alert_name} at {station_name}   
        Dear Weather Monitoring Team,

        A weather alert has been triggered for {station_name} based on real-time weather data.

        🌦️ Alert Details
        🔹 Alert Name: {alert_name}
        🔹 Type: {type}
        🔹 Threshold: {threshold}
        🔹 Current Value: {current_value}
        🔹 Triggered At: {timestamp}

        📢 Message: {alert["message"]}

        📌 Additional Information
        ✅ This alert was raised based on live weather conditions.
        ⏳ A new alert can be triggered again after a cooldown period of {cooldown} minutes.

        ⚠️ Please review the conditions and take necessary actions if required.

        Best regards,
        Villebon Weather Alert System
        """       
        # Extract alert and weather station details
        station_name = db_config.get('weatherStation', station_key)
        alert_name = alert["name"]
        type = alert["alert_type"]
        threshold = alert["threshold"]
        cooldown = alert["cooldown"]
        weather_unit = alert["weather_unit"]
        
        message_template = alert['message']
        # Remplacer {threshold} par la valeur réelle
        alert_message = message_template.format(threshold=threshold)
        lastdatetime = data.get("last_datetime", "Unknown Time")
        recipients = alert["recipients"]  # List of recipient emails
        current_value = data.get("last_value", "N/A")
        timestamp = data.get("now", "Unknown Time")

        alert_reason = data.get("alert_reason", "Unknown Reason")

        sender_email = "villebonweather@gmail.com"  # The sender email address (must match the OAuth2 account)
        # Construct email subject
        subject = f"⚠️ Weather Alert: {alert_name} at {station_name}"
       
        # Construct email body
        body_text = f"""
        Dear Weather Monitoring Team,

        A weather alert has been triggered for {station_name} based on real-time weather data.

        🌦️ Alert Details:

        🔹 Alert Name: {alert_name}
        🔹 Type: {type}
        🔹 Threshold: {threshold}{weather_unit}
        🔹 Current Value: {current_value}{weather_unit}
        🔹 More recent value update time: {lastdatetime} 
        🔹 Triggered At: {timestamp}
        🔹 Reason: {alert_reason}

        📢 Message: {alert_message}   

        📌 Additional Information:

        ✅ This alert was raised based on live weather conditions.
        ⏳ A new alert can be triggered again after a cooldown period of {cooldown} minute(s).

        ⚠️ Please review the conditions and take necessary actions if required.

        Best regards,
        Villebon Weather Alert System
        """
        
        # Create a MIMEText message.
        message = MIMEText(body_text)
        message['to'] = ", ".join(recipients)
        message['from'] = sender_email
        message['subject'] = subject
        
        # Encode the message in base64.
        raw_message = base64.urlsafe_b64encode(message.as_bytes()).decode()
        body = {'raw': raw_message}
        
        # Get the Gmail API service using OAuth2 credentials.
        service = self.get_gmail_service()
        try:
            # Send the email via the Gmail API.
            sent_message = service.users().messages().send(userId="me", body=body).execute()
            print(f"Alert sent: {alert['name']} for station {station_name}. Message ID: {sent_message['id']}")
        except Exception as e:
            print(f"An error occurred while sending email for alert '{alert['name']}': {e}")

# Example usage
if __name__ == "__main__":
    db_config_file = "/etc/wconditions/db_config.json"
    alert_config_file = "WeatherAlertConf.json"
    
    weather_alert = WeatherAlert(db_config_file, alert_config_file)
    weather_alert.run_alert_checks()