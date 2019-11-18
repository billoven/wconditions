import argparse
import test

parser = argparse.ArgumentParser(parents=[test.parser])

parser.add_argument('--local-arg', action="store_true", default=False)

print parser.parse_args()
