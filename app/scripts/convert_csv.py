#! /usr/bin/env python3

import argparse
import csv
import json
import os
import sys

data_folder = os.path.join(os.path.dirname(__file__), os.pardir, 'data')


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('csv_file', help='Path to CSV file to conver to JSON')
    args = parser.parse_args()
    csv_filename = args.csv_file

    if not os.path.exists(csv_filename):
        print('File does not exist')
        sys.exit(1)

    json_filename = os.path.join(data_folder, 'delivery.json')
    json_data = {}

    with open(csv_filename) as csv_file:
        csv_reader = csv.reader(csv_file, delimiter=',')
        line_count = 0
        for row in csv_reader:
            if line_count == 0:
                line_count +=1
                continue

            project = row[3].replace('\n', '')
            if project not in json_data:
                json_data[project] = {}
            if row[0] != '':
                # Estimated data
                row_type = 'estimated'
                date = row[0]
                wc = row[7]
            else:
                # Actual date
                row_type = 'actual'
                date = row[1]
                wc = row[8]

            # Ignore empty word count
            if wc == '':
                continue

            if row_type not in json_data[project]:
                json_data[project][row_type] = {}
            json_data[project][row_type][date] = wc

    # Remove empty projects
    empty_projects = []
    for project_name, project_data in json_data.items():
        if project_data == {}:
            empty_projects.append(project_name)
    for name in empty_projects:
        del json_data[name]


    with open(json_filename, 'w') as json_file:
        json_file.write(json.dumps(json_data, indent=2, sort_keys=True))


if __name__ == '__main__':
    main()
