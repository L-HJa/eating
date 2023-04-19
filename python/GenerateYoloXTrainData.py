import os
import sys
import shutil
import cv2
from YoloXTrainDataHelper import main as transferToCoco
# 由於YoloxObjectDetection不是module所以無法使用sys引入，看是不是要把它變成module比較快
# from YoloxTrain import main as yoloxTrain

def main(uid, storageRoot):
    changeFileName(uid=uid, storageRoot=storageRoot)
    createTrainClassesTxt(uid=uid, storageRoot=storageRoot)
    createTrainTxt(uid=uid, storageRoot=storageRoot)
    sourcePath = os.path.join(storageRoot, uid, "ObjectDetection")
    transferToCoco(source_path=sourcePath)
    # yoloxTrain(uid=uid, storageRoot=storageRoot)
    print("Success")

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


if __name__ == '__main__':
    uid = sys.argv[1]
    storageRoot = sys.argv[2]
    main(uid=uid, storageRoot=storageRoot)
