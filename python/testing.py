import json
import sys
import argparse

def args_parse():
    parser = argparse.ArgumentParser()
    parser.add_argument("--file-name", type=str, default="Hello")
    args = parser.parse_args()
    return args

def saveFileWithArgs():
    args = args_parse()
    print(args.file_name)

def saveFile(fileName, info):
    with open(fileName + ".json", "w") as f:
        json.dump(info, f)
    print("Success Save")

if __name__ == "__main__":
    fileName = sys.argv[1]
    info = sys.argv[2]
    saveFile(fileName=fileName, info=info)