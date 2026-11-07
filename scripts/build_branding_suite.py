import os
import numpy as np
from PIL import Image
from scipy import ndimage

light_src = r'C:/Users/ThinkPad/.gemini/antigravity/brain/651d3902-698f-40e4-bf3e-f7e31db31569/.user_uploaded/media_1793853201984_9922ce67.png'
dark_src = r'C:/Users/ThinkPad/.gemini/antigravity/brain/651d3902-698f-40e4-bf3e-f7e31db31569/.user_uploaded/media_1793853199903_38f1e0c0.png'

brand_dir = r'seacast-prigi-laravel/public/assets/branding'
legacy_brand_dir = r'seacast-prigi-laravel/public/images/brand'
preview_dir = r'C:/Users/ThinkPad/.gemini/antigravity/brain/651d3902-698f-40e4-bf3e-f7e31db31569/brand_previews'
os.makedirs(brand_dir, exist_ok=True)
os.makedirs(legacy_brand_dir, exist_ok=True)
os.makedirs(preview_dir, exist_ok=True)

# -------------------------------------------------------------------------
# 1. LIGHT THEME FULL LOGO
# -------------------------------------------------------------------------
print("Processing Light Theme Logo...")
img_l_raw = Image.open(light_src)
# Crop to logo content (discard editorial label at Y < 68 and bottom empty margin)
# Content is in Y=[75, 445], X=[38, 480]
pad = 6
crop_box_l = (36, 74, 482, 448)
img_l = img_l_raw.crop(crop_box_l)
arr_l = np.array(img_l).astype(float)[:, :, :3]
H_l, W_l, _ = arr_l.shape

# Distance from pure white
dist_w = np.sqrt(np.sum((255.0 - arr_l)**2, axis=2))
is_bg_seed_l = dist_w < 10.0
lbl_l, n_l = ndimage.label(is_bg_seed_l)
bg_id_l = lbl_l[0, 0]
is_bg_l = (lbl_l == bg_id_l)

# Enclosed letter counter holes at Y >= 190 (SEACAST and subtitle)
for i in range(1, n_l + 1):
    if i == bg_id_l:
        continue
    coords = np.argwhere(lbl_l == i)
    if coords[:, 0].min() >= 190:
        is_bg_l = is_bg_l | (lbl_l == i)

alpha_l = np.ones((H_l, W_l), dtype=float)
alpha_l[is_bg_l] = 0.0
edge_l = (~is_bg_l) & (dist_w < 45.0)
alpha_l[edge_l] = np.clip(dist_w[edge_l] / 45.0, 0.0, 1.0)

# Unmix white background
fg_l = np.copy(arr_l)
val_l = alpha_l > 0.04
for c in range(3):
    unmixed = (fg_l[:, :, c] - (1.0 - alpha_l) * 255.0) / np.maximum(alpha_l, 0.04)
    fg_l[:, :, c] = np.clip(np.where(val_l, unmixed, fg_l[:, :, c]), 0.0, 255.0)

rgba_l = np.dstack([fg_l.astype(np.uint8), (alpha_l * 255.0).astype(np.uint8)])
logo_light = Image.fromarray(rgba_l, mode='RGBA')

# -------------------------------------------------------------------------
# 2. DARK THEME FULL LOGO
# -------------------------------------------------------------------------
print("Processing Dark Theme Logo...")
img_d_raw = Image.open(dark_src)
crop_box_d = (36, 74, 482, 448)
img_d = img_d_raw.crop(crop_box_d)
arr_d = np.array(img_d).astype(float)[:, :, :3]
H_d, W_d, _ = arr_d.shape
bg_color_d = np.array([12.0, 25.0, 42.0])

dist_d = np.sqrt(np.sum((arr_d - bg_color_d)**2, axis=2))
is_bg_seed_d = dist_d < 8.0
lbl_d, n_d = ndimage.label(is_bg_seed_d)
bg_id_d = lbl_d[0, 0]
is_bg_d = (lbl_d == bg_id_d)

for i in range(1, n_d + 1):
    if i == bg_id_d:
        continue
    coords = np.argwhere(lbl_d == i)
    if coords[:, 0].min() >= 190:
        is_bg_d = is_bg_d | (lbl_d == i)

alpha_d = np.ones((H_d, W_d), dtype=float)
alpha_d[is_bg_d] = 0.0
edge_d = (~is_bg_d) & (dist_d < 40.0)
alpha_d[edge_d] = np.clip(dist_d[edge_d] / 40.0, 0.0, 1.0)

fg_d = np.copy(arr_d)
val_d = alpha_d > 0.04
for c in range(3):
    unmixed = (fg_d[:, :, c] - (1.0 - alpha_d) * bg_color_d[c]) / np.maximum(alpha_d, 0.04)
    fg_d[:, :, c] = np.clip(np.where(val_d, unmixed, fg_d[:, :, c]), 0.0, 255.0)

rgba_d = np.dstack([fg_d.astype(np.uint8), (alpha_d * 255.0).astype(np.uint8)])
logo_dark = Image.fromarray(rgba_d, mode='RGBA')

