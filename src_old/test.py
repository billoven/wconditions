import argparse

parser = argparse.ArgumentParser()

parser = argparse.ArgumentParser(
    prog='PROG',
    description='''this description
    was indented weird
    but that is okay''',
    epilog='''
    likewise for this epilog whose whitespace will
    be cleaned up and whose words will be wrapped
    across a couple lines''')


group = parser.add_mutually_exclusive_group()
group.add_argument("-v", "--verbose", action="store_true")
group.add_argument("-q", "--quiet", action="store_true")

parser.add_argument("x", type=int, help="the base")
parser.add_argument("y", type=int, help="the exponent")

args = parser.parse_args()
answer = args.x**args.y
if args.quiet:
    print(answer)
elif args.verbose:
    print("{} to the power {} equals {}".format(args.x, args.y, answer))
else:
    print("{}^{} == {}".format(args.x, args.y, answer))
