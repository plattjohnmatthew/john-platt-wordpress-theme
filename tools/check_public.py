"""Basic public-distribution hygiene and local documentation-link checks."""
from pathlib import Path
import re


def check(root):
    theme = root / "john-platt"
    forbidden = re.compile(
        r"https?://[^\s\"'<>]+\.local\b|[A-Za-z]:[\\/]Users[\\/]|"
        r"gh[pousr]_[A-Za-z0-9]{20,}|github_pat_[A-Za-z0-9_]+|"
        r"-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----|G-[A-Z0-9]{6,}"
    )
    for path in theme.rglob("*"):
        if not path.is_file():
            continue
        if path.suffix.lower() in {".zip", ".sql", ".log", ".mp4", ".db", ".key", ".pem"}:
            raise SystemExit("Unexpected distribution artifact: " + str(path.relative_to(root)))
        if path.suffix in {".php", ".js", ".css", ".json"}:
            text = path.read_text(encoding="utf-8-sig")
            if forbidden.search(text):
                raise SystemExit("Possible private/local/tracking value: " + str(path.relative_to(root)))
            for address in re.findall(r"[\w.+-]+@([\w.-]+\.[A-Za-z]{2,})", text):
                if address.lower() not in {"example.com", "example.org", "example.net"}:
                    raise SystemExit("Non-placeholder email in theme: " + str(path.relative_to(root)))
    for path in root.rglob("*.md"):
        if any(part in {".git", "dist"} for part in path.relative_to(root).parts):
            continue
        for link in re.findall(r"\]\(([^)]+)\)", path.read_text(encoding="utf-8-sig")):
            if "://" in link or link.startswith(("#", "mailto:")):
                continue
            target = link.split("#", 1)[0]
            if target and not (path.parent / target).exists():
                raise SystemExit(f"Broken local documentation link in {path.name}: {link}")
    print("PASS: public theme hygiene and local documentation links.")


if __name__ == "__main__":
    check(Path(__file__).resolve().parents[1])
