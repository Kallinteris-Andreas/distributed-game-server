from http.server import HTTPServer, BaseHTTPRequestHandler
import game_master_db
import json
import threading

def busy_wait():
    while True:
        pass

class auth_handler(BaseHTTPRequestHandler):
    def do_POST(self):
        if self.path.endswith('/newPractice'):
            threading.Thread(target=busy_wait).start()
            print("never")
        elif self.path.endswith('/getMyScores'):
            print("works")

def main():
    port = 6969
    server = HTTPServer(('', port ), auth_handler)
    print('Game Master Server running on port: ' + str(port))
    server.serve_forever()


if __name__ == '__main__':
    main()