import os
import sys
import time
import re
import json
import urllib.parse
import requests

USERNAME = os.environ.get("EOG_USERNAME", "")
PASSWORD = os.environ.get("EOG_PASSWORD", "")
LOGIN_INIT_URL = (
    "https://eogdata.mines.edu/wwwdata/viirs_products/vbd/v23/idn/index.json"
)
MONTHLY_INDEX_URL = "https://eogdata.mines.edu/wwwdata/viirs_products/vbd/v23/idn/fnl/monthly/index.json"
BASE_DOWNLOAD_URL = (
    "https://eogdata.mines.edu/wwwdata/viirs_products/vbd/v23/idn/fnl/monthly/"
)

TARGET_DIR = os.path.abspath(
    os.path.join(os.path.dirname(__file__), "..", "data", "eog", "vbd", "monthly")
)
os.makedirs(TARGET_DIR, exist_ok=True)
PROGRESS_FILE = os.path.join(TARGET_DIR, "download_progress.json")

session = requests.Session()
session.headers.update(
    {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }
)


def login():
    print("[*] Performing authentication with EOG Mines...")
    if not USERNAME or not PASSWORD:
        print("[-] Set EOG_USERNAME and EOG_PASSWORD in the local environment.")
        return False
    try:
        r1 = session.get(LOGIN_INIT_URL, allow_redirects=True, timeout=30)
        if "index.json" in r1.url and r1.status_code == 200:
            print("[+] Session already authenticated.")
            return True

        form_match = re.search(r'<form[^>]+action=["\']([^"\']+)["\']', r1.text)
        if not form_match:
            print("[-] Login form not found on page.")
            return False

        form_action = form_match.group(1).replace("&amp;", "&")
        login_url = urllib.parse.urljoin(r1.url, form_action)

        input_matches = re.findall(
            r'<input[^>]+name=["\']([^"\']+)["\'][^>]*value=["\']([^"\']*)["\']',
            r1.text,
        )
        payload = {name: val for name, val in input_matches}
        payload["username"] = USERNAME
        payload["password"] = PASSWORD

        r2 = session.post(login_url, data=payload, allow_redirects=True, timeout=30)
        if r2.status_code == 200:
            print("[+] Authentication successful!")
            return True
        else:
            print(f"[-] Authentication failed with status: {r2.status_code}")
            return False
    except Exception as e:
        print(f"[-] Exception during login: {e}")
        return False


def get_monthly_file_list():
    r = session.get(MONTHLY_INDEX_URL, timeout=30)
    if r.status_code != 200:
        login()
        r = session.get(MONTHLY_INDEX_URL, timeout=30)

    if r.status_code != 200:
        raise RuntimeError(f"Cannot fetch monthly index: HTTP {r.status_code}")

    data = r.json()
    contents = data[0].get("contents", [])

    # Filter 2015 - 2026
    filtered = []
    for item in contents:
        name = item.get("name", "")
        if re.search(r"(201[5-9]|202[0-6])\d{4}", name) and name.endswith(".zip"):
            filtered.append(item)

    # Sort chronologically by filename
    filtered.sort(key=lambda x: x.get("name", ""))
    return filtered


def download_file(item, idx, total):
    filename = item["name"]
    expected_size = item.get("size", 0)
    dest_path = os.path.join(TARGET_DIR, filename)
    file_url = urllib.parse.urljoin(BASE_DOWNLOAD_URL, filename)

    # Check if already downloaded
    if os.path.exists(dest_path):
        current_size = os.path.getsize(dest_path)
        if current_size == expected_size and expected_size > 0:
            print(
                f"[{idx}/{total}] SKIP (already complete): {filename} ({current_size / (1024 * 1024):.2f} MB)"
            )
            return True

    print(
        f"[{idx}/{total}] DOWNLOADING: {filename} ({expected_size / (1024 * 1024):.2f} MB)..."
    )

    for attempt in range(1, 4):
        try:
            r = session.get(file_url, stream=True, timeout=60)

            # Re-authenticate if redirected to login
            if (
                r.status_code in (401, 403)
                or "openid-connect" in r.url
                or "login" in r.url
            ):
                print(
                    f"    [*] Session expired, re-authenticating (attempt {attempt})..."
                )
                login()
                r = session.get(file_url, stream=True, timeout=60)

            if r.status_code != 200:
                print(f"    [-] HTTP Error {r.status_code} on attempt {attempt}")
                time.sleep(3)
                continue

            temp_path = dest_path + ".part"
            downloaded = 0
            start_time = time.time()
            with open(temp_path, "wb") as f:
                for chunk in r.iter_content(chunk_size=1024 * 1024):  # 1 MB chunks
                    if chunk:
                        f.write(chunk)
                        downloaded += len(chunk)

            # Verify size
            if expected_size > 0 and downloaded != expected_size:
                print(
                    f"    [-] Size mismatch: got {downloaded}, expected {expected_size}. Retrying..."
                )
                if os.path.exists(temp_path):
                    os.remove(temp_path)
                time.sleep(2)
                continue

            # Move temp file to destination
            if os.path.exists(dest_path):
                os.remove(dest_path)
            os.rename(temp_path, dest_path)

            duration = time.time() - start_time
            speed = (downloaded / (1024 * 1024)) / max(duration, 0.1)
            print(
                f"    [+] SUCCESS: {filename} saved in {duration:.1f}s ({speed:.2f} MB/s)"
            )
            return True

        except Exception as e:
            print(f"    [-] Attempt {attempt} failed: {e}")
            time.sleep(3)

    print(f"[-] FAILED to download {filename} after 3 attempts!")
    return False


def main():
    if not login():
        print("[-] Exiting: Unable to login.")
        sys.exit(1)

    print("[*] Fetching monthly catalog for Indonesia (2015-2026)...")
    files = get_monthly_file_list()
    total_files = len(files)
    total_bytes = sum(f.get("size", 0) for f in files)
    print(
        f"[+] Total files to download: {total_files} ({total_bytes / (1024 * 1024):.2f} MB)"
    )

    success_count = 0
    failed_files = []

    for idx, item in enumerate(files, 1):
        ok = download_file(item, idx, total_files)
        if ok:
            success_count += 1
        else:
            failed_files.append(item["name"])

        # Update progress file
        progress = {
            "total_files": total_files,
            "completed": success_count,
            "current_index": idx,
            "current_file": item["name"],
            "failed_files": failed_files,
            "last_updated": time.strftime("%Y-%m-%d %H:%M:%S"),
        }
        with open(PROGRESS_FILE, "w", encoding="utf-8") as pf:
            json.dump(progress, pf, indent=2)

    print("\n==================== DOWNLOAD SUMMARY ====================")
    print(f"Total Target Files : {total_files}")
    print(f"Successfully Saved : {success_count}")
    print(f"Failed Files       : {len(failed_files)}")
    if failed_files:
        print(f"Failed file list   : {failed_files}")
    print(f"Destination Folder : {TARGET_DIR}")


if __name__ == "__main__":
    main()
