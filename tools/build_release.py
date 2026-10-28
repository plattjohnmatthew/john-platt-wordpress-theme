"""Build a WordPress-installable ZIP from clean committed source. Python stdlib only."""
from pathlib import Path, PurePosixPath
import hashlib
import io
import re
import subprocess
import zipfile
from check_public import check

ROOT = Path(__file__).resolve().parents[1]


def git(*args):
    return subprocess.check_output(["git", "-C", str(ROOT), *args])


def main():
    check(ROOT)
    if git("status", "--porcelain").strip():
        raise SystemExit("Commit or resolve changes before building a release.")
    revision = git("rev-parse", "HEAD").decode().strip()
    metadata = git("show", "HEAD:john-platt/style.css").decode()
    version = re.search(r"^Version:\s*(\S+)", metadata, re.M).group(1)
    archive = git("archive", "--format=zip", "HEAD", "john-platt", "docs", "README.md", "LICENSE", "NOTICE.md")
    expected = {}
    with zipfile.ZipFile(io.BytesIO(archive)) as source:
        for entry in source.infolist():
            if entry.is_dir():
                continue
            name = entry.filename
            target = name if name.startswith("john-platt/") else "john-platt/" + name
            parts = PurePosixPath(target).parts
            if ".." in parts or any(p in {".git", "uploads", "tests", "tools", "node_modules"} for p in parts):
                raise SystemExit("Unexpected release path: " + target)
            if target in expected:
                raise SystemExit("Duplicate release path: " + target)
            expected[target] = source.read(entry)
    for required in ("style.css", "functions.php", "index.php", "LICENSE", "README.md", "docs/GETTING-STARTED.md"):
        if "john-platt/" + required not in expected:
            raise SystemExit("Missing required file: " + required)
    output = ROOT / "dist"
    output.mkdir(exist_ok=True)
    destination = output / "john-platt-theme.zip"
    with zipfile.ZipFile(destination, "w", zipfile.ZIP_DEFLATED, compresslevel=9) as packed:
        for name, data in sorted(expected.items()):
            info = zipfile.ZipInfo(name, (2026, 1, 1, 0, 0, 0))
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o100644 << 16
            packed.writestr(info, data)
    with zipfile.ZipFile(destination) as packed:
        if set(packed.namelist()) != set(expected) or any(packed.read(k) != v for k, v in expected.items()):
            raise SystemExit("ZIP verification failed.")
    if git("rev-parse", "HEAD").decode().strip() != revision or git("status", "--porcelain").strip():
        raise SystemExit("Source changed while packaging; do not publish this output.")
    digest = hashlib.sha256(destination.read_bytes()).hexdigest()
    (output / "SHA256SUMS.txt").write_text(digest + "  john-platt-theme.zip\n", encoding="utf-8")
    print(f"Verified v{version}: {len(expected)} files, {destination.stat().st_size} bytes; commit {revision}")
    print(destination)


if __name__ == "__main__":
    main()
