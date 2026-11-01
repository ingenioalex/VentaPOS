#!/usr/bin/env python3
"""Exporta el transcript de Cursor a Markdown (solo mensajes, sin secretos)."""
from __future__ import annotations

import json
import re
from pathlib import Path

SRC = Path(
    r"C:\Users\Alex\.cursor\projects\d-Proyectos-VentaPOS-build"
    r"\agent-transcripts\89fc26d9-47ea-4261-97b6-4812db4435f2"
    r"\89fc26d9-47ea-4261-97b6-4812db4435f2.jsonl"
)
OUT_DIR = Path(__file__).resolve().parents[1] / "docs" / "ventasagil"
OUT = OUT_DIR / "CHAT-CURSOR-2026.md"

SECRET_RES = [
    (re.compile(r"vl110185", re.I), "[DB_PASSWORD]"),
    (re.compile(r"base64:[A-Za-z0-9+/=]{20,}"), "[APP_KEY]"),
    (re.compile(r"gho_[A-Za-z0-9_]+"), "[GITHUB_TOKEN]"),
    (re.compile(r"dns_hostinger_api_token\s*=\s*\S+", re.I), "dns_hostinger_api_token = [TOKEN]"),
    (re.compile(r"HOSTINGER_API_TOKEN[=:][^\s\"']+", re.I), "HOSTINGER_API_TOKEN=[TOKEN]"),
    (re.compile(r"Bearer\s+[A-Za-z0-9._\-]+"), "Bearer [TOKEN]"),
    (re.compile(r"223305@dey@", re.I), "[ADMIN_PANEL_PATH]"),
    (re.compile(r"laravel-session=[^;\s]+"), "laravel-session=[COOKIE]"),
    (re.compile(r"XSRF-TOKEN=[^;\s]+"), "XSRF-TOKEN=[COOKIE]"),
]


def sanitize(text: str) -> str:
    for pat, repl in SECRET_RES:
        text = pat.sub(repl, text)
    return text


def extract_user_query(text: str) -> str:
    m = re.search(r"<user_query>\s*(.*?)\s*</user_query>", text, re.S)
    return (m.group(1) if m else text).strip()


def texts_from_message(msg: dict) -> list[str]:
    content = msg.get("message", {}).get("content")
    if isinstance(content, str):
        return [content]
    out = []
    if isinstance(content, list):
        for part in content:
            if not isinstance(part, dict):
                continue
            if part.get("type") == "text" and part.get("text"):
                t = part["text"].strip()
                if t and t != "[REDACTED]":
                    out.append(t)
    return out


def main() -> None:
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    lines_out = [
        "# Chat Cursor — VentasÁgil / VentaPOS",
        "",
        "Exportación de la conversación de Cursor (hilo `89fc26d9-47ea-4261-97b6-4812db4435f2`).",
        "",
        "- Solo mensajes de usuario y respuestas del asistente (sin llamadas internas a herramientas).",
        "- Secretos (claves, tokens, cookies) sustituidos por marcadores.",
        "- Fecha de exportación: 2026-11-01.",
        "",
        "---",
        "",
    ]
    n_user = n_as = 0
    pending_as: list[str] = []

    def flush_assistant() -> None:
        nonlocal n_as
        if not pending_as:
            return
        n_as += 1
        body = "\n\n".join(pending_as).strip()
        body = re.sub(r"\n\[REDACTED\]\n?", "\n", body)
        body = sanitize(body)
        if body:
            lines_out.append("### Asistente")
            lines_out.append("")
            lines_out.append(body)
            lines_out.append("")
        pending_as.clear()

    with SRC.open(encoding="utf-8") as f:
        for raw in f:
            raw = raw.strip()
            if not raw:
                continue
            try:
                obj = json.loads(raw)
            except json.JSONDecodeError:
                continue
            role = obj.get("role")
            chunks = texts_from_message(obj)
            if not chunks:
                continue
            if role == "user":
                flush_assistant()
                q = extract_user_query("\n".join(chunks))
                q = sanitize(q)
                if not q:
                    continue
                n_user += 1
                lines_out.append(f"## Usuario ({n_user})")
                lines_out.append("")
                lines_out.append(q)
                lines_out.append("")
            elif role == "assistant":
                for t in chunks:
                    if t.startswith("{") and '"type": "tool' in t:
                        continue
                    pending_as.append(t)
    flush_assistant()

    lines_out.append("---")
    lines_out.append("")
    lines_out.append(f"Mensajes de usuario: **{n_user}**. Bloques de respuesta: **{n_as}**.")
    lines_out.append("")
    OUT.write_text("\n".join(lines_out), encoding="utf-8")
    print(f"Wrote {OUT} ({OUT.stat().st_size} bytes) users={n_user} assistants={n_as}")


if __name__ == "__main__":
    main()
