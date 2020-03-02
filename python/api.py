import os

import connexion
from flask import render_template

from server import conf, tables

app = connexion.App(__name__, specification_dir='./')
app.add_api('server/swagger.yml')


# TODO: make a new one, but http://localhost:5000/api/ui/ is a better homepage anyways.
# @app.route('/')
# def index():
#     return render_template('home.html')


if __name__ == '__main__':
    if conf.trefle_token is None or conf.trefle_token == '':
        # TODO: probably move to conf.py?
        conf.trefle_token = os.environ['COEN315_TREFLE_TOKEN']
    tables.create_meadowmonitor_database(user=conf.user, password=conf.password, host=conf.host, port=conf.port,
                                         database=conf.database)
    if conf.populate_database is True:
        tables.create_test_data()
    app.run(host='0.0.0.0', port=5000, debug=True, use_reloader=False)



