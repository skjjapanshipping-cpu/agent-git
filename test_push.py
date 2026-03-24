# Test push to SKJ-BUYER group
import json
import urllib.request

data = json.dumps({
    "to": "C05368736c382d9f1d1bc337eaf774def",
    "messages": [{"type": "text", "text": "[TEST] ทดสอบส่งข้อความไปกลุ่ม SKJ-BUYER"}]
}).encode('utf-8')

token = "OrVFxm7rXoooOcOtE3itsf2DU0kRiwCnrofa1cMpbPimEsEN6f7yHksV2itA/wMnhe6est8COdfQ3xR3ji6vS2tQi+UiCP5hk6JPwI3tWoEl12JS+j2lXXhL/6CoAqYv4ltv5A5NXvKzyKsO6TEdHQdB04t89/1O/w1cDnyilFU="

req = urllib.request.Request(
    "https://api.line.me/v2/bot/message/push",
    data=data,
    headers={
        "Content-Type": "application/json",
        "Authorization": f"Bearer {token}"
    },
    method="POST"
)

try:
    resp = urllib.request.urlopen(req)
    print(f"STATUS: {resp.status}")
    print(f"BODY: {resp.read().decode()}")
except urllib.error.HTTPError as e:
    print(f"ERROR STATUS: {e.code}")
    print(f"ERROR BODY: {e.read().decode()}")
