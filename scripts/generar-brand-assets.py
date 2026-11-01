#!/usr/bin/env python3
"""Genera raster (PNG/ICO) del kit de marca VentasÁgil a partir del símbolo vectorial."""
from __future__ import annotations

from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "public" / "brand"

TEAL = (15, 118, 110, 255)       # #0f766e
TEAL_LIGHT = (20, 184, 166, 255) # #14b8a6
INK = (15, 23, 42, 255)          # #0f172a
WHITE = (255, 255, 255, 255)
BLACK = (0, 0, 0, 255)
TEAL_SOFT = (94, 234, 212, 255)  # #5eead4

# Rayo legible a 16px (coords en espacio 0..64)
BOLT = [
    (36.5, 9.5),
    (17.5, 34.2),
    (30.7, 34.2),
    (25.6, 54.5),
    (49.0, 26.1),
    (35.4, 26.1),
    (36.5, 9.5),
]


def lerp(a, b, t):
    return tuple(int(a[i] + (b[i] - a[i]) * t) for i in range(4))


def rounded_rect_mask(size: int, radius: float) -> Image.Image:
    m = Image.new("L", (size, size), 0)
    d = ImageDraw.Draw(m)
    d.rounded_rectangle((0, 0, size - 1, size - 1), radius=radius, fill=255)
    return m


def paint_plate(size: int, mode: str) -> Image.Image:
    """mode: color | mono | light | stamp"""
    img = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    px = img.load()
    r = size * 14 / 64
    mask = rounded_rect_mask(size, r)

    if mode == "color":
        for y in range(size):
            for x in range(size):
                if mask.getpixel((x, y)) < 128:
                    continue
                t = (x + y) / (2 * (size - 1))
                px[x, y] = lerp(TEAL_LIGHT, TEAL, t)
    elif mode == "light":
        base = Image.new("RGBA", (size, size), WHITE)
        img = Image.composite(base, img, mask)
    elif mode in ("mono", "stamp"):
        fill = BLACK if mode == "stamp" else INK
        base = Image.new("RGBA", (size, size), fill)
        img = Image.composite(base, img, mask)
    else:
        raise ValueError(mode)

    bolt_fill = TEAL if mode == "light" else WHITE
    draw_bolt(img, size, bolt_fill)
    return img


def draw_bolt(img: Image.Image, size: int, fill) -> None:
    scale = size / 64
    pts = [(x * scale, y * scale) for x, y in BOLT]
    ImageDraw.Draw(img).polygon(pts, fill=fill)


def font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont | ImageFont.ImageFont:
    candidates = [
        r"C:\Windows\Fonts\segoeuib.ttf" if bold else r"C:\Windows\Fonts\segoeui.ttf",
        r"C:\Windows\Fonts\arialbd.ttf" if bold else r"C:\Windows\Fonts\arial.ttf",
        "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf" if bold else "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf",
    ]
    for path in candidates:
        if Path(path).exists():
            return ImageFont.truetype(path, size)
    return ImageFont.load_default()


def save(img: Image.Image, name: str) -> None:
    path = OUT / name
    path.parent.mkdir(parents=True, exist_ok=True)
    img.save(path)
    print(f"  {path.relative_to(ROOT)}")


def logo_horizontal(mode: str, width: int = 1120, height: int = 224) -> Image.Image:
    img = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    icon_size = int(height * 0.86)
    pad_y = (height - icon_size) // 2
    plate_mode = "stamp" if mode == "mono" else ("light" if mode == "on_dark" else "color")
    icon = paint_plate(icon_size, plate_mode if mode != "mono" else "stamp")
    img.alpha_composite(icon, (int(height * 0.04), pad_y))

    f_size = int(height * 0.48)
    f_reg = font(f_size, bold=True)
    f_bold = font(f_size, bold=True)
    draw = ImageDraw.Draw(img)
    x = int(height * 1.05)
    y = int(height * 0.28)

    if mode == "mono":
        c1 = c2 = BLACK
    elif mode == "on_dark":
        c1, c2 = WHITE, TEAL_SOFT
    else:
        c1, c2 = INK, TEAL

    draw.text((x, y), "Ventas", font=f_reg, fill=c1)
    # medir "Ventas"
    bbox = draw.textbbox((x, y), "Ventas", font=f_reg)
    x2 = bbox[2] + int(height * 0.02)
    draw.text((x2, y), "Ágil", font=f_bold, fill=c2)
    return img


