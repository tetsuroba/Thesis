import struct
from PIL import Image, ImageEnhance
import numpy as np
from sklearn.preprocessing import LabelEncoder, OneHotEncoder
import tensorflow as tf
from tensorflow import keras
import matplotlib.pyplot as plt
import cv2 as cv
import pandas as pd

######Adat beolvasása és kimentése változókba
def read_data():
	record_size = 576
	data =  np.zeros((607200,63,64),dtype=np.uint8)
	labels = np.zeros(607200,dtype=np.uint16) #607200
	for j in range(0,5):
		filename = 'ETL9B_'+ str(j+1)
		k = j * 121440
		for i in range(121440):
			with open(filename, 'rb') as f:
				f.seek(i * record_size)
				s = f.read(record_size)
				r = struct.unpack('>2H4s504s64x', s)
				data[k,:,:] = np.array(Image.frombytes('1', (64, 63), r[3], 'raw'))
				labels[k] = r[1]
				k+=1
		print("done")
	return (data,labels)
###### Adatok és címkék elmentése .csv fájlba
def writedata(data):
	with open('F:\data\data.csv','w') as FOUT:
		np.savetxt(FOUT,data,fmt='%.0u',delimiter=',')
def writelabels(labels):
	with open('F:\data\labels.csv','w') as FOUT:
		np.savetxt(FOUT,labels,fmt='%.0u',delimiter=',')
###### Keverés
def unison_shuffled_copies(a, b):
    assert len(a) == len(b)
    p = np.random.permutation(len(a))
    return a[p], b[p]
###### Dictionary létrehozása, dictionary elmentése, címkék normalizálása a dictionary alapján
def create_dict(labels):
	uniq = np.unique(labels)
	newdict = dict()
	for i in range(0,len(uniq)):
		newdict[uniq[i]] = i
	return newdict
def savedict(dict):
	import csv
	w = csv.writer(open("dict.csv","w",newline=''))
	for key,val in dict.items():
		w.writerow([key,val])
def normalize_labels(labels,dict):
	newlabels = np.zeros(len(labels),dtype="uint16")
	for i in range(0,len(labels)):
		newlabels[i] = dict[labels[i]]
	return newlabels
###### Adat átméretezése 32x32-re
def resize_img(data):
	newdata = np.zeros((607200,32,32),dtype=np.uint8)
	for i in range(len(data)):
		newdata[i] = cv2.resize(data[i],(32,32))
	return newdata
###### Adat augmentáció, elforgatás
def rotate_data(data):
  import cv2 as cv
  rotated_data = np.zeros((607200,32,32),dtype=np.uint8)
  for i in range(len(data)):
	  angle = np.random.randint(90)-45
	  image_center = tuple(np.array(data[i].shape[1::-1]) / 2)
	  rot_mat = cv.getRotationMatrix2D(image_center,angle,1.0)
	  rotated_data[i] = cv.warpAffine(data[i], rot_mat, data[i].shape[1::-1], flags=cv.INTER_LINEAR)
  return rotated_data
###### Adat augmentáció, eltolás
def translate_data(data):
	import cv2 as cv
	translated_data = np.zeros((607200,32,32),dtype=np.uint8)
	for i in range(len(data)):
		amt1 = np.random.randint(10)-5
		amt2 = np.random.randint(10)-5
		T = np.float32([[1, 0, amt2], [0, 1, amt1]]) 
		translated_data[i] = cv.warpAffine(data[i], T, (32, 32)) 
	return translated_data
###### Adat augmentáció, só-bors zaj hozzáadása
def add_noise(data):
	noisydata = np.zeros((607200,32,32),dtype=np.uint8)
	for i in range(len(data)):
		noise = np.zeros(data[i].shape,np.int16)
		cv.randu(noise,-32,32)
		noisydata[i] = cv.add(data[i],noise,dtype=cv.CV_8UC1)
	return noisydata
