"""
Generate professional SVG car illustrations for the car rental demo.
Each car gets a unique color and type-appropriate shape.
"""
import os

UPLOAD_DIR = r"D:\Nardo pro\online-car-rental\uploads"
os.makedirs(UPLOAD_DIR, exist_ok=True)

# Also copy to XAMPP
XAMPP_DIR = r"C:\xampp\htdocs\online-car-rental\uploads"
os.makedirs(XAMPP_DIR, exist_ok=True)

def car_svg(filename, body_color, body_color2, car_type="sedan"):
    """Generate a professional side-view car SVG."""

    if car_type == "sedan":
        # Sleek sedan profile
        body_path = "M 80 200 Q 80 180 100 175 L 180 170 Q 200 140 240 135 L 420 135 Q 460 140 480 170 L 560 175 Q 580 180 580 200 L 580 230 L 80 230 Z"
        windows = "M 210 145 L 240 145 Q 250 150 255 165 L 210 165 Z M 265 145 L 340 145 L 340 165 L 265 165 Z M 355 145 L 430 145 Q 450 150 460 165 L 355 165 Z"
        wheels = [(180, 235), (470, 235)]
    elif car_type == "suv":
        # Taller SUV profile
        body_path = "M 80 200 Q 80 175 100 170 L 170 165 Q 180 130 210 125 L 450 125 Q 480 130 490 165 L 560 170 Q 580 175 580 200 L 580 235 L 80 235 Z"
        windows = "M 195 135 L 215 135 Q 225 145 225 160 L 195 160 Z M 240 135 L 340 135 L 340 160 L 240 160 Z M 355 135 L 460 135 Q 475 145 480 160 L 355 160 Z"
        wheels = [(180, 240), (470, 240)]
    elif car_type == "pickup":
        # Pickup truck with bed
        body_path = "M 80 195 Q 80 170 100 165 L 170 160 Q 180 125 210 120 L 350 120 Q 370 125 375 160 L 375 195 L 580 195 L 580 230 L 80 230 Z"
        windows = "M 200 130 L 215 130 Q 225 140 225 155 L 200 155 Z M 240 130 L 350 130 L 350 155 L 240 155 Z"
        wheels = [(180, 235), (470, 235)]
    elif car_type == "van":
        # Boxy van
        body_path = "M 80 200 Q 80 175 100 170 L 140 165 L 140 110 Q 140 100 150 100 L 470 100 Q 480 100 480 110 L 480 165 L 560 170 Q 580 175 580 200 L 580 235 L 80 235 Z"
        windows = "M 155 110 L 240 110 L 240 155 L 155 155 Z M 250 110 L 340 110 L 340 155 L 250 155 Z M 350 110 L 465 110 L 465 155 L 350 155 Z"
        wheels = [(180, 240), (470, 240)]
    elif car_type == "electric":
        # Modern electric car (similar to sedan but sleeker)
        body_path = "M 80 200 Q 80 185 105 178 L 190 172 Q 210 142 250 138 L 410 138 Q 450 142 470 172 L 555 178 Q 580 185 580 200 L 580 230 L 80 230 Z"
        windows = "M 220 148 L 250 148 Q 258 153 262 168 L 220 168 Z M 275 148 L 350 148 L 350 168 L 275 168 Z M 365 148 L 440 148 Q 455 153 462 168 L 365 168 Z"
        wheels = [(180, 235), (470, 235)]
    elif car_type == "luxury":
        # Elegant luxury car (longer, lower)
        body_path = "M 60 205 Q 60 185 85 178 L 175 172 Q 200 140 250 135 L 430 135 Q 470 140 490 172 L 575 178 Q 600 185 600 205 L 600 230 L 60 230 Z"
        windows = "M 220 145 L 255 145 Q 263 150 267 165 L 220 165 Z M 285 145 L 360 145 L 360 165 L 285 165 Z M 375 145 L 460 145 Q 475 150 482 165 L 375 165 Z"
        wheels = [(170, 235), (490, 235)]
    elif car_type == "hatchback":
        # Compact hatchback
        body_path = "M 100 200 Q 100 180 120 175 L 190 170 Q 210 140 250 135 L 400 135 Q 430 140 440 170 L 490 175 Q 500 180 500 200 L 500 230 L 100 230 Z"
        windows = "M 220 145 L 250 145 Q 258 150 262 165 L 220 165 Z M 275 145 L 360 145 L 360 165 L 275 165 Z M 375 145 L 430 145 Q 440 150 443 165 L 375 165 Z"
        wheels = [(190, 235), (420, 235)]
    else:
        body_path = "M 80 200 Q 80 180 100 175 L 180 170 Q 200 140 240 135 L 420 135 Q 460 140 480 170 L 560 175 Q 580 180 580 200 L 580 230 L 80 230 Z"
        windows = "M 210 145 L 240 145 Q 250 150 255 165 L 210 165 Z M 265 145 L 340 145 L 340 165 L 265 165 Z M 355 145 L 430 145 Q 450 150 460 165 L 355 165 Z"
        wheels = [(180, 235), (470, 235)]

    wheels_svg = ""
    for wx, wy in wheels:
        wheels_svg += f"""
        <!-- Wheel -->
        <circle cx="{wx}" cy="{wy}" r="32" fill="#1a1a2e"/>
        <circle cx="{wx}" cy="{wy}" r="22" fill="#2d2d44"/>
        <circle cx="{wx}" cy="{wy}" r="14" fill="#4a4a6a"/>
        <circle cx="{wx}" cy="{wy}" r="6" fill="#1a1a2e"/>
        <!-- Rim spokes -->
        <line x1="{wx-18}" y1="{wy}" x2="{wx+18}" y2="{wy}" stroke="#3a3a55" stroke-width="3"/>
        <line x1="{wx}" y1="{wy-18}" x2="{wx}" y2="{wy+18}" stroke="#3a3a55" stroke-width="3"/>
        <line x1="{wx-13}" y1="{wy-13}" x2="{wx+13}" y2="{wy+13}" stroke="#3a3a55" stroke-width="3"/>
        <line x1="{wx-13}" y1="{wy+13}" x2="{wx+13}" y2="{wy-13}" stroke="#3a3a55" stroke-width="3"/>
        """

    svg = f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 660 300" width="660" height="300">
  <defs>
    <linearGradient id="bodyGrad_{filename}" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" style="stop-color:{body_color}"/>
      <stop offset="60%" style="stop-color:{body_color}"/>
      <stop offset="100%" style="stop-color:{body_color2}"/>
    </linearGradient>
    <linearGradient id="windowGrad_{filename}" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" style="stop-color:#a0c4e8;stop-opacity:0.9"/>
      <stop offset="100%" style="stop-color:#6b9dc4;stop-opacity:0.7"/>
    </linearGradient>
    <radialGradient id="shadowGrad_{filename}" cx="50%" cy="50%" r="50%">
      <stop offset="0%" style="stop-color:rgba(0,0,0,0.3)"/>
      <stop offset="100%" style="stop-color:rgba(0,0,0,0)"/>
    </radialGradient>
  </defs>

  <!-- Background -->
  <rect width="660" height="300" fill="#f0f4f8"/>

  <!-- Shadow under car -->
  <ellipse cx="330" cy="265" rx="260" ry="18" fill="url(#shadowGrad_{filename})"/>

  <!-- Car body -->
  <path d="{body_path}" fill="url(#bodyGrad_{filename})" stroke="{body_color2}" stroke-width="2"/>

  <!-- Windows -->
  <path d="{windows}" fill="url(#windowGrad_{filename})" stroke="#4a6a8a" stroke-width="1.5"/>

  <!-- Window divider lines -->
  <line x1="260" y1="145" x2="260" y2="165" stroke="#4a6a8a" stroke-width="2"/>
  <line x1="350" y1="145" x2="350" y2="165" stroke="#4a6a8a" stroke-width="2"/>

  <!-- Door line -->
  <line x1="330" y1="170" x2="330" y2="225" stroke="{body_color2}" stroke-width="1.5" opacity="0.5"/>

  <!-- Door handle -->
  <rect x="305" y="185" width="20" height="4" rx="2" fill="{body_color2}" opacity="0.6"/>
  <rect x="345" y="185" width="20" height="4" rx="2" fill="{body_color2}" opacity="0.6"/>

  <!-- Headlight -->
  <ellipse cx="565" cy="195" rx="12" ry="8" fill="#fef9e7" stroke="#e2c478" stroke-width="1"/>
  <ellipse cx="565" cy="195" rx="8" ry="5" fill="#fffde7"/>

  <!-- Taillight -->
  <rect x="82" y="188" width="14" height="14" rx="3" fill="#dc2626" opacity="0.8"/>

  <!-- Side mirror -->
  <ellipse cx="480" cy="172" rx="6" ry="4" fill="{body_color2}"/>

  <!-- Wheels -->
  {wheels_svg}

  <!-- Ground line -->
  <line x1="40" y1="270" x2="620" y2="270" stroke="#cbd5e1" stroke-width="2" stroke-dasharray="8,6"/>
</svg>'''

    for d in [UPLOAD_DIR, XAMPP_DIR]:
        filepath = os.path.join(d, filename)
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(svg)
        print(f"  Generated: {filepath}")


# Car definitions: (filename, color1, color2, type)
cars = [
    ("toyota_camry.svg",     "#1e3a5f", "#0f1f3a", "sedan"),
    ("toyota_v8.svg",        "#2d4a22", "#1a3015", "suv"),
    ("honda_civic.svg",      "#1a4d6e", "#0d2d44", "sedan"),
    ("isuzu_dmax.svg",       "#5c3a1a", "#3a2310", "pickup"),
    ("toyota_hiace.svg",     "#d4d4d4", "#a8a8a8", "van"),
    ("lexus_rx350.svg",     "#1a1a1a", "#0a0a0a", "luxury"),
    ("byd_atto3.svg",       "#2d7a3e", "#1a5028", "electric"),
    ("suzuki_swift.svg",    "#c41e3a", "#8a1428", "hatchback"),
]

print("Generating car images...")
for filename, c1, c2, ctype in cars:
    car_svg(filename, c1, c2, ctype)

print(f"\nDone! {len(cars)} images generated in both directories.")
