#!/usr/bin/env bash
# Usage: ./set-github-user.sh <your-github-username> [repo-name]
# Replaces the YOUR_GITHUB_USER placeholder (and optionally the repo name) everywhere.
set -euo pipefail
USER_NAME="${1:?Usage: ./set-github-user.sh <github-username> [repo-name]}"
REPO="${2:-esteban-wp-portfolio}"
FILES=$(grep -rl --exclude=set-github-user.sh -e 'YOUR_GITHUB_USER' -e 'esteban-wp-portfolio' . --exclude-dir=.git || true)
for f in $FILES; do
  sed -i.bak -e "s/YOUR_GITHUB_USER/${USER_NAME}/g" -e "s/esteban-wp-portfolio/${REPO}/g" "$f" && rm "$f.bak"
done
echo "Updated: $FILES"
