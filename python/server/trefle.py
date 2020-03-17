import pprint
from typing import Dict, Any, List

import requests

from server import conf

TREFLE_API = 'https://trefle.io/api/'


# SUGGESTION (Erik): If you're unfamiliar with the Python requests module, use response.json() to get
#  the content of a successful request. I don't always do this myself because we may need to be able to
#  check status codes of failed requests.


def get_desired(species_id: int) -> Dict[str, Any]:
    response = requests.get(url=TREFLE_API + f'species/{species_id}', params={'token': conf.trefle_token})
    response.raise_for_status()
    complete = response.json()
    growth = complete['growth']
    desired = {
        'moisture': growth['moisture_use'],
        'light': growth['shade_tolerance'],
        'temperature_min': growth['temperature_minimum']['deg_f'],
        # There is no temperature max on Trefle at this time.
        # 'temperature_max': growth['temperature_maximum']['deg_f'],
        'ph_min': growth['ph_minimum'],
        'ph_max': growth['ph_maximum'],
    }
    return desired


def search_species_complete(search_term: str) -> List[Dict]:
    # Get all species matching a partial search term
    # Ex: 'straw' will return both 'European bedstraw' and 'Appalachian barren strawberry'
    ret = []
    page = 1
    species_set = set()
    while page < 10 and len(ret) < 5:
        print(page)
        complete = requests.get(url=TREFLE_API + 'species',
                                params={'token': conf.trefle_token, 'q': search_term, 'page': page}).json()
        if len(complete) == 0:
            break
        for species in complete:
            if species['complete_data'] is True and species['common_name'] not in species_set:
                ret.append(species)
                species_set.add(species['common_name'])
                print(species['common_name'])
        page += 1
    return ret


def get_species(species_id=None) -> requests.Response:
    # TODO: update to return as json
    if species_id is None:
        # get all species
        # note that this returns a page at a time of 30 species per page, so this may be undesirable...
        resp = requests.get(url=TREFLE_API + 'species', params={'token': conf.trefle_token})
    else:
        resp = requests.get(url=TREFLE_API + f'species/{species_id}', params={'token': conf.trefle_token})
    resp.raise_for_status()
    return resp
