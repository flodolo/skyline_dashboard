#! /usr/bin/env python3

from configparser import ConfigParser
from urllib.parse import quote as urlquote
from urllib.request import urlopen
import datetime
import glob
import json
import os
import sys

# Read config file
data_folder = os.path.join(os.path.dirname(__file__), os.pardir, 'data')
config_file = os.path.join(data_folder, os.pardir, 'config', 'config.ini')
if not os.path.isfile(config_file):
    print('Configuration file /app/config/config.ini is missing')
    sys.exit(1)
else:
    config_parser = ConfigParser()
    config_parser.read(config_file)
    try:
        tmx_folder = os.path.join(config_parser.get('config', 'tmx_path'), '')
    except Exception as e:
        print('tmx_path not found in config.ini')
        sys.exit(1)
    if not os.path.exists(tmx_folder):
        print('Path to TMX is not valid')
        sys.exit(1)


def extract_gecko_data(list_file, locales, data):
    list_file = os.path.join(data_folder, list_file)
    with open(list_file) as f:
        string_list = json.load(f)

    string_ids = []
    for filename, ids in string_list.items():
        for id in ids:
            string_ids.append('{}:{}'.format(filename, id))

    total_strings = len(string_ids)
    for locale in locales:
        tmx_path = os.path.join(tmx_folder, locale,
                                'cache_{}_gecko_strings.json'.format(locale))
        with open(tmx_path) as f:
            locale_data = json.load(f)

        missing_strings = 0
        for id in string_ids:
            if id not in locale_data:
                missing_strings += 1

        if total_strings == 0:
            completion = 0
        else:
            completion = round(
                float(total_strings - missing_strings) * 100 / total_strings, 2)
        data[locale] = {
            'missing': missing_strings,
            'total': total_strings,
            'completion': completion,
        }


def get_android_ids(product_folder):
    tmx_path = os.path.join(
        tmx_folder, 'en-US', 'cache_en-US_android_l10n.json')
    with open(tmx_path) as f:
        data = json.load(f)
    string_ids = []

    product_folder = '/{}/'.format(product_folder)
    for id in data:
        if product_folder in id:
            string_ids.append(id)
    string_ids.sort()

    return string_ids


def extract_android_data(product, locales, data):
    string_ids = get_android_ids(product)
    total_strings = len(string_ids)
    for locale in locales:
        tmx_path = os.path.join(tmx_folder, locale,
                                'cache_{}_android_l10n.json'.format(locale))
        with open(tmx_path) as f:
            locale_data = json.load(f)

        missing_strings = 0
        for id in string_ids:
            if id not in locale_data:
                missing_strings += 1

        if total_strings == 0:
            completion = 0
        else:
            completion = round(
                float(total_strings - missing_strings) * 100 / total_strings, 2)
        data[locale] = {
            'missing': missing_strings,
            'total': total_strings,
            'completion': completion,
        }


def main():
    date_key = datetime.datetime.utcnow().strftime('%Y-%m-%d %H:%M')
    stats_filename = os.path.join(data_folder, 'statistics.json')
    print('Reading existing data')
    if os.path.exists(stats_filename):
        with open(stats_filename) as f:
            stats = json.load(f)
    else:
        stats = {}

    stats[date_key] = {}

    # Read supported locales
    print('Reading supported locales')
    locales_filename = os.path.join(data_folder, 'locales.json')
    with open(locales_filename) as f:
        all_locales = json.load(f)

    # Read Firefox data
    print('Extracting Transvision data')
    stats[date_key]['firefox'] = {}
    extract_gecko_data(
        'string_list_desktop.json', all_locales['firefox'],
        stats[date_key]['firefox'])

    # Read Fennec data
    stats[date_key]['fennec'] = {}
    extract_gecko_data(
        'string_list_mobile.json', all_locales['fennec'],
        stats[date_key]['fennec'])

    # Read android-l10n data
    for product in ['android-components', 'fenix', 'firefox-tv', 'lockwise-android']:
        stats[date_key][product] = {}
        extract_android_data(
            product, all_locales[product], stats[date_key][product])

    # Read data from Pontoon
    print('Extracting Pontoon data')
    query = '''
{
  fxa: project(slug: "firefox-accounts") {
    ...allLocales
  }
  fxios: project(slug: "firefox-for-ios") {
    ...allLocales
  }
  lockwiseios: project(slug: "lockwise-ios") {
    ...allLocales
  }
  monitor: project(slug: "firefox-monitor-website") {
    ...allLocales
  }
}

fragment allLocales on Project {
  localizations {
    locale {
      code
    }
    totalStrings
    missingStrings
  }
}
'''

    try:
        url = 'https://pontoon.mozilla.org/graphql?query={}'.format(
            urlquote(query))
        response = urlopen(url)
        json_data = json.load(response)
        for project, project_data in json_data['data'].items():
            stats[date_key][project] = {}
            for element in project_data['localizations']:
                locale = element['locale']['code']
                completion = round(
                    float(element['totalStrings'] - element['missingStrings']) * 100 / element['totalStrings'], 2)
                stats[date_key][project][locale] = {
                    'missing': element['missingStrings'],
                    'total': element['totalStrings'],
                    'completion': completion,
                }
    except Exception as e:
        print(e)

    # Store data
    print('Storing data in JSON files')
    with open(stats_filename, 'w') as f:
        f.write(json.dumps(stats, sort_keys=True))

    # Remove existing cache files
    print('Removing cache files')
    cache_folder = os.path.join(os.path.dirname(__file__), os.pardir, os.pardir, 'cache')
    file_list = glob.glob(os.path.join(cache_folder, '*.cache'))
    for file_name in file_list:
        try:
            os.remove(os.path.abspath(file_name))
        except:
            print('Error removing file: {}'.format(file_name))


if __name__ == '__main__':
    main()
