import tensorflow.compat.v1 as tf
import numpy as np
import pandas as pd
from PIL import Image
import cv2 as cv
from tensorflow.compat.v1 import keras
from keras.models import load_model
import jaconv
import sys

config = tf.ConfigProto()
config.gpu_options.allow_growth = True
session = tf.Session(config=config)

php_param = sys.argv[1]

def fnc(image,mode):
	img = cv.imread(image,cv.IMREAD_GRAYSCALE)
	if mode == 0:
		model = load_model('ffn.h5')
		if(img.shape != (32,32)):
			img = cv.resize(img,(32,32))
		img = np.asarray(img,dtype="uint8").reshape(1,1024) / 255
	elif mode == 1:
		model = tf.keras.models.load_model('cnn.h5')
		if(img.shape != (32,32)):
			img = cv.resize(img,(32,32))
		img = np.asarray(img,dtype="uint8").reshape(1,32,32,1) / 255
	d = pd.DataFrame(np.loadtxt("dict.csv",delimiter=',', dtype="uint16"))
	predictions = model.predict(img)
	top10 = np.argsort(predictions, axis=-1, kind='quicksort', order=None)[::-1]
	codes = np.zeros(10).astype(int)
	char = ["","","","","","","","","",""]
	chances = np.zeros(10)
	for i in range(0,10):
		codes[i] = d.iloc[top10[0][3036 - i]][0]
		chances[i] = predictions[0][top10[0][3036 - i]]
		char[i] = format(codes[i],'x')
		char[i] = bytes.fromhex( '1b2442' + str(char[i]) + '1b2842').decode('iso2022_jp')
	return (char,chances)
	
(preds,chances) = fnc(php_param,1)

for i in range(9,-1,-1):
	print(preds[i])
	print(f'{chances[i]:.4f}')
