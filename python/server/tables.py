import datetime
import pprint
import random
from typing import List, Tuple

import sqlalchemy
from sqlalchemy import Column, Integer, String, Float, DateTime
from sqlalchemy import ForeignKey
from sqlalchemy import create_engine, MetaData, Table
from sqlalchemy.engine import Engine
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import mapper, sessionmaker
from sqlalchemy.orm import relationship
from sqlalchemy_utils import database_exists, create_database

from server import conf, trefle

Base = declarative_base()


class Users(Base):
    __tablename__ = 'users'
    id = Column(Integer, primary_key=True, autoincrement=True)
    firstname = Column(String(30))
    lastname = Column(String(30))
    email = Column(String(50))
    password = Column(String(255))
    date = Column(DateTime, server_default=sqlalchemy.func.now())

    devices = relationship('Devices', back_populates='user')

    def __repr__(self):
        return (
            f'<Users: id={self.id}, firstname={self.firstname}, lastname={self.lastname}, email={self.email}, '
            f'date={self.date}>'
        )


class Devices(Base):
    __tablename__ = 'devices'
    id = Column(Integer, primary_key=True, autoincrement=True)
    ownerID = Column(Integer, ForeignKey('users.id'), nullable=False)  # foreign key -> users
    label = Column(String(255))
    idealPlantID = Column(Integer, ForeignKey('plants.id'))
    idealPH = Column(Float)
    idealTemp = Column(Float)
    idealLight = Column(String(20))
    idealMoisture = Column(String(20))
    date = Column(DateTime, server_default=sqlalchemy.func.now())

    user = relationship('Users', back_populates='devices')
    plant = relationship('Plants')

    def __repr__(self):
        return (
            f'<Devices: id={self.id}, label={self.label}, ownerID={self.ownerID}, idealPlantID={self.idealPlantID}, '
            f'idealPH={self.idealPH}, idealTemp={self.idealTemp}, idealLight={self.idealLight}, '
            f'idealMoisture={self.idealMoisture}, date={self.date}>'
        )


class Data(Base):
    __tablename__ = 'data'
    id = Column(Integer, primary_key=True, autoincrement=True)
    deviceID = Column(Integer, ForeignKey('devices.id'), nullable=False)  # foreign key -> devices
    plantID = Column(Integer, ForeignKey('plants.id'))
    ph = Column(Float)
    temp = Column(Float)
    light = Column(Float)
    moisture = Column(Float)
    date = Column(DateTime, server_default=sqlalchemy.func.now())

    plant = relationship('Plants')

    def __repr__(self):
        return (
            f'<Data: id={self.id}, plantID={self.plantID}, ph={self.ph}, temp={self.temp}, light={self.light}, '
            f'moisture={self.moisture}, date={self.date}>'
        )

    def json(self):
        return {
            'device_id': self.deviceID,
            'date': self.date,
            'species': self.plant.plantName,
            'light': self.light,
            'moisture': self.moisture,
            'ph': self.ph,
            'temp': self.temp,
        }


class Plants(Base):
    __tablename__ = 'plants'
    id = Column(Integer, primary_key=True)
    plantName = Column(String(100))

    def __repr__(self):
        return f'<Plants: id={self.id}, plantName={self.plantName}>'


def _mysql_engine(user: str, password: str, host: str, port: int, database: str, pool_recycle: int = 3600) -> Engine:
    return create_engine(f'mysql+mysqlconnector://{user}:{password}@{host}:{port}/{database}',
                         pool_recycle=pool_recycle)


def new_session(user: str, password: str, host: str, port: int, database: str) -> sqlalchemy.orm.session.Session:
    engine = _mysql_engine(user, password, host, port, database)
    Session = sessionmaker(bind=engine)
    session = Session()
    return session


# # # Testing / data population functions # # #

def create_meadowmonitor_database(user: str, password: str, host: str, port: int, database: str):
    engine = _mysql_engine(user, password, host, port, database)
    print("*******engine created*******")
    if not database_exists(engine.url):
        create_database(engine.url)
        print("*******database created*******")
    Base.metadata.create_all(bind=engine)


