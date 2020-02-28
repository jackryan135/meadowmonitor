from server import conf
from server.tables import new_session, Devices, Data, Users


def history(device_id: str, rows: int = 5):
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


def plant(device_id: str):
    session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)
    device = session.query(Devices).filter_by(id=device_id).one_or_none()  # type: Devices
    if device is None:
        return 'Device not found', 404
    return device.plant.plantName


def change_plant(device_id: str, species: bytes):
    # TODO: update for table changes
    # TODO: create a 'update device plant' method, probably in tables
    # TODO: obv needs to throw a 404 if the device doesn't exist, etc
    species = species.decode('utf-8')  # unfortunate that this is required with plaintext parameters :(
    print(device_id, species)

    # return "OK"
    return None, 501


def list_devices(user_id: str):
    session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)
    devices = session.query(Devices).filter_by(ownerID=user_id).all()
    if len(devices) == 0 or devices is None:
        user = session.query(Users).filter_by(id=user_id).one_or_none()
        if user is None:
            return "User not found", 404
        return None, 204
    devices = [
        {'device_id': device.id, 'plant_type': device.plant.plantName} for device in devices
    ]
    return devices
