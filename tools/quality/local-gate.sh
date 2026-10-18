#!/usr/bin/env bash
# Local-first gate (ADR-0223): run the same PHP checks CI runs, before any push.
# On success it stamps the tested HEAD so tools/git-hooks/pre-push allows that exact commit.
set -euo pipefail
cd "$(git rev-parse --show-toplevel)"

if [ -n "$(git status --porcelain=v1 --untracked-files=no)" ]; then
    echo "local-gate: commit or stash tracked changes first; the gate certifies a commit, not a dirty tree." >&2
    exit 1
fi

head="$(git rev-parse HEAD)"
echo "local-gate: verifying ${head}"

git diff --name-only --diff-filter=ACMR origin/main...HEAD -- '*.php' | while read -r file; do
    php -l "$file" >/dev/null
done

composer fast
composer quality:php

echo "$head" > "$(git rev-parse --git-dir)/wpe-local-gate-pass"
echo "local-gate: PASS ${head} — push allowed for this exact commit."
