#!/usr/bin/env bash
# check-undeclared-tokens.sh
# CI guardrail: fail if any color-token utility class used in views/js refers
# to a nuance step NOT declared in resources/css/app.css @theme (D-06).
#
# In Tailwind v4 CSS-first mode, only declared @theme tokens emit utilities.
# An undeclared class (e.g. text-warn-900) emits zero CSS — a silent breakage.
#
# Brand/semantic token families checked:
#   sand, azure, navy, lagon, sun, ink, warn, success, danger, whatsapp
#
# Usage: bin/check-undeclared-tokens.sh
# Exit 0 = all classes declared. Exit 1 = undeclared classes found (list printed).

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CSS_FILE="$REPO_ROOT/resources/css/app.css"
VIEWS_DIR="$REPO_ROOT/resources/views"
JS_DIR="$REPO_ROOT/resources/js"

# -- 1. Build declared token set from @theme block ----------------------------
DECLARED=$(awk '/@theme \{/{inside=1} inside && /--color-[a-z]/{print} /^\}/{inside=0}' "$CSS_FILE" \
  | grep -oE -- '--color-[a-z]+-?[0-9]*:' \
  | sed 's/^--color-//; s/:$//' \
  | sort -u)

if [ -z "$DECLARED" ]; then
  echo "ERROR: could not parse @theme block from $CSS_FILE" >&2
  exit 2
fi

# -- 2. Token families to check -----------------------------------------------
FAMILIES="sand|azure|navy|lagon|sun|ink|warn|success|danger|whatsapp"

# -- 3. Search views and js for undeclared token utility classes --------------
FOUND_ISSUES=""

while IFS= read -r file; do
  lineno=0
  while IFS= read -r line; do
    lineno=$((lineno + 1))

    # Skip Blade comment lines
    case "$line" in *'{{--'*) continue ;; esac
    # Skip JS single-line comment lines (after trimming leading whitespace)
    stripped="${line#"${line%%[![:space:]]*}"}"
    case "$stripped" in '//'*) continue ;; esac

    # Extract (bg|text|border|ring)-(family)(-step)? matches, strip the prefix
    tokens=$(echo "$line" \
      | grep -oE "(bg|text|border|ring)-(${FAMILIES})(-[0-9]+)?" \
      | cut -d'-' -f2- \
      | sort -u 2>/dev/null || true)

    for token in $tokens; do
      # Strip opacity modifier if present (e.g. lagon-50/40 -> lagon-50)
      token_base="${token%%/*}"

      if echo "$DECLARED" | grep -qx "$token_base"; then
        continue
      fi
      FOUND_ISSUES="${FOUND_ISSUES}${file}:${lineno}: undeclared token — ${token_base}\n"
    done
  done < "$file"
done < <(find "$VIEWS_DIR" "$JS_DIR" -type f \( -name "*.blade.php" -o -name "*.js" \))

# -- 4. Report ----------------------------------------------------------------
if [ -n "$FOUND_ISSUES" ]; then
  echo ""
  echo "FAIL: undeclared Tailwind v4 token classes found (emit zero CSS):"
  echo ""
  printf "%b" "$FOUND_ISSUES"
  echo ""
  echo "Fix: add the missing nuance to @theme in resources/css/app.css,"
  echo "     or remap the class to a declared token."
  exit 1
fi

echo "OK: all color-token utility classes are declared in @theme."