def init_plants():
    sesh = new_session(user=conf.user, password=conf.password, host=conf.host, port=conf.port,
                       database=conf.database)
    sentinel = Plants(id=-1, plantName="None")
    sesh.add(sentinel)
    sesh.commit()


# # # Not used in production # # #

def create_test_data():
    sesh = new_session(user=conf.user, password=conf.password, host=conf.host, port=conf.port,
                       database=conf.database)

    users = [
        ("Jeff", "Bridges", "jbridges@test.com", datetime.datetime.utcnow()),
        ("Jennifer", "Anniston", "janniston@test.com", datetime.datetime.utcnow()),
        ("Brad", "Pitt", "bpitt@test.com", datetime.datetime.utcnow()),
        ("Oprah", "Winfrey", "owinfrey@test.com", datetime.datetime.utcnow()),
        ("Zach", "Efron", "zefron@test.com", datetime.datetime.utcnow()),
    ]  # type: List[Tuple]

    user_rows = []
    for user in users:
        # user_row = Users(firstname=user[0], lastname=user[1], email=user[2], date=user[3])  # don't need this if timestamps autopopulate
        user_row = Users(firstname=user[0], lastname=user[1], email=user[2])
        user_rows.append(user_row)
        print("*******User Row Added*******")
    sesh.add_all(user_rows)
    sesh.commit()
    print("**********Users added*********")

    plants = [
        (169882, "western swordfern"),
        (175949, "Puerto Rico royal palm"),
        (175874, "Virginia rose"),
        (114322, "sagebrush mariposa lily"),
        (141504, "common sunflower"),
        (160569, "Barbary fig"),
    ]  # type: List[Tuple]
    plant_rows = []
    for plant in plants:
        plant_row = Plants(id=plant[0], plantName=plant[1])
        plant_rows.append(plant_row)
        print("*******Plant Row Added*******")
    sesh.add_all(plant_rows)
    sesh.commit()
    print("*******Plants added*******")

    users = sesh.query(Users).all()  # type: List[Users]
    users.append(users[0])  # add jeff bridges again
    device_rows = []
    levels = ['HIGH', 'MEDIUM', 'LOW', 'TOLERANT']
    for user, plant in zip(users, plants):
        print("*******Before Trefle called*******")
        pprint.pprint(trefle.get_species(plant[0]).json())
        plant_data = trefle.get_species(plant[0]).json()['growth']
        print("*******JSON parsed*******")
        device_row = Devices(
            ownerID=user.id,
            idealPlantID=plant[0],
            idealPH=(plant_data['ph_minimum'] + plant_data['ph_maximum']) / 2,
            idealTemp=plant_data['temperature_minimum']['deg_f'],
            # idealLight=random.randrange(300, 999),
            idealLight=random.choice(levels),
            # these should both be maps of str -> float, i don't want to deal with it right now.
            # idealMoisture=random.randrange(300, 999),
            idealMoisture=random.choice(levels),
            # these should both be maps of str -> float, i don't want to deal with it right now.
            # date=datetime.datetime.utcnow()  # don't need this if timestamps autopopulate
        )
        device_rows.append(device_row)
        print("*******Device Row Added*******")
        sesh.add_all(device_rows)
        sesh.commit()
        print("******Devices added*******")

        devices = sesh.query(Devices).all()  # type: List[Devices]
        plants = sesh.query(Plants).all()  # type: List[Plants]
        data_rows = []
        for device, another_plant in zip(devices, plants):
            date = datetime.datetime.utcnow()
        ph = 7
        temp = 65
        light = 30
        moisture = 42
        for i in range(10):
            data = Data(
                deviceID=device.id,
                plantID=plant.id,
                ph=ph,
                temp=temp,
                light=light,
                moisture=moisture,
                date=date,
            )
        data_rows.append(data)
        print("*******Data Row Added*******")
        date += datetime.timedelta(minutes=60)
        ph += random.random()
        temp += (random.random() - 0.5) * 8
        light += (random.random() - 0.5) * 4
        moisture += (random.random() - 0.5) * 4
        sesh.add_all(data_rows)
        sesh.commit()
        print("*******Data added*******")