def logo_vertical(mode: str, size: int = 800) -> Image.Image:
    w, h = size, int(size * 1.12)
    img = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    icon_size = int(size * 0.52)
    icon = paint_plate(icon_size, "stamp" if mode == "mono" else "color")
    img.alpha_composite(icon, ((w - icon_size) // 2, int(size * 0.06)))

    f_size = int(size * 0.11)
    f = font(f_size, bold=True)
    draw = ImageDraw.Draw(img)
    c1, c2 = (BLACK, BLACK) if mode == "mono" else (INK, TEAL)
    y1 = int(size * 0.68)
    y2 = int(size * 0.82)

    def center(text, y, fill):
        bbox = draw.textbbox((0, 0), text, font=f)
        tw = bbox[2] - bbox[0]
        draw.text(((w - tw) // 2, y), text, font=f, fill=fill)

    center("Ventas", y1, c1)
    center("Ágil", y2, c2)
    return img


def wordmark(mode: str, width: int = 880, height: int = 192) -> Image.Image:
    img = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    f = font(int(height * 0.62), bold=True)
    draw = ImageDraw.Draw(img)
    c1, c2 = (BLACK, BLACK) if mode == "mono" else (INK, TEAL)
    x, y = int(height * 0.05), int(height * 0.18)
    draw.text((x, y), "Ventas", font=f, fill=c1)
    bbox = draw.textbbox((x, y), "Ventas", font=f)
    draw.text((bbox[2] + 4, y), "Ágil", font=f, fill=c2)
    return img


def og_image() -> Image.Image:
    w, h = 1200, 630
    img = Image.new("RGBA", (w, h), (248, 250, 252, 255))
    draw = ImageDraw.Draw(img)
    # franja teal
    draw.rectangle((0, 0, w, 12), fill=TEAL)
    draw.rectangle((0, h - 12, w, h), fill=TEAL)

    icon = paint_plate(180, "color")
    img.alpha_composite(icon, (80, (h - 180) // 2 - 20))

    f1 = font(72, bold=True)
    f2 = font(36, bold=False)
    draw.text((300, 210), "Ventas", font=f1, fill=INK)
    bbox = draw.textbbox((300, 210), "Ventas", font=f1)
    draw.text((bbox[2] + 8, 210), "Ágil", font=f1, fill=TEAL)
    draw.text((300, 310), "POS en la nube para bodegas y negocios", font=f2, fill=(100, 116, 139, 255))
    draw.text((300, 360), "ventasagil.com", font=f2, fill=TEAL)
    return img


def whatsapp_profile(size: int = 640) -> Image.Image:
    img = Image.new("RGBA", (size, size), TEAL)
    # degradado suave
    px = img.load()
    for y in range(size):
        for x in range(size):
            t = (x + y) / (2 * (size - 1))
            px[x, y] = lerp(TEAL_LIGHT, TEAL, t)
    bolt_size = int(size * 0.62)
    # dibujar solo el rayo blanco centrado
    layer = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    # bolt en coords 64 → escalar a bolt_size y centrar
    scale = bolt_size / 64
    pts = [
        (size / 2 - bolt_size / 2 + x * scale, size / 2 - bolt_size / 2 + y * scale)
        for x, y in BOLT
    ]
    ImageDraw.Draw(layer).polygon(pts, fill=WHITE)
    return Image.alpha_composite(img, layer)


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    print("Generando kit de marca VentasÁgil…")

    # Iconos / favicons
    for s in (16, 32, 48, 64, 128, 180, 192, 256, 512):
        save(paint_plate(s, "color"), f"png/icon-{s}.png")

    save(paint_plate(512, "mono"), "png/icon-mono-512.png")
    save(paint_plate(512, "light"), "png/icon-light-512.png")
    save(paint_plate(512, "stamp"), "png/icon-stamp-512.png")
    save(paint_plate(1024, "color"), "png/icon-1024.png")

    # ICO multi-resolución (Pillow genera 16/32/48 desde la mayor)
    ico_master = paint_plate(48, "color").convert("RGBA")
    ico_path = OUT / "favicon.ico"
    ico_master.save(ico_path, format="ICO", sizes=[(16, 16), (32, 32), (48, 48)])
    fav_public = ROOT / "public" / "favicon.ico"
    fav_public.write_bytes(ico_path.read_bytes())
    print(f"  {ico_path.relative_to(ROOT)}")
    print(f"  {fav_public.relative_to(ROOT)}")

    # Apple / PWA
    save(paint_plate(180, "color"), "apple-touch-icon.png")
    save(paint_plate(192, "color"), "icon-192.png")
    save(paint_plate(512, "color"), "icon-512.png")

    # WhatsApp / redes
    save(whatsapp_profile(640), "png/whatsapp-profile-640.png")
    save(whatsapp_profile(512), "png/whatsapp-profile-512.png")

    # Logos
    save(logo_horizontal("color"), "png/logo-horizontal.png")
    save(logo_horizontal("color", 560, 112), "png/logo-horizontal-sm.png")
    save(logo_horizontal("on_dark"), "png/logo-horizontal-on-dark.png")
    save(logo_horizontal("mono"), "png/logo-horizontal-mono.png")
    save(logo_vertical("color"), "png/logo-vertical.png")
    save(logo_vertical("mono"), "png/logo-vertical-mono.png")
    save(wordmark("color"), "png/wordmark.png")
    save(wordmark("mono"), "png/wordmark-mono.png")

    # Sellos / factura (fondo blanco opcional con margen)
    stamp = paint_plate(512, "stamp")
    invoice = Image.new("RGBA", (600, 600), WHITE)
    invoice.alpha_composite(stamp, (44, 44))
    save(invoice, "png/sello-factura.png")

    save(og_image(), "og-image.png")

    # Compatibilidad con rutas actuales
    save(logo_horizontal("color", 880, 176), "logo.png")
    # logo.svg ya se mantiene como horizontal en disco vía copia en script shell

    print("Listo.")


if __name__ == "__main__":
    main()