# -------------------------------------------------------------------------
# 3. SYMBOL ONLY (Light & Dark)
# -------------------------------------------------------------------------
print("Processing Symbol Marks...")
# Symbol region in cropped space is Y=[0, 192], X=[38, 440]
sym_box = (38, 0, 440, 192)
sym_l_crop = logo_light.crop(sym_box)
sym_d_crop = logo_dark.crop(sym_box)

def make_square_symbol(crop_img, size=512):
    sq = Image.new('RGBA', (size, size), (0, 0, 0, 0))
    w, h = crop_img.size
    # Fit into box with 10% padding
    target_box = int(size * 0.88)
    scale = min(target_box / w, target_box / h)
    nw, nh = int(w * scale), int(h * scale)
    scaled = crop_img.resize((nw, nh), Image.Resampling.LANCZOS)
    ox = (size - nw) // 2
    oy = (size - nh) // 2
    sq.paste(scaled, (ox, oy), scaled)
    return sq

symbol_light = make_square_symbol(sym_l_crop)
symbol_dark = make_square_symbol(sym_d_crop)

# -------------------------------------------------------------------------
# 4. SAVE ASSETS
# -------------------------------------------------------------------------
print("Saving Brand Suite Assets...")
# Primary canonical paths: public/assets/branding/
path_logo_light = os.path.join(brand_dir, 'seacast-logo-light.png')
path_logo_dark = os.path.join(brand_dir, 'seacast-logo-dark.png')
path_sym_light = os.path.join(brand_dir, 'seacast-symbol-light.png')
path_sym_dark = os.path.join(brand_dir, 'seacast-symbol-dark.png')

logo_light.save(path_logo_light, 'PNG')
logo_dark.save(path_logo_dark, 'PNG')
symbol_light.save(path_sym_light, 'PNG')
symbol_dark.save(path_sym_dark, 'PNG')

# Legacy alias paths: public/images/brand/ for backward-compatibility
logo_light.save(os.path.join(legacy_brand_dir, 'seacast-logo-light.png'), 'PNG')
logo_dark.save(os.path.join(legacy_brand_dir, 'seacast-logo-dark.png'), 'PNG')
symbol_light.save(os.path.join(legacy_brand_dir, 'seacast-symbol-light.png'), 'PNG')
symbol_dark.save(os.path.join(legacy_brand_dir, 'seacast-symbol-dark.png'), 'PNG')
logo_light.save(os.path.join(legacy_brand_dir, 'logo-full.png'), 'PNG')
logo_dark.save(os.path.join(legacy_brand_dir, 'logo-full-dark.png'), 'PNG')
symbol_light.save(os.path.join(legacy_brand_dir, 'logo-mark.png'), 'PNG')
symbol_dark.save(os.path.join(legacy_brand_dir, 'logo-mark-dark.png'), 'PNG')

# Favicon and App Icons
fav_sizes = [(16, 16), (32, 32), (48, 48)]
symbol_dark.resize((32, 32), Image.Resampling.LANCZOS).save(os.path.join(brand_dir, 'favicon-32x32.png'), 'PNG')
symbol_dark.resize((16, 16), Image.Resampling.LANCZOS).save(os.path.join(brand_dir, 'favicon-16x16.png'), 'PNG')
symbol_dark.resize((32, 32), Image.Resampling.LANCZOS).save(os.path.join(legacy_brand_dir, 'favicon-32x32.png'), 'PNG')
symbol_dark.resize((16, 16), Image.Resampling.LANCZOS).save(os.path.join(legacy_brand_dir, 'favicon-16x16.png'), 'PNG')

ico_images = [symbol_dark.resize(s, Image.Resampling.LANCZOS) for s in fav_sizes]
ico_images[0].save('seacast-prigi-laravel/public/favicon.ico', format='ICO', sizes=[(s[0], s[1]) for s in fav_sizes])

# Apple touch icon (180x180) on dark navy squircle background
ati = Image.new('RGBA', (180, 180), (7, 27, 43, 255))
ati_sym = sym_d_crop.resize((140, int(sym_d_crop.size[1] * 140.0 / sym_d_crop.size[0])), Image.Resampling.LANCZOS)
ox = (180 - ati_sym.width) // 2
oy = (180 - ati_sym.height) // 2
ati.paste(ati_sym, (ox, oy), ati_sym)
ati.save(os.path.join(brand_dir, 'apple-touch-icon.png'), 'PNG')
ati.save(os.path.join(legacy_brand_dir, 'apple-touch-icon.png'), 'PNG')

# Save Preview Composites for verification
navy_canvas = Image.new('RGBA', logo_dark.size, (7, 27, 43, 255))
comp_dark = Image.alpha_composite(navy_canvas, logo_dark)
comp_dark.save(os.path.join(preview_dir, 'refined_preview_dark.png'), 'PNG')

white_canvas = Image.new('RGBA', logo_light.size, (255, 255, 255, 255))
comp_light = Image.alpha_composite(white_canvas, logo_light)
comp_light.save(os.path.join(preview_dir, 'refined_preview_light.png'), 'PNG')

print("All branding suite assets built successfully!")
print(f"  Logo Light: {logo_light.size}")
print(f"  Logo Dark:  {logo_dark.size}")
print(f"  Symbol:     {symbol_dark.size}")
