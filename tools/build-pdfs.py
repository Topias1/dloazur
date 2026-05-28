#!/usr/bin/env python3
"""
Builds the project PDF exports under docs/exports/:

  - docs/exports/recap-client.pdf   (1-page-spread client recap for Pierre)
  - docs/exports/cadrage-v3.pdf     (full framing note, multi-page)

Renders with locally-installed Google Chrome (headless print-to-pdf), the
same engine the mockups already target. No native deps to install.

Usage:   python3 tools/build-pdfs.py
"""

from __future__ import annotations
import os
import subprocess
import sys
from pathlib import Path

import markdown

ROOT = Path(__file__).resolve().parents[1]
EXP = ROOT / "docs" / "exports"
EXP.mkdir(parents=True, exist_ok=True)

CHROME = "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
if not Path(CHROME).exists():
    sys.exit(f"Chrome not found at {CHROME}. Install it or edit this script.")


def chrome_pdf(input_html: Path, output_pdf: Path) -> None:
    cmd = [
        CHROME,
        "--headless=new",
        "--disable-gpu",
        "--no-pdf-header-footer",
        "--virtual-time-budget=30000",
        f"--print-to-pdf={output_pdf}",
        f"file://{input_html.resolve()}",
    ]
    print("→", input_html.relative_to(ROOT))
    res = subprocess.run(cmd, capture_output=True, text=True)
    if res.returncode != 0:
        sys.stderr.write(res.stderr)
        sys.exit(res.returncode)
    print(f"  {output_pdf.relative_to(ROOT)}  ({output_pdf.stat().st_size // 1024} KB)")


# ---- 1. Client recap (HTML already designed for print) --------------------

recap_html = EXP / "recap-client.html"
chrome_pdf(recap_html, EXP / "recap-client.pdf")


# ---- 2. Cadrage v3 (MD → styled HTML → PDF) -------------------------------

cadrage_md = ROOT / "docs/superpowers/specs/2026-05-27-dloazur-refonte-design.md"
md_text = cadrage_md.read_text(encoding="utf-8")
md_html = markdown.markdown(
    md_text,
    extensions=["extra", "tables", "sane_lists", "attr_list"],
)

