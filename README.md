# Meadow Monitor

Meadow Monitor is fully autonomous, one time setup system that allows the user to take care of their plants completely remotely. Meadow Monitor provides a web portal as well as a mobile application for the user to input their plant information as well as monitor their plant health. Meadow Monitor can detect the light,temperature, and moisture level of the plant it is connected to and can provide adequate heat and water as needed. Meadow Monitor takes advantage of the Treflio.io API to make sure that the plant is getting the best care with the most up to date information there is.

### To deploy on MAMP -

To deploy these files to MAMP, copy common.php and config.php from /private_files/ to 

```
/Applications/MAMP/
```
Copy /public_html/favicon.ico to 
```
/Applications/MAMP/bin/
```
Copy all other /public_html/ files and folders to
```
/Applications/MAMP/htdocs/
```

### To deploy with Docker -

Make sure to have Docker and docker-compose installed on the current device or EC2 instance. Clone repo from git, navigate to the base directory /meadowmonitor/ and run:
```
sudo dockerd
sudo service docker start
```
```
docker-compose up
```

The wesite will be accessible on the host address at port 8080.

### To Initialize Database -

To initialize the MySQL database, first turn on the Apache and MySQL servers by pressing the "Start Servers" button on the MAMP window. 

The primary difference here is that we're initializing the tables using SQLAlchemy in Python; see `server/tables.py` for the schema.
```py
# run:
import os

from server import conf
from server import tables

conf.trefle_token = os.environ['COEN315_TREFLE_TOKEN']  # or whatever
# create the database and tables
tables.create_meadowmonitor_database(user=conf.user, password=conf.password, host=conf.host, port=conf.port,
                                     database=conf.database)
# fill out the tables with test data if desired
tables.create_test_data()
```

### To Deploy to ESP32 -

Install the SPIFFS uploader: https://github.com/me-no-dev/arduino-esp32fs-plugin/releases/
