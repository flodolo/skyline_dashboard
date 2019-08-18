#! /usr/bin/env python3

import json
import os

data_folder = os.path.join(os.path.dirname(__file__), os.pardir, 'data')


def main():
    stats_filename = os.path.join(data_folder, 'statistics.json')
    print('Reading existing data')
    with open(stats_filename) as f:
        stats = json.load(f)

    locales_filename = os.path.join(data_folder, 'locales.json')
    with open(locales_filename) as f:
        all_locales = json.load(f)

    products = [
        'fenix',
        'fennec',
        'firefox',
        'fxa',
        'fxios',
        'lockwiseandroid',
        'lockwiseios',
        'monitor',
        'mozillaorg'
    ]

    for day, day_data in list(stats.items()):
        for product, product_data in list(day_data.items()):
            if product not in products:
                print("Removed {} in {}".format(product, day))
                del stats[day][product]
                continue
            if product not in all_locales:
                continue
            for locale in list(product_data):
                if locale not in all_locales[product]:
                    print("Removed {} in {} for {}".format(
                            locale, product, day))
                    del stats[day][product][locale]

    # Store data
    stats_filename = os.path.join(data_folder, 'statistics2.json')
    with open(stats_filename, 'w') as f:
        f.write(json.dumps(stats, sort_keys=True))


if __name__ == '__main__':
    main()
