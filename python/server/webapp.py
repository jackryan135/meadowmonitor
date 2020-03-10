import pprint
from typing import Dict, Any

from server import conf
from server.tables import new_session, Devices, Data, Users, Plants
from server.trefle import search_species_complete, get_desired, get_species


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


def get_plant(device_id: int):
    session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)
    device = session.query(Devices).filter_by(id=device_id).one_or_none()  # type: Devices
    if device is None:
        return 'Device not found', 404
    if device.plant is None:
        return None
    return device.plant.plantName


def change_plant(device_id: int, species_id: int):
    # # TODO: update for table changes
    # # TODO: create a 'update device plant' method, probably in tables
    # # TODO: obv needs to throw a 404 if the device doesn't exist, etc
    # species = species.decode('utf-8')  # unfortunate that this is required with plaintext parameters :(
    # print(device_id, species)
    #
    # # return "OK"
    # return None, 501
    session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)
    device = session.query(Devices).filter_by(id=device_id).one_or_none()  # type: Devices

    plant = session.query(Plants).filter_by(id=species_id).one_or_none()
    if plant is None:
        species_data = get_species(species_id)
        if species_data['complete_data'] is not True:
            return "No plant data found", 404
        plant = Plants(id=species_id, plantName=species_data['common_name'])
        session.add(plant)
        session.commit()

    desired = get_desired(species_id)
    pprint.pprint(desired)

    # update species and not other things
    device.idealPlantID = species_id
    device.idealMoisture = desired['moisture'].upper()
    device.idealLight = desired['light'].upper()
    device.idealTemp = desired['temperature_min']
    # this isn't really used since we couldn't find a sensor for it
    device.idealPH = (desired['ph_max'] + desired['ph_min']) / 2

    session.commit()
    return "OK", 200


def override_values(device_id: int, values: Dict[str, Any]):
    session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)
    device = session.query(Devices).filter_by(id=device_id).one_or_none()  # type: Devices
    if device is None:
        return "No matching device", 404
    if 'temperature' in values:
        # override ideal temperature
        device.idealTemp = values['temperature']
    if 'moisture' in values:
        # override ideal moisture
        device.idealMoisture = values['moisture'].upper()
    session.commit()

    if 'temperature' not in values and 'moisture' not in values:
        return "Empty request body", 204
    return "OK", 200


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
