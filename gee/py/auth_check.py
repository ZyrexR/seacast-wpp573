# -*- coding: utf-8 -*-
"""
Authentication & Readiness Checker for Google Earth Engine Python API (`ee`).
Usage:
    python -m gee.py.auth_check [--project YOUR_GCP_PROJECT_ID]
"""
import argparse
import json
import os
import sys
import ee

CREDENTIALS_PATHS = [
    os.path.expanduser("~/.config/earthengine/credentials"),
    os.path.join(os.environ.get("APPDATA", ""), "earthengine", "credentials"),
    os.path.join(os.environ.get("USERPROFILE", ""), ".config", "earthengine", "credentials"),
]


def find_credentials_file():
    for p in CREDENTIALS_PATHS:
        if p and os.path.exists(p):
            return p
    return None


def check_gee_auth(project_id=None):
    cred_file = find_credentials_file()
    status = {
        "ee_package_version": ee.__version__,
        "credentials_file_found": cred_file is not None,
        "credentials_path": cred_file,
        "project_id": project_id or os.environ.get("EE_PROJECT_ID") or os.environ.get("GOOGLE_CLOUD_PROJECT") or "seca-513505",
        "initialized": False,
        "test_query_passed": False,
        "error": None
    }

    if not cred_file:
        status["error"] = (
            "GEE credentials file not found. Run `earthengine authenticate` or "
            "`python -c \"import ee; ee.Authenticate()\"` to sign in via Google OAuth."
        )
        return status

    try:
        if status["project_id"]:
            ee.Initialize(project=status["project_id"])
        else:
            ee.Initialize()
        status["initialized"] = True
        # Quick server-side test: query ETOPO1 band names
        bands = ee.Image("NOAA/NGDC/ETOPO1").bandNames().getInfo()
        status["test_query_passed"] = ("bedrock" in bands)
    except Exception as exc:
        status["error"] = str(exc)

    return status


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Check Google Earth Engine authentication status.")
    parser.add_argument("--project", type=str, default=None, help="Google Cloud Project ID registered for Earth Engine")
    parser.add_argument("--authenticate", action="store_true", help="Trigger interactive ee.Authenticate() browser flow")
    args = parser.parse_args()

    if args.authenticate:
        print("Starting interactive Google Earth Engine OAuth flow...")
        ee.Authenticate(auth_mode="notebook")

    res = check_gee_auth(project_id=args.project)
    print(json.dumps(res, indent=2))
    sys.exit(0 if res["test_query_passed"] else 1)
