import json
import sys
import argparse
import subprocess

from PIL import Image

def args_parse():
    parser = argparse.ArgumentParser()
    parser.add_argument("--file-name", type=str, default="Hello")
    args = parser.parse_args()
    return args

def saveFileWithArgs():
    args = args_parse()
    print(args.file_name)

def saveFile(fileName, info):
    commend = "conda activate pytorch && python C:/xampp/htdocs/API/eating/python/t2.py"

    p = subprocess.Popen(commend, shell=True)
    p.wait()
    print(p.returncode)
    print("Success Save")

if __name__ == "__main__":
    fileName = sys.argv[1]
    info = sys.argv[2]
    saveFile(fileName=fileName, info=info)