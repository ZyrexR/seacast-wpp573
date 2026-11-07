import os
import time
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By

out_dir = r"C:/Users/ThinkPad/.gemini/antigravity/brain/651d3902-698f-40e4-bf3e-f7e31db31569/screenshots"
os.makedirs(out_dir, exist_ok=True)

chrome_options = Options()
chrome_options.add_argument("--headless=new")
chrome_options.add_argument("--window-size=1440,900")
chrome_options.add_argument("--disable-gpu")
chrome_options.add_argument("--no-sandbox")

driver = webdriver.Chrome(options=chrome_options)

try:
    # 1. Homepage
    print("Navigating to Homepage...")
    driver.get("http://127.0.0.1:8000/")
    time.sleep(2)
    driver.save_screenshot(os.path.join(out_dir, "24_brand_logo_homepage.png"))
    print("Saved 24_brand_logo_homepage.png")

    # 2. Login Page
    print("Navigating to Login Page...")
    driver.get("http://127.0.0.1:8000/login")
    time.sleep(2)
    driver.save_screenshot(os.path.join(out_dir, "25_brand_logo_login.png"))
    print("Saved 25_brand_logo_login.png")

    # 3. Log in and capture Dashboard Overview
    print("Logging in to Dashboard...")
    driver.get("http://127.0.0.1:8000/login")
    time.sleep(1)
    driver.find_element(By.ID, "email").send_value = "operator@seacast.id"
    driver.execute_script("document.getElementById('email').value = 'operator@seacast.id'; document.getElementById('password').value = 'seacast2026';")
    driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
    time.sleep(3)
    driver.save_screenshot(os.path.join(out_dir, "26_brand_logo_dashboard_sidebar.png"))
    print("Saved 26_brand_logo_dashboard_sidebar.png")

    # 4. Design System Showroom
    print("Navigating to Design System...")
    driver.get("http://127.0.0.1:8000/design-system")
    time.sleep(2)
    driver.save_screenshot(os.path.join(out_dir, "27_brand_logo_design_system.png"))
    print("Saved 27_brand_logo_design_system.png")

finally:
    driver.quit()
    print("Done capturing screenshots.")

