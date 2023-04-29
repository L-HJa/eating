import os
import sys
sys.stderr = sys.stdout
import shutil
import cv2
from YoloXTrainDataHelper import main as transferToCoco
import subprocess
import requests
import json
# from Train.train import main as yoloxTrain

def main(uid, storageRoot):
    # savePid(uid=uid)
    changeFileName(uid=uid, storageRoot=storageRoot)
    createTrainClassesTxt(uid=uid, storageRoot=storageRoot)
    createTrainTxt(uid=uid, storageRoot=storageRoot)
    sourcePath = os.path.join(storageRoot, uid, "ObjectDetection")
    transferToCoco(source_path=sourcePath)
    trainObjectDetectionModel(uid=uid, storageRoot=storageRoot)
    deleteTrain(uid=uid)
    print("Success")

# 將正在訓練的狀態刪除
def deleteTrain(uid):
    databaseUrl = "http://120.126.151.186/API/eating/model-weight/delete-train-status"
    data = {
        "uid": uid
    }
    json_data = json.dumps(data)
    _ = requests.post(databaseUrl, data=json_data)


# 更新檔案名稱
def changeFileName(uid, storageRoot):
    imagesFolder = os.path.join(storageRoot, uid, "ObjectDetection", "imgs")
    annotationsFolder = os.path.join(storageRoot, uid, "ObjectDetection", "annotations")

    newImagesFolder = os.path.join(storageRoot, uid, "ObjectDetection", "new_imgs")
    newAnnotationsFolder = os.path.join(storageRoot, uid, "ObjectDetection", "new_annotations")
    if os.path.exists(newImagesFolder):
         shutil.rmtree(newImagesFolder)
    if os.path.exists(newAnnotationsFolder):
         shutil.rmtree(newAnnotationsFolder)
    os.mkdir(newImagesFolder)
    os.mkdir(newAnnotationsFolder)

    imagesName = os.listdir(imagesFolder)
    for idx, imageName in enumerate(imagesName):
        annotationName = os.path.splitext(imageName)[0] + ".txt"
        imagePath = os.path.join(imagesFolder, imageName)
        annotationPath = os.path.join(annotationsFolder, annotationName)

        newImagePath = os.path.join(newImagesFolder, str(idx) + ".jpg")
        newAnnotationPath = os.path.join(newAnnotationsFolder, str(idx) + ".txt")

        shutil.copyfile(imagePath, newImagePath)
        shutil.copyfile(annotationPath, newAnnotationPath)

def createTrainClassesTxt(uid, storageRoot):
    classesInfo = "Donburi\nSoupRice\nRice\nCountable\nSoupNoodle\nNoodle\nSideDish\nSolidSoup\nSoup"
    classesFilePath = os.path.join(storageRoot, uid, "ObjectDetection", "classes.txt")
    with open(classesFilePath, "w") as f:
        f.write(classesInfo)

def createTrainTxt(uid, storageRoot):
    imageFolder = os.path.join(storageRoot, uid, "ObjectDetection", "new_imgs")
    annoFolder = os.path.join(storageRoot, uid, "ObjectDetection", "new_annotations")
    savePath = os.path.join(storageRoot, uid, "ObjectDetection", "2012_train.txt")
    supportImage = ['.jpg', '.JPG', '.jpeg', '.JPEG']
    imgsName = [imgName for imgName in os.listdir(imageFolder) if os.path.splitext(imgName)[1] in supportImage]
    annosName = [annoName for annoName in os.listdir(annoFolder) if os.path.splitext(annoName)[1] == '.txt']
    imgsName = sorted(imgsName)
    annosName = sorted(annosName)
    with open(savePath, 'w') as f:
        pass
    for img_name, anno_name in zip(imgsName, annosName):
        if os.path.splitext(img_name)[0] != os.path.splitext(anno_name)[0]:
            assert ValueError
        imgPath = os.path.join(imageFolder, img_name)
        annoPath = os.path.join(annoFolder, anno_name)
        img = cv2.imread(imgPath)
        imgHeight, imgWidth = img.shape[:2]
        with open(annoPath, 'r') as f:
            annos = f.readlines()
        targets = list()
        for anno in annos:
            label, centerX, centerY, w, h = anno.strip().split(' ')
            centerX = (float(centerX) * imgWidth)
            centerY = (float(centerY) * imgHeight)
            w = (float(w) * imgWidth)
            h = (float(h) * imgHeight)
            xmin = int(centerX - w / 2)
            ymin = int(centerY - h / 2)
            xmax = int(centerX + w / 2)
            ymax = int(centerY + h / 2)
            res = str(xmin) + ',' + str(ymin) + ',' + str(xmax) + ',' + str(ymax) + ',' + label
            targets.append(res)
        annotation = imgPath + ' ' + ' '.join(targets)
        with open(savePath, 'a') as f:
            f.write(annotation)
            f.write('\n')

def trainObjectDetectionModel(uid, storageRoot):
    classesPath = os.path.join(storageRoot, uid, "ObjectDetection", "classes.txt")
    trainAnnotationPath = os.path.join(storageRoot, uid, "ObjectDetection", "2012_train.txt")
    cocoJsonFile = os.path.join(storageRoot, uid, "ObjectDetection", "self_annotation.json")
    savePath = os.path.join(storageRoot, uid, "ObjectDetection", "checkpoints")
    commend = f"conda activate pytorch && python C:/xampp/htdocs/API/eating/python/ObjectDetection/Train/train.py --uid {uid} --classes-path {classesPath} --train-annotation-path {trainAnnotationPath} --coco-json-file {cocoJsonFile} --save-dir {savePath}"
    p = subprocess.Popen(commend, shell=True)
    p.wait()
    print(p.returncode)

if __name__ == '__main__':
    uid = sys.argv[1]
    storageRoot = sys.argv[2]
    main(uid=uid, storageRoot=storageRoot)
