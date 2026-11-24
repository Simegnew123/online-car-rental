"""
Fix car SVGs: make backgrounds transparent so card gradient shows through.
Also change white van to a more visible silver color.
"""
import os

dirs = [
    r"D:\Nardo pro\online-car-rental\uploads",
    r"C:\xampp\htdocs\online-car-rental\uploads",
]

for d in dirs:
    for fn in os.listdir(d):
        if not fn.endswith('.svg'):
            continue
        path = os.path.join(d, fn)
        with open(path, 'r', encoding='utf-8') as f:
            svg = f.read()

        # Replace light background rect with transparent
        svg = svg.replace(
            '<rect width="660" height="300" fill="#f0f4f8"/>',
            '<rect width="660" height="300" fill="none"/>'
        )

        # Make ground line transparent (it was grey on light bg, invisible on dark)
        svg = svg.replace(
            'stroke="#cbd5e1" stroke-width="2" stroke-dasharray="8,6"',
            'stroke="rgba(255,255,255,0.3)" stroke-width="2" stroke-dasharray="8,6"'
        )

        # Fix white van — change from #d4d4d4 to a more visible silver
        if 'toyota_hiace' in fn:
            svg = svg.replace('#d4d4d4', '#9ca3af')  # darker silver
            svg = svg.replace('#a8a8a8', '#6b7280')  # darker silver gradient end

        with open(path, 'w', encoding='utf-8') as f:
            f.write(svg)

        print(f"  Fixed: {path}")

print("\nDone! All SVG backgrounds are now transparent.")
