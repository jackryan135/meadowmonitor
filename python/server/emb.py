import datetime

from server import conf, trefle
from server.tables import new_session, Devices, Data
import pprint
from typing import Any, Dict


def desired(device_id: int):
    session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)
    device = session.query(Devices).filter_by(id=device_id).one_or_none()  # type: Devices
    if device is None:
        return 'Device not found', 404
    desired_values = {
        'moisture': device.idealMoisture,
        'light': device.idealLight,
        'temperature_min': device.idealTemp,
        'ph': device.idealPH,
    }
    return desired_values


def log(device_id: int, values: Dict[str, Any]):
    session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)

    device = session.query(Devices).filter_by(id=device_id).one_or_none()
    if device is None:
        return 'Device not found', 404

    data_row = Data(
        deviceID=device.id,
        plantID=device.idealPlantID,
        ph=values['ph'],
        temp=values['temp'],
        light=values['light'],
        moisture=values['moisture'],
        date=datetime.datetime.utcnow(),
    )
    session.add(data_row)
    session.commit()

    return data_row.id, 201
