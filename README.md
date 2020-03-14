# Meadow Monitor

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

To initialize the MySQL database, first turn on the Apache and MySQL servers by pressing the "Start Servers" button on the MAMP window. ~~Then, visit the URL [localhost:8080/install.php](localhost:8080/install.php)~~

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
