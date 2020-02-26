from server import conf, trefle
from server.tables import new_session, Devices
import pprint
from typing import Any, Dict


def desired(device_id: str):
    session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)
    device = session.query(Devices).filter_by(id=device_id).one_or_none()  # type: Devices
    desired_values = trefle.get_desired(device.idealPlantID)
    return desired_values


def log(device_id: str, values: Dict[str, Any]):
    # TODO: update to work with new db schema
    # session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)
    session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)
    # entry = Data(
    #     date=datetime.utcnow(),
    #     deviceID=device_id,
    #     light=values['light'],
    #     moisture=values['moisture'],
    #     ph=values['ph'],
    #     plantSpecies="test",  # eventually map this to the Devices table
    #     temp=values['temp'],
    # )

    # entry = Data()
    # entry.date=datetime.utcnow(),
    # entry.deviceID = device_id,
    # entry.light = values['light'],
    # entry.moisture = values['moisture'],
    # entry.ph = values['ph'],
    # entry.plantSpecies = "test",  # eventually map this to the Devices table
    # entry.temp = values['temp'],

    session.execute(
        "insert into DATA("
        "   deviceID, plantSpecies, ph, temp, light, moisture"
        ") VALUES ("
        f"  {device_id}, \"{'strawberry'}\", {values['ph']}, {values['temp']}, {values['light']}, {values['moisture']}"
        ")"
    )
    session.commit()

    # for attr in vars(entry):
    #     if attr[0] != '_':
    #         print(attr, entry.__getattribute__(attr))
    # session.add(entry)
    # session.commit()
    pprint.pprint(f'logged for device {device_id}: {values}')
    return "nice"
