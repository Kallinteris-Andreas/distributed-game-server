from http.server import HTTPServer, BaseHTTPRequestHandler
import json
import auth_db

class auth_handler(BaseHTTPRequestHandler):
    def do_GET(self):
        if self.path.endswith('/listUsers'):
            users = auth_db.list_users()
            keys = ['username', 'role']
            data = [dict(zip(keys, user)) for user in users]
            response = json.dumps(data, indent=4)
            self.send_response(200)
            self.send_header('Content-type','application/json')
            self.end_headers()
            self.wfile.write(response.encode("utf-8"))
        elif self.path.endswith('/listPlayers'):
            users = auth_db.list_players()
            keys = ['username']
            data = [dict(zip(keys, user)) for user in users]
            response = json.dumps(data, indent=4)
            self.send_response(200)
            self.send_header('Content-type','application/json')
            self.end_headers()
            self.wfile.write(response.encode("utf-8"))
        else:
            self.send_response(500)
            self.end_headers()
    def do_POST(self):
        content_len = int(self.headers.get('Content-Length'))
        post_body = self.rfile.read(content_len)
        json_body = json.loads(post_body)
        if self.path.endswith('/createUser'):
            username = (json_body['username'])
            password = (json_body['password'])
            que = (auth_db.create_user(username, password))
            if que == False:
                self.send_response(500)
                self.end_headers()
            else:
                self.send_response(200)
                self.end_headers()
        elif self.path.endswith('/changePassword'):
            token = (json_body['token'])
            new_password = (json_body['newpassword'])
            (auth_db.change_password(token, new_password))
        elif self.path.endswith('/login'):
            username = (json_body['username'])
            password = (json_body['password'])
            que = (auth_db.login(username, password))
            if que == False:
                self.send_response(401)
                self.end_headers()
            else:
                token = (que[0])
                role = (que[1])
                response = (json.dumps({'token':token, 'role':role}))
                self.send_response(200)
                self.send_header('Content-type','application/json')
                self.end_headers()
                self.wfile.write(response.encode("utf-8"))
        elif self.path.endswith('/validateToken'):
            token = (json_body['token'])
            que = auth_db.validate_token(token)
            if que == False:
                self.send_response(401)
                self.end_headers()
            else:
                username = (que[0])
                role = (que[1])
                response = (json.dumps({'username':username, 'role':role}))
                self.send_response(200)
                self.send_header('Content-type','application/json')
                self.end_headers()
                self.wfile.write(response.encode("utf-8"))
        elif self.path.endswith('/updateRole'):
            username = (json_body['username'])
            role = (json_body['newrole'])
            print (username)
            print (role)
            if auth_db.update_role(username,role):
                self.send_response(200)
            else:
                self.send_response(500)
            self.end_headers()
        elif self.path.endswith('/logout'):
            token = (json_body['token'])
            if auth_db.logout(token):
                self.send_response(200)
            else:
                self.send_response(500)
            self.end_headers()
               
        else:
            print(self.path)
            self.send_response(500)
            self.end_headers()

def main():
    port = 42069
    server = HTTPServer(('', port ), auth_handler)
    print('Server running on port: ' + str(port))
    server.serve_forever()


if __name__ == '__main__':
    main()