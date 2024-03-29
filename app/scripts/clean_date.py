#! /usr/bin/env python3

import argparse
import glob
import json
import os
import sys

data_folder = os.path.join(os.path.dirname(__file__), os.pardir, 'data')


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('date', help='Date to remove, format YYYY-MM-DD HH:MM')
    args = parser.parse_args()
    date = args.date

    stats_filename = os.path.join(data_folder, 'statistics.json')
    print('Reading existing data')
    with open(stats_filename) as f:
        stats = json.load(f)

    if date not in stats:
        print('Date is not available in the file. Available dates:')
        for day in stats:
            print(' - {}'.format(day))
        sys.exit(1)

    # Remove the data
    print('Removing selected date')
    del stats[date]

    # Store data
    print('Storing updated data to file')
    stats_filename = os.path.join(data_folder, 'statistics.json')
    with open(stats_filename, 'w') as f:
        f.write(json.dumps(stats, sort_keys=True))

    # Remove existing cache files
    print('Removing cache files')
    cache_folder = os.path.join(os.path.dirname(
        __file__), os.pardir, os.pardir, 'cache')
    file_list = glob.glob(os.path.join(cache_folder, '*.cache'))
    for file_name in file_list:
        try:
            os.remove(os.path.abspath(file_name))
        except:
            print('Error removing file: {}'.format(file_name))


if __name__ == '__main__':
    main()
