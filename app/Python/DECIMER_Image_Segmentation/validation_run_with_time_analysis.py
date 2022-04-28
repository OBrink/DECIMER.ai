'''
 * This Software is under the MIT License
 * Refer to LICENSE or https://opensource.org/licenses/MIT for more information
 * Written by ©Kohulan Rajan 2020
'''
import os
import sys
import random
import math
import numpy as np
import skimage.io
import matplotlib
import matplotlib.pyplot as plt
import cv2
from PIL import Image
import argparse
import time

import warnings
warnings.filterwarnings("ignore")

from mrcnn import utils
from mrcnn import model as modellib
from mrcnn import visualize
from mrcnn import moldetect
from Scripts import complete_structure 


# Root directory of the project
ROOT_DIR = os.path.dirname(os.path.dirname(os.getcwd()))

def main():
	'''Takes the path of the input directory and the path of the output directory and puts the segmented structures with and without the mask expansion in the
	output directory.'''

	parser = argparse.ArgumentParser(description="Select the chemical structures from a scanned literature and save them")
    # Input Arguments
	parser.add_argument(
		'--input_dir',
		help='Enter the name of the directory with the input images',
		required=True
	)
	parser.add_argument(
		'--output_dir',
		help='Enter the name of the directory for the segmented output images',
		required=True
	)
	args = parser.parse_args()
	

	MODEL_DIR = os.path.join(ROOT_DIR, "logs")

	# Local path to trained weights file
	COCO_MODEL_PATH = os.path.join("model_trained/mask_rcnn_molecule.h5")

	# Download COCO trained weights from Releases if needed
	if not os.path.exists(COCO_MODEL_PATH):
		utils.download_trained_weights(COCO_MODEL_PATH)

	# Image Path
	#IMAGE_DIR = os.path.join("/media/data_drive/Kohulan/After-Meeting_20190522/MaskRCNN/Test")
	config = moldetect.MolDetectConfig()

	# Override the training configurations with a few
	# changes for inferencing.
	class InferenceConfig(config.__class__):
		# Run detection on one image at a time
		GPU_COUNT = 1
		IMAGES_PER_GPU = 1

	config = InferenceConfig()
	config.display()

	# Create model object in inference mode.
	model = modellib.MaskRCNN(mode="inference", model_dir=MODEL_DIR, config=config)

	# Load weights trained on MS-COCO
	model.load_weights(COCO_MODEL_PATH, by_name=True)

	class_names=['BG', 'Molecule']


	# Apply the model to every image in the input directory, save the result, apply the mask expansion and save the result again.
	with open(str(args.output_dir) + "/report.txt", "w") as output:
		output.write("File name \t Model detection time \t Expansion time \t Number of depictions \n")
	for file in os.listdir(args.input_dir):
		# Apply Mask R CNN model
		input_dir = args.input_dir
		t0 = time.time()
		r = get_masks(input_dir = input_dir, filename = file, model = model)
		t1 = time.time()
		model_seg = save_segments(expanded_masks = r['masks'], input_dir = input_dir, filename = file, output_dir = args.output_dir, mask_expansion = False)
		print(model_seg)

		# Expand masks to surround the complete structure
		image = skimage.io.imread(input_dir + "/" + file)
		t2 = time.time()
		expanded_masks = complete_structure.complete_structure_mask(image_array = image, mask_array = r['masks'], debug = False)
		t3 = time.time()
		final_seg = save_segments(expanded_masks = expanded_masks, input_dir = input_dir, filename = file, output_dir = args.output_dir, mask_expansion = True)
		print(final_seg)

		with open(str(args.output_dir) + "./report.txt", "a") as output:
			output.write(str(file) + "\t" + str(t1-t0) + "\t" + str(t3-t2) + "\t" + str(r["masks"].shape[2]) + "\n")

#expanded_masks, input_dir, filename, output_dir, mask_expansion = True
def get_masks(input_dir, filename, model):
	'''This function applies the Mask R CNN model on a given input image and returns the masks of the detected structures '''

	IMAGE_PATH = input_dir + "/" + filename

	image = skimage.io.imread(IMAGE_PATH)
	# Run detection
	results = model.detect([image], verbose=1)

	r = results[0]

	return r

def save_segments(expanded_masks, input_dir, filename, output_dir, mask_expansion = True):
	'''This function takes the masks, the corresponding image and an output directory and saves the segmented image
	of a structure depiction. The mask_expansion attribute only changes the name of the output file.'''
	IMAGE_PATH = input_dir + "/" + filename	

	mask = expanded_masks

	for i in range(mask.shape[2]):
		image = cv2.imread(os.path.join(IMAGE_PATH), -1)

		for j in range(image.shape[2]):
			image[:,:,j] = image[:,:,j] * mask[:,:,i]

		original = image.copy()

		#Remove unwanted background
		grayscale = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
		_,thresholded = cv2.threshold(grayscale,0,255,cv2.THRESH_OTSU)
		bbox = cv2.boundingRect(thresholded)
		x, y, w, h = bbox
		foreground = image[y:y+h, x:x+w]

		masked_image = np.zeros(image.shape).astype(np.uint8)
		masked_image = visualize.apply_mask(masked_image, mask[:, :, i],[1,1,1])
		masked_image = Image.fromarray(masked_image)
		masked_image = masked_image.convert('RGB')

		im_gray = cv2.cvtColor(np.asarray(masked_image), cv2.COLOR_RGB2GRAY)
		(thresh, im_bw) = cv2.threshold(im_gray, 128, 255, cv2.THRESH_BINARY | cv2.THRESH_OTSU)

		#Removal of transparent layer - black background
		_,alpha = cv2.threshold(im_bw,0,255,cv2.THRESH_BINARY)
		b, g, r = cv2.split(image)
		rgba = [b,g,r, alpha]
		dst = cv2.merge(rgba,4)
		background = dst[y:y+h, x:x+w]
		trans_mask = background[:,:,3] == 0
		background[trans_mask] = [255, 255, 255, 255]
		new_img = cv2.cvtColor(background, cv2.COLOR_BGRA2BGR)

		#save segments
		if mask_expansion:
			output_image = output_dir + filename + "_segment_%d.png"%i
		else:
			output_image = output_dir + filename + "_segment_NOEXPANSION_%d.png"%i


		cv2.imwrite(output_image, new_img)
		

	return "Completed, Segments saved inside the ouput folder!"


if __name__ == '__main__':
    main()

