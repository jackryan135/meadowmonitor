from server import conf
from server.tables import new_session, Devices


def history(device_id: str, rows: int):
    # TODO: update for table chances
    #  return the most recent n rows for the given device
    pass


def plant(device_id: str):
    session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)
    device = session.query(Devices).filter_by(id=device_id).one_or_none()  # type: Devices
    return device.plant.plantName


def change_plant(device_id: str, species: bytes):
    # TODO: update for table chances
    species = species.decode('utf-8')  # unfortunate that this is required with plaintext parameters :(
    print(device_id, species)
    return "ok"


def list_devices(user_id: str):
    session = new_session(conf.user, conf.password, conf.host, conf.port, conf.database)
    devices = session.query(Devices).filter_by(ownerID=user_id).all()
    devices = [
        {'user_id': device.ownerID, 'plant_type': device.plant.plantName} for device in devices
    ]
    return devices
