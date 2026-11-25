#!/usr/bin/env bash
# Builds dist/shrishti-heritage.zip (macOS/Linux/Git Bash). Usage: ./tools/package-theme.sh
set -euo pipefail
cd "$(dirname "$0")/.."
mkdir -p dist && rm -f dist/shrishti-heritage.zip
(cd wordpress-theme && zip -rq ../dist/shrishti-heritage.zip shrishti-heritage)
echo "Created dist/shrishti-heritage.zip"
