# https://realpython.com/python-sockets/#multi-connection-client-and-server

import sys
import random
import json
from itertools import cycle
from multiprocessing import Pool
import socket


def send_and_receive(input_path: str, port: int):
    """
    Send page image path to local segmentation server with given port.
    Receive path of segmented chemical structure images.

    Args:
        input_path (str): Path of image to process using DECIMER Segmentation
        port (int): port of local segmentation socket server

    Returns:
        str: Received path of chemical structure image
    """
    HOST = "supervisor"  # The server's hostname or IP address
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.connect((HOST, port))
        s.sendall(input_path.encode('utf-8'))
        data = s.recv(32768)
    return data.decode('utf-8')


def main():
    # Make sure the array with paths can be digested by eval
    paths = sys.argv[1].replace(',', '","').replace(
        '[', '["').replace(']', '"]')
    paths = eval(paths)

    # Create endless iterator of shuffled ports
    num_ports = 6
    ports = list(range(23456, 23456 + num_ports))
    random.shuffle(ports)
    ports = cycle(ports)

    # Wrap up pairs of path and port and send out requests parallely
    starmap_tuples = [(path, next(ports))
                      for path in paths]
    with Pool(len(paths)) as p:
        json_array_path_strings = p.starmap(send_and_receive, starmap_tuples)

    # Wrap up received data in a single list of paths
    paths = [eval(path) for path in json_array_path_strings]
    paths = [li for su in paths for li in su]

    print(json.dumps(paths))


if __name__ == '__main__':
    main()
