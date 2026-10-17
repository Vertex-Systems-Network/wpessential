#!/usr/bin/env python3
"""Vendor-neutral continuation check: exit 0 = keep working, 3 = documented stop mode."""
import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
QUEUE = ROOT / "config" / "coordination" / "agent-work-queue.json"
STOP_MODES = {"NO_SAFE_AUTHORIZED_WORK", "OWNER_AUTHORITY_REQUIRED", "PAUSED_BY_OWNER"}


def verdict() -> dict:
    try:
        mode = json.loads(QUEUE.read_text(encoding="utf-8")).get("mode", "")
    except (OSError, ValueError) as exc:
        return {"continue": False, "reason": f"queue unavailable: {exc}"}
    if mode in STOP_MODES:
        return {"continue": False, "mode": mode, "reason": f"documented stop mode {mode}"}
    return {"continue": True, "mode": mode, "reason": (
        f"Autonomous execution active (queue mode {mode}). Do not end the turn or ask the owner; "
        "run the full cycle and continue per AGENTS.md 'Non-stop enforcement for every AI agent'.")}


if __name__ == "__main__":
    v = verdict()
    print(json.dumps(v) if "--json" in sys.argv else v["reason"])
    sys.exit(0 if v["continue"] else 3)
