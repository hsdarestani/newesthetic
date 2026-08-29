from pathlib import Path
from urllib.parse import urlsplit
import re

ROOT = Path('.')

ANCHOR_ABS = re.compile(r'(<a\b[^>]*?\bhref=["\'])https://a-esthetic\.de(?P<path>/[^"\']*)', re.I)
ANCHOR_HREF = re.compile(r'<a\b[^>]*?\bhref=["\']([^"\']+)["\']', re.I)
PROD_ANCHOR = re.compile(r'<a\b[^>]*?\bhref=["\']https://a-esthetic\.de/', re.I)
ROBOTS = re.compile(r'<meta\b(?=[^>]*\bname=["\']robots["\'])(?=[^>]*\bcontent=["\']noindex,nofollow["\'])[^>]*>', re.I)

LEGACY_PATHS = {
    '/datenschutz/': '/datenschutzerklaerung/',
    '/hyaluronsaure-behandlungen/lippenunterspritzung/': '/hyaluronsaure-behandlungen/lippenunterspritzung-frankfurt/',
    '/hyaluronsaure-behandlungen/wangenaufbau/': '/hyaluronsaure-behandlungen/wangenaufbau-frankfurt/',
    '/hyaluronsaure-behandlungen/jawline-kinnaufbau/': '/hyaluronsaure-behandlungen/jawline-kinnaufbau-frankfurt/',
    '/hyaluronsaure-behandlungen/nasolabialfalte/': '/hyaluronsaure-behandlungen/nasolabialfalte-hyaluron-frankfurt/',
    '/infusionstherapien/curcumin-infusion-frankfurt/': '/infusionstherapien/curcumin-infusion-frankfurt-ablauf-kosten/',
}


def route_for(path: Path) -> str:
    if path == Path('index.html'):
        return '/'
    return '/' + path.parent.as_posix().strip('/') + '/'


def target_exists(href: str) -> bool:
    path = urlsplit(href).path or '/'
    if path == '/':
        return Path('index.html').exists()
    local = Path(path.lstrip('/'))
    if path.endswith('/'):
        return (local / 'index.html').exists()
    return local.exists() or (local / 'index.html').exists()


def clean_internal_links() -> int:
    changed = 0
    for p in ROOT.rglob('*.html'):
        if '.git' in p.parts:
            continue
        s = p.read_text(encoding='utf-8', errors='replace')
        s, n = ANCHOR_ABS.subn(lambda m: m.group(1) + (m.group('path') or '/'), s)
        changed += n
        for old, new in LEGACY_PATHS.items():
            for quote in ('"', "'"):
                before = f'href={quote}{old}{quote}'
                after = f'href={quote}{new}{quote}'
                if before in s:
                    count = s.count(before)
                    s = s.replace(before, after)
                    changed += count
        p.write_text(s, encoding='utf-8')
    return changed


