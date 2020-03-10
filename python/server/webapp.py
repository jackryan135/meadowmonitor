from typing import Dict, Any

from server import conf
from server.tables import new_session, Devices, Data, Users
from server.trefle import search_species_complete


def history(device_id: int, rows: int = 5):
    # TODO: update for table chances
    #  return the most recent n rows for the given device
    session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)
    # incomplete, but we can filter by daterange if desired:
    # session.query(Data).filter_by(deviceID=device_id).filter(func.DATE(Data.date) > delta)
    data_rows = session.query(Data).filter_by(deviceID=device_id).order_by(Data.date.desc()).all()
    if len(data_rows) == 0 or data_rows is None:
        device = session.query(Devices).filter_by(id=device_id).one_or_none()
        if device is None:
            return "Device not found", 404
        return None, 204
    output = [data.json() for data in data_rows[:rows]]
    return output


def plant(device_id: int):
    session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)
    device = session.query(Devices).filter_by(id=device_id).one_or_none()  # type: Devices
    if device is None:
        return 'Device not found', 404
    if device.plant is None:
        return None
    return device.plant.plantName


def change_plant(device_id: int, species: bytes):
    # TODO: update for table changes
    # TODO: create a 'update device plant' method, probably in tables
    # TODO: obv needs to throw a 404 if the device doesn't exist, etc
    species = species.decode('utf-8')  # unfortunate that this is required with plaintext parameters :(
    print(device_id, species)

    # return "OK"
    return None, 501


def list_devices(user_id: int):
    session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)
    devices = session.query(Devices).filter_by(ownerID=user_id).all()
    if len(devices) == 0 or devices is None:
        user = session.query(Users).filter_by(id=user_id).one_or_none()
        if user is None:
            return "User not found", 404
        return None, 204
    devices = [
        {
            'device_id': device.id,
            'plant_type': device.plant.plantName if device.plant is not None else None,
            'label': device.label,
        } for device in devices
    ]
    return devices


def add_device(user_id: int, values: Dict[str, Any]):
    session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)
    if 'label' in values:
        device = Devices(ownerID=user_id, label=values['label'])
    else:
        device = Devices(ownerID=user_id)
    session.add(device)
    session.commit()
    return device.id, 201


def search(search_term: str):
    search_results = search_species_complete(search_term)
    plant_results = [{
        'id': result['id'],
        'scientific_name': result['scientific_name'],
        'common_name': result['common_name'],
        'complete_data': result['complete_data'],
    } for result in search_results]
    return plant_results
