import json
from tabulate import tabulate
import sys

def flatten_dict(d, parent_key='', sep='_'):
    """
    Flatten a nested dictionary.

    Parameters:
    - d (dict): Nested dictionary to be flattened.
    - parent_key (str): Parent key for recursion.
    - sep (str): Separator between keys.

    Returns:
    - dict: Flattened dictionary.
    """
    items = []
    for k, v in d.items():
        new_key = f"{parent_key}{sep}{k}" if parent_key else k
        if isinstance(v, dict):
            items.extend(flatten_dict(v, new_key, sep=sep).items())
        else:
            items.append((new_key, v))
    return dict(items)

def display_stats(stats):
    """
    Display the climate statistics in a formatted table.

    Parameters:
    - stats (dict): Dictionary containing climate statistics.
    """
    flat_stats = flatten_dict(stats)
    
    data = []
    headers = ['Metric', 'Value']

    for key, value in flat_stats.items():
        data.append([key, value])

    print(tabulate(data, headers=headers, tablefmt='grid'))

def main(json_file):
    with open(json_file, 'r') as file:
        stats_dict = json.load(file)
        display_stats(stats_dict)

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python script.py <json_file>")
        sys.exit(1)

    json_file_path = sys.argv[1]
    main(json_file_path)
