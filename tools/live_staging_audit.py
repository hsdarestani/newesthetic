from concurrent.futures import ThreadPoolExecutor, as_completed
from pathlib import Path
from urllib.request import Request, build_opener, HTTPRedirectHandler
from urllib.error import HTTPError, URLError
from urllib.parse import urlsplit
import re
import time

BASE = 'https://newesthetic.pages.dev'
UA = 'APlus-Esthetic-Staging-Audit/1.0'
ROBOTS = re.compile(r'<meta\b(?=[^>]*\bname=["\']robots["\'])(?=[^>]*\bcontent=["\']noindex,nofollow["\'])[^>]*>', re.I)


def route_for(path: Path) -> str:
    if path == Path('index.html'):
        return '/'
    return '/' + path.parent.as_posix().strip('/') + '/'


def fetch(route: str, attempts: int = 3):
    url = BASE + route
    last = None
    for attempt in range(attempts):
        try:
            req = Request(url, headers={'User-Agent': UA, 'Cache-Control': 'no-cache'})
            with build_opener().open(req, timeout=20) as r:
                return r.getcode(), r.geturl(), r.read().decode('utf-8', errors='replace')
        except (HTTPError, URLError, TimeoutError) as e:
            last = e
            if attempt + 1 < attempts:
                time.sleep(2)
    raise last


class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


def fetch_redirect(route: str):
    req = Request(BASE + route, headers={'User-Agent': UA, 'Cache-Control': 'no-cache'})
    try:
        with build_opener(NoRedirect).open(req, timeout=20) as r:
            return r.getcode(), r.headers.get('Location', '')
    except HTTPError as e:
        return e.code, e.headers.get('Location', '')


def check_page(path: Path):
    route = route_for(path)
    expected_canonical = f'https://a-esthetic.de{route}'
    result = {'route': route, 'status': None, 'robots': False, 'canonical': False, 'error': ''}
    try:
        status, final_url, body = fetch(route)
        result['status'] = status
        result['robots'] = bool(ROBOTS.search(body))
        pats = [
            re.compile(r'<link\b[^>]*\brel=["\']canonical["\'][^>]*\bhref=["\']' + re.escape(expected_canonical) + r'["\'][^>]*>', re.I),
            re.compile(r'<link\b[^>]*\bhref=["\']' + re.escape(expected_canonical) + r'["\'][^>]*\brel=["\']canonical["\'][^>]*>', re.I),
        ]
        result['canonical'] = any(p.search(body) for p in pats)
        if final_url.rstrip('/') != (BASE + route).rstrip('/'):
            result['error'] = f'unexpected final URL: {final_url}'
    except Exception as e:
        result['error'] = f'{type(e).__name__}: {e}'
    return result


def main():
    pages = sorted(p for p in Path('.').rglob('index.html') if '.git' not in p.parts)
    results = []
    with ThreadPoolExecutor(max_workers=12) as pool:
        futures = {pool.submit(check_page, p): p for p in pages}
        for fut in as_completed(futures):
            results.append(fut.result())
    results.sort(key=lambda x: x['route'])

    failures = []
    for r in results:
        if r['status'] != 200 or not r['robots'] or not r['canonical'] or r['error']:
            failures.append(r)

    redir_status, redir_location = fetch_redirect('/laserbehandlungen/')
    redir_path = urlsplit(redir_location).path if redir_location else ''
    redir_ok = redir_status in (301, 302, 307, 308) and redir_path.rstrip('/') == '/laser-behandlungen'

    report = [
        '# A+ Esthetic — Live Staging Audit',
        '',
        f'- Live base: `{BASE}`',
        f'- Routes requested: **{len(results)}**',
        f'- HTTP 200: **{sum(1 for r in results if r["status"] == 200)}/{len(results)}**',
        f'- Live `noindex,nofollow`: **{sum(1 for r in results if r["robots"])}/{len(results)}**',
        f'- Exact production canonical: **{sum(1 for r in results if r["canonical"])}/{len(results)}**',
        f'- Legacy Laser redirect live: **{"PASS" if redir_ok else "FAIL"}** (`{redir_status}` → `{redir_location or "no Location"}`)',
        '',
        '## Route results',
        '',
        '| Route | HTTP | Robots | Canonical | Result |',
        '|---|---:|---|---|---|',
    ]
    for r in results:
        ok = r['status'] == 200 and r['robots'] and r['canonical'] and not r['error']
        report.append(f'| `{r["route"]}` | {r["status"] if r["status"] is not None else "ERR"} | {"PASS" if r["robots"] else "FAIL"} | {"PASS" if r["canonical"] else "FAIL"} | {"PASS" if ok else "FAIL"} |')
        if r['error']:
            report.append(f'<!-- {r["route"]}: {r["error"]} -->')

    report += ['', '## Result', '']
    if not failures and redir_ok:
        report.append('**PASS — deployed Cloudflare staging matches the repository-level staging guards.**')
        status_text = 'PASS\n'
    else:
        report.append('**FAIL — deployed staging still has one or more issues.**')
        status_text = 'FAIL\n'
        report += ['', '## Failures', '']
        for r in failures:
            report.append(f'- `{r["route"]}`: HTTP={r["status"]}, robots={r["robots"]}, canonical={r["canonical"]}, error={r["error"] or "none"}')
        if not redir_ok:
            report.append(f'- `/laserbehandlungen/`: status={redir_status}, Location={redir_location or "none"}')

    Path('LIVE-STAGING-AUDIT.md').write_text('\n'.join(report) + '\n', encoding='utf-8')
    Path('.live-audit-status').write_text(status_text, encoding='utf-8')
    print(status_text.strip())


if __name__ == '__main__':
    main()
