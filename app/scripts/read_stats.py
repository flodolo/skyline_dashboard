#! /usr/bin/env python3

from urllib.parse import quote as urlquote
from urllib.request import urlopen
import datetime
import json
import os

tmx_folder = '/srv/transvision/data/TMX/'


def extract_data(list_file, locales, data):

    list_file = os.path.join(
        os.path.dirname(__file__), os.pardir, 'data', list_file)
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

        completion = round(
            float(total_strings - missing_strings) * 100 / total_strings, 2)
        data[locale] = {
          'missing': missing_strings,
          'total': total_strings,
          'completion': completion,
        }


def main():

    date_key = datetime.datetime.utcnow().strftime('%Y-%m-%d %H:%M')
    stats_filename = os.path.join(
        os.path.dirname(__file__), os.pardir, 'data', 'statistics.json')
    if os.path.exists(stats_filename):
        with open(stats_filename) as f:
            stats = json.load(f)
    else:
        stats = {}

    stats[date_key] = {}

    # Read desktop data
    locales = [
        'ach', 'af', 'an', 'ar', 'ast', 'az', 'be', 'bg', 'bn', 'br', 'bs',
        'ca', 'cak', 'cs', 'cy', 'da', 'de', 'dsb', 'el', 'en-CA', 'en-GB',
        'eo', 'es-AR', 'es-CL', 'es-ES', 'es-MX', 'et', 'eu', 'fa', 'ff', 'fi',
        'fr', 'fy-NL', 'ga-IE', 'gd', 'gl', 'gn', 'gu-IN', 'he', 'hi-IN', 'hr',
        'hsb', 'hu', 'hy-AM', 'ia', 'id', 'is', 'it', 'ja', 'ka', 'kab', 'kk',
        'km', 'kn', 'ko', 'lij', 'lt', 'lv', 'mk', 'mr', 'ms', 'my', 'nb-NO',
        'ne-NP', 'nl', 'nn-NO', 'oc', 'pa-IN', 'pl', 'pt-BR', 'pt-PT', 'rm',
        'ro', 'ru', 'si', 'sk', 'sl', 'son', 'sq', 'sr', 'sv-SE', 'ta', 'te',
        'th', 'tr', 'uk', 'ur', 'uz', 'vi', 'xh', 'zh-CN', 'zh-TW',
    ]
    stats[date_key]['firefox'] = {}
    extract_data('string_list_desktop.json',
                 locales, stats[date_key]['firefox'])

    # Read Fennec data
    locales = [
        'an', 'ar', 'ast', 'az', 'be', 'bg', 'bn', 'br', 'bs', 'ca', 'cak',
        'cs', 'cy', 'da', 'de', 'dsb', 'el', 'en-CA', 'en-GB', 'eo', 'es-AR',
        'es-CL', 'es-ES', 'es-MX', 'et', 'eu', 'fa', 'ff', 'fi', 'fr', 'fy-NL',
        'ga-IE', 'gd', 'gl', 'gn', 'gu-IN', 'he', 'hi-IN', 'hr', 'hsb', 'hu',
        'hy-AM', 'id', 'is', 'it', 'ja', 'ka', 'kab', 'kk', 'kn', 'ko', 'lij',
        'lo', 'lt', 'lv', 'ml', 'mr', 'ms', 'my', 'nb-NO', 'ne-NP', 'nl',
        'nn-NO', 'oc', 'pa-IN', 'pl', 'pt-BR', 'pt-PT', 'rm', 'ro', 'ru', 'sk',
        'sl', 'son', 'sq', 'sr', 'sv-SE', 'ta', 'te', 'th', 'tr', 'trs', 'uk',
        'ur', 'uz', 'vi', 'wo', 'xh', 'zam', 'zh-CN', 'zh-TW',
    ]
    stats[date_key]['fennec'] = {}
    extract_data('string_list_mobile.json',
                 locales, stats[date_key]['fennec'])

    # Read data from Pontoon

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
  mozillaorg: project(slug: "mozillaorg") {
    ...allLocales
  }
  androidl10n: project(slug: "android-l10n") {
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
    with open(stats_filename, 'w') as f:
        f.write(json.dumps(stats, sort_keys=True))


if __name__ == '__main__':
    main()
