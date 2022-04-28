'''
 * This Software is under the MIT License
 * Refer to LICENSE or https://opensource.org/licenses/MIT for more information
 * Written by ©Kohulan Rajan 2020
'''
import sys
import os
sys.path.append(os.path.split(__file__)[0])

import numpy as np
import skimage.io
import cv2
from PIL import Image
from mrcnn import model as modellib
from mrcnn import visualize
from mrcnn import moldetect
from Scripts import complete_structure
import warnings
warnings.filterwarnings("ignore")

# Root directory of the project
ROOT_DIR = os.path.dirname(os.path.dirname(os.getcwd()))


def coordination(page_image_path: str, model):
    """
    This function takes a list of paths of converted page images,
    runs the preloaded  Mask R-CNN model, and then coordinates the
    mask expansion.
    __
    The resulting list of saved segmented structure depiction paths
    is returned.

    Args:
        pdf_input_path (str): List of page image paths

    Returns:
        None
    """
    # Define image paths and output path
    output_dir = './storage/app/public/media/'
    page_image_path = os.path.join(output_dir,
                                   os.path.split(page_image_path)[1])
    # Run segmentation model
    raw_masks = get_masks(page_image_path, model)
    # Mask expansion
    file_paths = expand_and_save(raw_masks, page_image_path)
    return file_paths


def load_segmentation_model(path="model_trained/mask_rcnn_molecule.h5", config=False):
    # Directory to save logs and trained model
    MODEL_DIR = os.path.join(ROOT_DIR, "logs")
    # Local path to trained weights file
    #base = '../app/Python/DECIMER_Image_Segmentation/'
    #base = './DECIMER_Image_Segmentation/'
    base = os.path.split(__file__)[0]
    TRAINED_MODEL_PATH = os.path.join(base, path)
    if not config:
        config = moldetect.MolDetectConfig()

    # Override the training configurations with a few
    # changes for inference.
    class InferenceConfig(config.__class__):
        # Run detection on one image at a time
        GPU_COUNT = 1
        IMAGES_PER_GPU = 1
    config = InferenceConfig()
    # Create model object in inference mode.
    model = modellib.MaskRCNN(mode="inference",
                              model_dir=MODEL_DIR,
                              config=config)
    # Load weights trained on MS-COCO
    model.load_weights(TRAINED_MODEL_PATH, by_name=True)
    return model


def expand_and_save(masks, image_path):
    # Mask expansion
    image = skimage.io.imread(image_path)
    expanded_masks = complete_structure.complete_structure_mask(
        image_array=image,
        mask_array=masks['masks'],
        debug=False)
    # Save segments
    segmented_img = save_segments(expanded_masks, image_path)
    return segmented_img


def get_masks(IMAGE_PATH, model):
    # Read image
    image = skimage.io.imread(IMAGE_PATH)

    # Run detection
    results = model.detect([image], verbose=1)
    r = results[0]
    return r


def save_segments(expanded_masks: np.array, image_path: str):
    file_paths = []

    for i in range(expanded_masks.shape[2]):
        image = cv2.imread(os.path.join(image_path), -1)

        for j in range(image.shape[2]):
            image[:, :, j] = image[:, :, j] * expanded_masks[:, :, i]

        # Remove unwanted background
        grayscale = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        _, thresholded = cv2.threshold(grayscale, 0, 255, cv2.THRESH_OTSU)
        bbox = cv2.boundingRect(thresholded)
        x, y, w, h = bbox

        masked_image = np.zeros(image.shape).astype(np.uint8)
        masked_image = visualize.apply_mask(masked_image,
                                            expanded_masks[:, :, i],
                                            [1, 1, 1])
        masked_image = Image.fromarray(masked_image)
        masked_image = masked_image.convert('RGB')

        im_gray = cv2.cvtColor(np.asarray(masked_image), cv2.COLOR_RGB2GRAY)
        (_, im_bw) = cv2.threshold(im_gray,
                                   128,
                                   255,
                                   cv2.THRESH_BINARY | cv2.THRESH_OTSU)

        # Removal of transparent layer - black background
        _, alpha = cv2.threshold(im_bw, 0, 255, cv2.THRESH_BINARY)
        b, g, r = cv2.split(image)
        rgba = [b, g, r, alpha]
        dst = cv2.merge(rgba, 4)
        background = dst[y:y+h, x:x+w]
        trans_mask = background[:, :, 3] == 0
        background[trans_mask] = [255, 255, 255, 255]
        new_img = cv2.cvtColor(background, cv2.COLOR_BGRA2BGR)

        # Define the correct path to save the segments
        image_name = os.path.split(image_path)[1]
        filename = f"{image_name[:-4]}_{i}.png"
        media_path = '../storage/media/'
        file_path = os.path.join(media_path, filename)
        file_paths.append(file_path)
        cv2.imwrite(os.path.join('./storage/app/public/media/', filename), new_img)
    return file_paths


if __name__ == '__main__':
    file_paths = coordination()
    print(file_paths)
