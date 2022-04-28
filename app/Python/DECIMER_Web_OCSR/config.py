# Network configuration file
import tensorflow as tf
import efficientnet.tfkeras as efn
from PIL import Image
import numpy as np
import io

TARGET_DTYPE = tf.float32


def central_square_image(im):
    """
    This function takes a Pillow Image object and will add white padding
    so that the image has a square shape with the width/height of the longest side
    of the original image.
    ___
    im: PIL.Image
    ___
    output: PIL.Image
    """
    max_wh = int(1.2 * max(im.size))
    # If the new image is smaller than 299x299, then let's paste it into an empty image
    # of that size instead of distorting it later while resizing.
    if max_wh < 299:
        max_wh = 299
    new_im = Image.new(im.mode, (max_wh, max_wh), "white")
    paste_pos = (
        int((new_im.size[0] - im.size[0]) / 2),
        int((new_im.size[1] - im.size[1]) / 2),
    )
    new_im.paste(im, paste_pos)
    return new_im


def delete_empty_borders(im):
    """This function takes a Pillow Image object, converts it to grayscale and
    deletes white space at the borders.
    ___
    im: PIL.Image
    ___
    output: PIL.Image
    """
    im = np.asarray(im.convert("L"))
    mask = im > 200
    rows = np.flatnonzero((~mask).sum(axis=1))
    cols = np.flatnonzero((~mask).sum(axis=0))
    crop = im[rows.min() : rows.max() + 1, cols.min() : cols.max() + 1]
    return Image.fromarray(crop)


def PIL_im_to_BytesIO(im):
    """
    Convert pillow image to io.BytesIO object
    ___
    im: PIL.Image
    ___
    Output: io.BytesIO object with the image data
    """
    output = io.BytesIO()
    im.save(output, format="PNG")
    return output


def decode_image(image_path: str):
    """
    Loads and preprocesses an image
    Args:
        image_path (str): path of input image

    Returns:
        Processed image
    """
    img = Image.open(image_path)
    img = delete_empty_borders(img)
    img = central_square_image(img)
    img = PIL_im_to_BytesIO(img)
    img = tf.image.decode_png(img.getvalue(), channels=3)
    img = tf.image.resize(img, (299, 299))
    img = efn.preprocess_input(img)
    return img
