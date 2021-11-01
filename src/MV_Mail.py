import smtplib
from email.message import EmailMessage


class Mail:

        def __init__(self):

                self.server = smtplib.SMTP("smtp.gmail.com",587)

        def __del__(self):

                self.server.quit()

        def send(self, email, subject, body):

                msg = EmailMessage()
                msg["Subject"] = subject
                msg["From"] = email
                msg["To"] = email
                msg.set_content(body)
                self.server.send_message(msg)
