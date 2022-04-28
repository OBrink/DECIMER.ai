# https://realpython.com/python-sockets/#multi-connection-client-and-server
import sys
import os
import socket
import selectors
import types
import json
from multiprocessing import Pool
base = os.path.split(__file__)[0]
sys.path.append(os.path.join(base, 'DECIMER_Image_Segmentation/'))
import Detect_and_save_segmentation


sel = selectors.DefaultSelector()


def accept_wrapper(sock):
    """
    Accept connection from client
    """
    conn, addr = sock.accept()  # Should be ready to read
    print(f"Accepted connection from {addr}")
    #conn.setblocking(False)
    data = types.SimpleNamespace(addr=addr, inb=b"", outb=b"")
    events = selectors.EVENT_READ | selectors.EVENT_WRITE
    sel.register(conn, events, data=data)


def service_connection(key, mask, model):
    """
    Handle connection to server, process data using DECIMER
    and send it back to client"""
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
            input_path = data.outb.decode('utf-8')
            # Run DECIMER Segmentation
            segment_paths = Detect_and_save_segmentation.coordination(
                input_path,
                model)

            # Send it back
            processed_info = json.dumps(segment_paths).encode('utf-8')
            print(f"Echoing {processed_info} to {data.addr}")
            sock.send(processed_info)  # Should be ready to write
            data.outb = b""


def run_server(port: int):
    """
    This function starts a DECIMER Segmentation socket server
    on 127.0.0.1 with a given port.

    Args:
        port (int)
    """
    #host = "127.0.0.1"  # The server's hostname or IP address
    host = "0.0.0.0"  # The server's hostname or IP address
    model = Detect_and_save_segmentation.load_segmentation_model()
    lsock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    lsock.bind((host, port))
    lsock.listen()
    print(f"Listening on {(host, port)}")
    #lsock.setblocking(False)
    sel.register(lsock, selectors.EVENT_READ, data=None)
    while True:
        try:
            events = sel.select(timeout=None)
            for key, mask in events:
                if key.data is None:
                    accept_wrapper(key.fileobj)
                else:
                    service_connection(key, mask, model)

        except KeyboardInterrupt:
            print("Caught keyboard interrupt, exiting")
            break
    sel.close()
    
    
def main():
    """
    Start DECIMER Segmentation server on given localhost port

    Returns:
        None

    """

    print("Setting up DECIMER Segmentation Server")
    print(f"Current working directory: {os.getcwd()}")

    run_server(int(sys.argv[1]))


if __name__ == "__main__":
    main()
