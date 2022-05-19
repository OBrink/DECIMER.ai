# https://realpython.com/python-sockets/#multi-connection-client-and-server

import sys
import json
import random
from itertools import cycle
from multiprocessing import Pool
import socket


def send_and_receive(smiles: str, port: int):
    """
    Send image path to local STOUT server with given port.
    Receive corresponding IUPAC name (str)

    Args:
        smiles: SMILES str to process using STOUT V2
        port (int): port of local segmentation socket server

    Returns:
        str: Received IUPAC name str
    """
    if len(smiles) == 0:
        return "Unable to generate IUPAC name"
    host = "supervisor"  # The server's hostname or IP address
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.connect((host, port))
        s.sendall(smiles.encode('utf-8'))
        data = s.recv(32768)
    return data.decode('utf-8')[1:-1]


def main():
    # Make sure the array with SMILES str can be digested by eval
    replacement_dict = {' ': '',
                        ',': '","', }
    smiles = sys.argv[1][1:-1]
    smiles = '["' + smiles + '"]'
    for key in replacement_dict.keys():
        smiles = smiles.replace(key, replacement_dict[key])
    smiles = eval(smiles)

    # Create endless iterator of shuffled ports
    num_ports = 4
    ports = list(range(12345, 12345 + num_ports))
    random.shuffle(ports)
    ports = cycle(ports)
    # Wrap up pairs of path and port and send out requests parallely
    starmap_tuples = [(smi, next(ports))
                      for smi in smiles]
    with Pool(len(smiles)) as p:
        SMILES = p.starmap(send_and_receive, starmap_tuples)
    print(json.dumps(SMILES))


if __name__ == '__main__':
    main()
