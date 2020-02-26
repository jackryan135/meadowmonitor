import os

import connexion
from flask import render_template

from server import conf, tables

app = connexion.App(__name__, specification_dir='./')
app.add_api('swagger.yml')


@app.route('/')
def index():
    return render_template('home.html')


if __name__ == '__main__':
    if conf.trefle_token is None:
        conf.trefle_token = os.environ['COEN315_TREFLE_TOKEN']
    # map_session(user=conf.user, password=conf.password, host=conf.host, port=conf.port, database=conf.database)  # not used if we're creating schema in sqlalchemy
    tables.create_meadowmonitor_database(user=conf.user, password=conf.password, host=conf.host, port=conf.port,
                                         database=conf.database)
    app.run(host='0.0.0.0', port=5000, debug=True)



