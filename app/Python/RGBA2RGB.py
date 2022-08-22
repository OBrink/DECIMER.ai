import sys
import os
from PIL import Image

def main():
    """
    This script takes an image path given as the first argument,
    and converts it to an RGB if it is an RGBA image.
    """
    im_path = sys.argv[1]
    img = Image.open(im_path)
    if img.mode == 'RGBA':
        img.load()
        new_img = Image.new('RGB', img.size, (255, 255, 255))
        new_img.paste(img, mask=img.split()[3])
        new_img.save(im_path, "PNG")

if __name__ == '__main__':
    main()
