FROM python:3

COPY pip-requirements.txt / 

RUN pip install -r pip-requirements.txt

ADD ./ python/
WORKDIR /python
ENV PATH=$PATH:/python
ENV PYTHONPATH /python

CMD ["python", "api.py"]