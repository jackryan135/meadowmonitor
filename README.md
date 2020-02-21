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

Make sure to have Docker installed on the current device. Then, navigate to the base directory /meadowmonitor/ and run:
```
docker-compose up
```

### To Initialize Database -

To initialize the MySQL database, first turn on the Apache and MySQL servers by pressing the "Start Servers" button on the MAMP window. Then, visit the URL [localhost:8080/install.php](localhost:8080/install.php)