cadrage_template = f"""<!doctype html>
<html lang="fr"><head>
<meta charset="utf-8">
<title>Note de cadrage v3 · Dlo Azur Piscines</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap">
<style>
:root {{
  --azure: oklch(0.615 0.211 256);
  --azure-deep: oklch(0.470 0.176 256);
  --azure-light: oklch(0.965 0.022 256);
  --marine: oklch(0.288 0.066 250);
  --marine-deep: oklch(0.232 0.052 251);
  --lagon: oklch(0.720 0.113 207);
  --lagon-deep: oklch(0.520 0.085 211);
  --sand: oklch(0.987 0.005 85);
  --line: oklch(0.928 0.011 80);
  --ink: oklch(0.255 0.045 250);
  --ink-mid: oklch(0.445 0.030 250);
  --ink-soft: oklch(0.585 0.024 250);
}}
@page {{ size: A4; margin: 18mm 18mm 20mm; }}
@page :first {{ margin: 0; }}
html, body {{ margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }}
body {{ font-family: 'Inter', system-ui, sans-serif; font-size: 10.5pt; line-height: 1.55; color: var(--ink-mid); background: white; }}

.cover {{
  width: 210mm; height: 297mm;
  background: var(--marine-deep); color: white;
  padding: 22mm 20mm 18mm;
  display: flex; flex-direction: column; justify-content: space-between;
  break-after: page;
}}
.cover .brand-row {{ display: flex; align-items: center; gap: 9px; }}
.cover .brand {{ font-family: Fredoka; font-weight: 700; font-size: 22px; color: white; line-height: 1; }}
.cover .tag {{ font-size: 9px; letter-spacing: 0.24em; text-transform: uppercase; color: oklch(0.788 0.130 256); margin-top: 2px; }}
.cover h1 {{ font-family: Fredoka; font-weight: 700; font-size: 56px; line-height: 1.04; margin: 0; color: white; max-width: 78%; letter-spacing: -0.012em; }}
.cover .lede {{ font-size: 16px; max-width: 78%; line-height: 1.5; color: oklch(0.908 0.025 246); margin-top: 14px; }}
.cover .meta {{ font-size: 11px; color: oklch(0.708 0.060 247); padding-top: 14px; border-top: 1px solid rgba(255,255,255,0.08); }}
.cover .meta strong {{ color: white; font-weight: 600; }}

article {{ padding-top: 0; }}
h1, h2, h3, h4 {{ font-family: 'Fredoka', system-ui, sans-serif; color: var(--ink); font-weight: 700; letter-spacing: -0.005em; }}
article > h1 {{ display: none; }} /* the cover already has the title */
h2 {{ font-size: 20pt; margin: 14pt 0 6pt; line-height: 1.12; break-before: page; }}
h2:first-of-type {{ break-before: avoid; }}
h3 {{ font-size: 12.5pt; font-weight: 600; margin: 14pt 0 4pt; }}
h4 {{ font-size: 11pt; font-weight: 600; margin: 8pt 0 2pt; }}
p {{ margin: 5pt 0; }}
ul, ol {{ margin: 5pt 0 5pt 1.2em; padding: 0; }}
li {{ margin: 2pt 0; }}
strong {{ color: var(--ink); font-weight: 600; }}
em {{ color: var(--ink); font-style: italic; }}
hr {{ border: 0; border-top: 1px solid var(--line); margin: 14pt 0; }}
code {{ font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 0.88em; background: var(--azure-light); color: var(--azure-deep); padding: 0.5pt 4pt; border-radius: 3pt; }}
pre {{ background: var(--azure-light); border-radius: 6pt; padding: 8pt 10pt; overflow: hidden; margin: 8pt 0; }}
pre code {{ background: none; padding: 0; color: var(--marine); display: block; line-height: 1.45; }}
table {{ border-collapse: collapse; width: 100%; font-size: 9.5pt; margin: 8pt 0 12pt; break-inside: avoid; }}
th, td {{ text-align: left; vertical-align: top; padding: 5pt 7pt; border-bottom: 1px solid var(--line); }}
th {{ font-family: Fredoka; font-weight: 600; color: var(--ink); background: var(--sand); }}
a {{ color: var(--azure-deep); text-decoration: none; border-bottom: 0.6pt solid oklch(0.470 0.176 256 / 0.35); }}
blockquote {{ margin: 8pt 0; padding: 4pt 12pt; color: var(--ink-mid); background: var(--azure-light); border-radius: 4pt; font-style: italic; }}
</style>
</head><body>

<section class="cover">
  <div class="brand-row">
    <svg width="32" height="38" viewBox="0 0 28 34" fill="none"><path d="M14 1.5C14 1.5 3.5 13.5 3.5 22a10.5 10.5 0 0 0 21 0C24.5 13.5 14 1.5 14 1.5Z" fill="oklch(0.702 0.176 256)"/><path d="M8.4 21.4c1.6-2.3 3.8-2.3 5.6 0s4 2.3 5.6 0" stroke="oklch(0.232 0.052 251)" stroke-width="1.8" stroke-linecap="round"/></svg>
    <div><div class="brand">Dlo Azur</div><div class="tag">Piscines</div></div>
  </div>
  <div>
    <h1>Note de cadrage<br>Refonte plateforme.</h1>
    <p class="lede">Décisions verrouillées, architecture, modèle de données, roadmap, hébergement, stratégie Odoo, état des maquettes.</p>
    <p class="meta">Version <strong>v3</strong> · 28 mai 2026 · <strong>Topias1/dloazur</strong></p>
  </div>
</section>

<article>
{md_html}
</article>

</body></html>
"""

cadrage_html_path = EXP / "_cadrage-print.html"
cadrage_html_path.write_text(cadrage_template, encoding="utf-8")
chrome_pdf(cadrage_html_path, EXP / "cadrage-v3.pdf")

print("\nBuilt:")
for pdf in sorted(EXP.glob("*.pdf")):
    print(f"  {pdf.relative_to(ROOT)}  ({pdf.stat().st_size // 1024} KB)")
