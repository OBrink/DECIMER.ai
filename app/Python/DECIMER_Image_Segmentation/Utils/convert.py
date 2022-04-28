import sys
from pdf2image import convert_from_path
import os


def convert(path):
    '''Takes all files from a given directory with pdf files and turns them into jpg files. xyz.pdf will produce xyz_1.png, xyz_2.png'''

    outputpath = os.path.abspath(str(path) + '_output')
    if os.path.exists(outputpath):
        pass
    else:
        os.system("mkdir " + outputpath)

    pages = convert_from_path(path, 500)
    dir_path, filename = os.path.split(path)
    pagecount = 1
    for page in pages:
        outputname = str(file + '_' + str(pagecount) + '.png')
        page.save(os.join(outputpath, outputname), 'PNG')
        pagecount += 1

    # print("All files are converted!")
    return outputpath

def main():
	print(sys.argv)
	if len(sys.argv) != 2:
		print("\"Usage of this function: convert.py input_directory")
	if len(sys.argv) == 2:
		convert(os.path.abspath(sys.argv[1]))
	sys.exit(1)

if __name__ == '__main__':
	main()