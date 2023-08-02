# https://realpython.com/python-sockets/#multi-connection-client-and-server
import sys
import os
import socket
import selectors
import types
from decimer_image_classifier import DecimerImageClassifier

sel = selectors.DefaultSelector()
decimer_classifier = DecimerImageClassifier()


def accept_wrapper(sock):
    """
    Accept connection from client
    """
    conn, addr = sock.accept()  # Should be ready to read
    print(f"Accepted connection from {addr}")
    data = types.SimpleNamespace(addr=addr, inb=b"", outb=b"")
    events = selectors.EVENT_READ | selectors.EVENT_WRITE
    sel.register(conn, events, data=data)


def service_connection(key, mask):
    """
    Handle connection to server, process data using DECIMER
    Image Classifier and send it back to client"""
    sock = key.fileobj
    data = key.data
    if mask & selectors.EVENT_READ:
        recv_data = sock.recv(32768)  # Should be ready to read
        if recv_data:
            print(f"Received_data: {recv_data}")
            data.outb += recv_data
        else:
            print(f"Closing connection to {data.addr}")
            sel.unregister(sock)
            sock.close()
    if mask & selectors.EVENT_WRITE:
        if data.outb:
            # Edit recieved path
            path = data.outb.decode('utf-8')
            file_dir = './storage/app/public/media/'
            file_name = os.path.split(path)[1]
            path = os.path.join(file_dir, file_name)
            # Run DECIMER Image Classifier
            result = decimer_classifier.is_chemical_structure(img=path)
            # Send it back
            processed_info = str(result).encode('utf-8')
            print(f"Echoing {processed_info} to {data.addr}")
            sock.send(processed_info)  # Should be ready to write
            data.outb = b""


def run_server(port: int):
    """
    This function starts a DECIMER Image Classifier socket server
    on 0.0.0.0 with a given port.

    Args:
        port (int)
    """
    host = "0.0.0.0"  # The server's hostname or IP address
    lsock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    lsock.bind((host, port))
    lsock.listen()
    print(f"Listening on {(host, port)}")
    sel.register(lsock, selectors.EVENT_READ, data=None)
    while True:
        try:
            events = sel.select(timeout=None)
            for key, mask in events:
                if key.data is None:
                    accept_wrapper(key.fileobj)
                else:
                    service_connection(key, mask)
        except KeyboardInterrupt:
            print("Caught keyboard interrupt, exiting")
            break
    sel.close()


def main():
    """
    Start local DECIMER OCSR server.
    Loading the model takes up approximately half a minute.
    By running this server in the background and sending requests
    to it, there is no need to reload the model whenever an image
    needs to be processed.

    Returns:
        None

    """
    print(f"Setting up DECIMER Image Classifier Server on port {sys.argv[1]}")
    print(f"Current working directory: {os.getcwd()}")

    run_server(int(sys.argv[1]))


if __name__ == "__main__":
    main()
