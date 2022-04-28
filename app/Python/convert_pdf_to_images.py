import sys
import os
import json
from pdf2image import convert_from_path


def convert_pdf_to_images(pdf_path: str):
    """
    This function takes the path of a pdf file, converts the pages to 300 DPI
    images, saves the resulting PNG files in the directory of the input pdf
    file and returns a json array containing the paths of the created pdf_files

    Args:
        pdf_path (str): path of pdf file

    Returns:
        _type_: json array with paths of generated png images
    """
    # Define path relative from location of Python script
    pdf_dir = os.path.split(__file__)[0]
    pdf_name = os.path.split(pdf_path)[1]
    base_path = os.path.join(pdf_dir, '../../storage/app/')
    full_pdf_path = os.path.join(base_path, pdf_path)
    
    # Convert pdf document to images
    # poppler_path = os.path.join(os.path.split(__file__)[0], 'poppler/bin/')
    # last_page param --> PAGE LIMITATION TO AVOID ABUSE OF WEB APP
    page_images = convert_from_path(full_pdf_path,
                                    300,
                                    last_page=10)
    im_paths = []
    num = 0
    for image in page_images:
        im_path = '{}_{}.png'.format(full_pdf_path, num)
        image.save(im_path, format='PNG')
        im_paths.append('media/{}_{}.png'.format(pdf_name, num))
        num += 1

    im_paths.sort()
    return json.dumps(im_paths)


if __name__ == '__main__':
    im_paths = convert_pdf_to_images(sys.argv[1])
    print(im_paths)
