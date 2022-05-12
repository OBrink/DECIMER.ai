import sys
import os
from PIL import Image


def main():
    """
    This script takes a stringified json array image path from sys.argv[1],
    checks if it has an exif-tag and prints 'true' or 'false'
    """
    paths = sys.argv[1]
    # Make sure the array with paths can be digested by eval
    paths = sys.argv[1].replace(',', '","')
    paths = paths.replace('[', '["').replace(']', '"]')
    paths = eval(paths)
    num_exif_tags = 0
    for image_path in paths:
        dir = '../storage/app/public/media/'
        image_path = os.path.join(dir, os.path.split(image_path)[1])
        image = Image.open(image_path)
        exif_tag = image.getexif()
        if exif_tag != {}:
            num_exif_tags += 1
    print(num_exif_tags)


if __name__ == '__main__':
    main()
