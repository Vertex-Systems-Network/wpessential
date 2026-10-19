#!/usr/bin/env python3
"""Vendor-neutral continuation check: exit 0 = keep working, 3 = documented stop mode."""
import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
QUEUE = ROOT / "config" / "coordination" / "agent-work-queue.json"
STOP_MODES = {"NO_SAFE_AUTHORIZED_WORK", "OWNER_AUTHORITY_REQUIRED", "PAUSED_BY_OWNER"}
# Block a stop once per turn; a retried stop (stop_hook_active) is then allowed, so the hook
# never loops. Claude Code caps consecutive Stop-hook blocks at 9 on its own.
MAX_CONSECUTIVE_STOP_BLOCKS = 1
COUNTER = ROOT / ".claude" / ".stop-hook-blocks"


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


def stop_hook() -> int:
    """Claude Code Stop hook: block at most MAX_CONSECUTIVE_STOP_BLOCKS times in a row."""
    try:
        active = bool(json.load(sys.stdin).get("stop_hook_active", False))
    except (ValueError, AttributeError):
        active = False
    v = verdict()
    try:
        count = int(COUNTER.read_text()) if active else 0
    except (OSError, ValueError):
        count = 0
    if not v["continue"] or count >= MAX_CONSECUTIVE_STOP_BLOCKS:
        COUNTER.unlink(missing_ok=True)
        return 0
    COUNTER.parent.mkdir(exist_ok=True)
    COUNTER.write_text(str(count + 1))
    print(json.dumps({"decision": "block", "reason": v["reason"]}))
    return 0


if __name__ == "__main__":
    if "--stop-hook" in sys.argv:
        sys.exit(stop_hook())
    v = verdict()
    print(json.dumps(v) if "--json" in sys.argv else v["reason"])
    sys.exit(0 if v["continue"] else 3)
