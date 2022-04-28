# https://realpython.com/python-sockets/#multi-connection-client-and-server

import sys
from itertools import cycle
import random
import json
from multiprocessing import Pool
import socket


def send_and_receive(path: str, port: int):
    """
    Send image path to local OCSR server with given port.
    Receive corresponding SMILES str

    Args:
        input_path (str): Path of image to process using DECIMER OCSR
        port (int): port of local segmentation socket server

    Returns:
        str: Received SMILES str
    """
    host = "supervisor"  # The server's hostname or IP address
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.connect((host, port))
        s.sendall(path.encode('utf-8'))
        data = s.recv(32768)
    return data.decode('utf-8')


def main():
    # Make sure the array with paths can be digested by eval
    paths = sys.argv[1].replace(',', '","')
    paths = paths.replace('[', '["').replace(']', '"]')
    paths = eval(paths)

    # Create endless iterator of shuffled ports
    num_ports = 6
    ports = list(range(65432, 65432 + num_ports))
    random.shuffle(ports)
    ports = cycle(ports)
    # Wrap up pairs of path and port and send out requests parallely
    starmap_tuples = [(path, next(ports))
                      for path in paths]
    with Pool(len(paths)) as p:
        SMILES = p.starmap(send_and_receive, starmap_tuples)

    print(json.dumps(SMILES))


if __name__ == '__main__':
    main()
