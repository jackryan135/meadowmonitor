import os

from server import conf
from server import tables

# create the database and tables
tables.create_meadowmonitor_database(user=conf.user, password=conf.password, host=conf.host, port=conf.port,
                                     database=conf.database)
# fill out the tables with test data if desired
tables.create_test_data()