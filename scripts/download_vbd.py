import os
import sys
import re
import json
import urllib.parse
import requests

USERNAME = os.environ.get("EOG_USERNAME", "")
PASSWORD = os.environ.get("EOG_PASSWORD", "")
LOGIN_INIT_URL = (
    "https://eogdata.mines.edu/wwwdata/viirs_products/vbd/v23/idn/index.json"
)
TARGET_DIR = os.path.abspath(
    os.path.join(os.path.dirname(__file__), "..", "data", "eog", "vbd")
)

session = requests.Session()
session.headers.update(
    {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }
)


def login():
    if not USERNAME or not PASSWORD:
        print("[-] Set EOG_USERNAME and EOG_PASSWORD in the local environment.")
        return False
    r1 = session.get(LOGIN_INIT_URL, allow_redirects=True)
    if "index.json" in r1.url and r1.status_code == 200:
        return True

    form_match = re.search(r'<form[^>]+action=["\']([^"\']+)["\']', r1.text)
    if not form_match:
        print("[-] Login form not found!")
        return False

    form_action = form_match.group(1).replace("&amp;", "&")
    login_url = urllib.parse.urljoin(r1.url, form_action)

    input_matches = re.findall(
        r'<input[^>]+name=["\']([^"\']+)["\'][^>]*value=["\']([^"\']*)["\']', r1.text
    )
    payload = {name: val for name, val in input_matches}
    payload["username"] = USERNAME
    payload["password"] = PASSWORD

    r2 = session.post(login_url, data=payload, allow_redirects=True)
    return r2.status_code == 200


if not login():
    print("Login failed")
    sys.exit(1)

url_monthly = "https://eogdata.mines.edu/wwwdata/viirs_products/vbd/v23/idn/fnl/monthly/index.json"
r = session.get(url_monthly)
print(f"Monthly index status: {r.status_code}")
if r.status_code == 200:
    data = r.json()
    contents = data[0].get("contents", [])
    print(f"Total items in monthly: {len(contents)}")

    # Save the monthly index
    monthly_index_file = os.path.join(TARGET_DIR, "monthly_index.json")
    with open(monthly_index_file, "w", encoding="utf-8") as f:
        json.dump(contents, f, indent=2)
    print(f"Saved monthly index to {monthly_index_file}")

    # Filter 2015 to 2026
    filtered = []
    for item in contents:
        name = item.get("name", "")
        # Match dates in filename, e.g. VBD_npp_20150101-20150131_idn_v23.csv.gz or .zip
        match = re.search(r"(201[5-9]|202[0-6])\d{4}", name)
        if match:
            filtered.append(item)

    print(f"Items matching 2015-2026: {len(filtered)}")
    total_size = sum(item.get("size", 0) for item in filtered)
    print(f"Total size of 2015-2026 monthly files: {total_size / (1024 * 1024):.2f} MB")
    print("First 5 files:")
    for f in filtered[:5]:
        print(f"  {f.get('name')} ({f.get('size') / 1024:.1f} KB)")
    print("Last 5 files:")
    for f in filtered[-5:]:
        print(f"  {f.get('name')} ({f.get('size') / 1024:.1f} KB)")
