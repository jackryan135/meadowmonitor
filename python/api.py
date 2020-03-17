import connexion
from flask import render_template
from flask_cors import CORS

from server import conf, tables
from server.tables import new_session, init_plants

app = connexion.App(__name__, specification_dir='./')
app.add_api('server/swagger.yml')
CORS(app.app)

# NOTE: We don't actually want an API homepage. If you're testing, use /api/ui/ for endpoint info.
# @app.route('/')
# def index():
#     return render_template('home.html')


if __name__ == '__main__':
    tables.create_meadowmonitor_database(user=conf.user, password=conf.password, host=conf.host, port=conf.port,
                                         database=conf.database)
    sesh = new_session(user=conf.user, password=conf.password, host=conf.host, port=conf.port,
                       database=conf.database)
    if sesh.query(tables.Plants).filter_by(id=-1).one_or_none() is None:
        init_plants()
    if conf.populate_database is True:
        # Only used when testing
        tables.create_test_data()
    app.run(host='0.0.0.0', port=5000, debug=True, use_reloader=False)