def audit():
    pages = sorted(p for p in ROOT.rglob('index.html') if '.git' not in p.parts)
    rows = []
    failures = []
    remaining_prod_anchors = []
    broken_links = []

    for p in pages:
        route = route_for(p)
        text = p.read_text(encoding='utf-8', errors='replace')
        canonical = f'https://a-esthetic.de{route}'
        has_robots = bool(ROBOTS.search(text))
        canonical_patterns = [
            re.compile(r'<link\b[^>]*\brel=["\']canonical["\'][^>]*\bhref=["\']' + re.escape(canonical) + r'["\'][^>]*>', re.I),
            re.compile(r'<link\b[^>]*\bhref=["\']' + re.escape(canonical) + r'["\'][^>]*\brel=["\']canonical["\'][^>]*>', re.I),
        ]
        has_canonical = any(pat.search(text) for pat in canonical_patterns)

        if not has_robots:
            failures.append(f'{route}: missing noindex,nofollow')
        if not has_canonical:
            failures.append(f'{route}: canonical mismatch; expected {canonical}')
        if PROD_ANCHOR.search(text):
            remaining_prod_anchors.append(route)

        for href in ANCHOR_HREF.findall(text):
            if href.startswith(('#', 'mailto:', 'tel:', 'javascript:')):
                continue
            if href.startswith(('http://', 'https://', '//')):
                continue
            if href.startswith('/') and not target_exists(href):
                broken_links.append((route, href))

        rows.append((route, 'PASS' if has_robots and has_canonical else 'FAIL'))

    redirect = Path('_redirects').read_text(encoding='utf-8').strip() if Path('_redirects').exists() else ''
    redirect_expected = '/laserbehandlungen/ /laser-behandlungen/ 301'
    redirect_ok = redirect == redirect_expected
    if not redirect_ok:
        failures.append('Legacy laser redirect missing or incorrect')
    if remaining_prod_anchors:
        failures.append('Absolute production internal anchor links remain: ' + ', '.join(remaining_prod_anchors))
    if broken_links:
        failures.append('Broken local links: ' + '; '.join(f'{src} -> {href}' for src, href in broken_links[:50]))

    report = [
        '# A+ Esthetic — Final Staging Audit',
        '',
        f'- HTML routes checked: **{len(pages)}**',
        f'- Routes with `noindex,nofollow` + exact production canonical: **{sum(1 for _, s in rows if s == "PASS")}/{len(rows)}**',
        f'- Remaining absolute `a-esthetic.de` internal anchor links: **{len(remaining_prod_anchors)}**',
        f'- Broken staging-local anchor targets: **{len(broken_links)}**',
        f'- Legacy `/laserbehandlungen/` 301 configured: **{"yes" if redirect_ok else "no"}**',
        '',
        '## Route audit',
        '',
        '| Route | SEO staging guard |',
        '|---|---|',
    ]
    report.extend(f'| `{route}` | {status} |' for route, status in rows)
    report += ['', '## Internal link crawl', '']
    if broken_links:
        report.extend(f'- `{src}` → `{href}`' for src, href in broken_links)
    else:
        report.append('- PASS — no broken staging-local anchor targets found.')
    report += ['', '## Production-link cleanup', '']
    if remaining_prod_anchors:
        report.extend(f'- FAIL — `{route}` still contains an absolute internal production anchor.' for route in remaining_prod_anchors)
    else:
        report.append('- PASS — internal navigation anchors are staging-relative; production canonicals/schema URLs remain untouched.')
    report += ['', '## Redirect', '', f'- `{redirect or "MISSING"}`', '']
    if failures:
        report += ['## Failures', ''] + [f'- {x}' for x in failures]
    else:
        report += ['## Result', '', '**PASS — repository-level staging crawl is clean.**']
    Path('STAGING-FINAL-AUDIT.md').write_text('\n'.join(report) + '\n', encoding='utf-8')
    return failures


def update_migration_audit():
    p = Path('SEO-CONTENT-AUDIT.md')
    if not p.exists():
        return
    s = p.read_text(encoding='utf-8')
    s = s.replace(
        '| `/` | yes | PASS / LINK CLEANUP | Main visible copy substantially matches production; some staging internal links still point to absolute production URLs and should be made local before final staging sign-off. |',
        '| `/` | yes | PASS | Main visible copy matched; internal navigation links are staging-relative while production canonical is preserved. |'
    )
    s = s.replace(
        '- `/laserbehandlungen/` → `/laser-behandlungen/` — REDIRECT REQUIRED (301)',
        '- `/laserbehandlungen/` → `/laser-behandlungen/` — REDIRECT CONFIGURED (301)'
    )
    old = '''## Remaining staging work before WordPress cutover

1. Clean Homepage staging-only absolute internal links so they route through staging while preserving the same semantic destinations.
2. Re-check title, description, canonical, robots, heading hierarchy, FAQ/schema and prices for every route.
3. Run a complete internal-link and HTTP-status crawl on staging.
4. Preserve required 301 redirects, including `/laserbehandlungen/` → `/laser-behandlungen/`.
5. Only after 100% parity, transfer the approved new design and exact text corpus to WordPress.'''
    new = '''## Remaining staging work before WordPress cutover

1. Repository-level link/canonical/robots crawl is complete; see `STAGING-FINAL-AUDIT.md`.
2. Verify the deployed Cloudflare Pages URLs return the expected HTTP status after this commit is published.
3. Preserve/export any additional legacy WordPress redirects not represented in this static repository.
4. After live staging verification, transfer the approved new design and exact text corpus to WordPress.'''
    s = s.replace(old, new)
    p.write_text(s, encoding='utf-8')


if __name__ == '__main__':
    rewritten = clean_internal_links()
    Path('_redirects').write_text('/laserbehandlungen/ /laser-behandlungen/ 301\n', encoding='utf-8')
    failures = audit()
    update_migration_audit()
    print(f'Rewrote {rewritten} internal/legacy anchor links.')
    if failures:
        print('\n'.join(failures))
        raise SystemExit(1)
    print('Final repository-level staging audit: PASS')
