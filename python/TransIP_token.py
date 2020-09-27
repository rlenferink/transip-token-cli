#!/usr/bin/python3

# Copyright 2020 Roy Lenferink <lenferinkroy@gmail.com>
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License. 

from OpenSSL import crypto
import binascii    
import base64
import json
import requests
import time

class TransIP_token:

    # The uniqid implementation is re-used from https://www.php2python.com/wiki/function.uniqid/
    def uniqid(self, prefix = ''):
        return prefix + hex(int(time.time()))[2:10] + hex(int(time.time()*1000000) % 0x100000)[2:7]

    def create_signature(self, request_body, key):
        priv_key = crypto.load_privatekey(crypto.FILETYPE_PEM, key)

        signature_bin_str = crypto.sign(priv_key, json.dumps(request_body), 'sha512')
        encodedBytes = base64.b64encode(signature_bin_str)
        encodedStr = str(encodedBytes, "utf-8")

        return encodedStr

    def perform_request(self, request_body, signature):
        url = "https://api.transip.nl/v6/auth"
        headers = {
          'Content-Type': 'application/json',
          'Signature': signature,
        }
        resp = requests.post(url, json=request_body, headers=headers)
        if resp.status_code != 201:
             raise Exception("{} (status code = {})".format(resp.json()['error'], resp.status_code))

        return resp.json()['token']

    def create_token(self, login, key, label, exp_time="30 minutes", read_only=False, global_key=False):
        request_body = {
          'login': login,
          'nonce': self.uniqid(),
          'read_only': read_only,
          'expiration_time': exp_time,
          'label': label,
          'global_key': global_key,
        }

        signature = self.create_signature(request_body, key)
        return self.perform_request(request_body, signature)

def main():
    import argparse

    # Setup parser and program arguments
    parser = argparse.ArgumentParser(description='Program used for creating TransIP API tokens')
    parser.add_argument('login', help='the TransIP user')
    parser.add_argument('key', help='the private key generated from the TransIP console. see: https://www.transip.nl/cp/account/api/')
    parser.add_argument('label', help='the label for the created token')
    
    parser.add_argument('--expiration-time', help='the expiration time of the created token, with a maximum of 1 month')
    parser.add_argument('--read-only', action='store_true', help='when specified the created token can only be used in read only mode')
    parser.add_argument('--global-key', action='store_true', help='when specified the created token is not bound to the specified whitelist and can be used everywhere')

    # Parse arguments
    args = parser.parse_args()

    inst = TransIP_token()
    token = inst.create_token(args.login, args.key, args.label, args.expiration_time, args.read_only, args.global_key)
    print(token)

if __name__ == '__main__':
    main()

