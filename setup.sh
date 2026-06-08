#!/usr/bin/env bash
#
# setup.sh — registers task-tracker.php as the 'task-tracker' command
#
# Creates an alias in ~/.bashrc pointing to this project. Since the storage
# uses __DIR__/data, the data always lives in the project folder, regardless
# of where the command is run from.

set -euo pipefail

# --- Output colors ---------------------------------------------------------
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BOLD='\033[1m'
NC='\033[0m' # no color

info()  { echo -e "${GREEN}==>${NC} $*"; }
warn()  { echo -e "${YELLOW}==>${NC} $*"; }
error() { echo -e "${RED}==>${NC} $*" >&2; }

# --- Resolve absolute paths ------------------------------------------------
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP_SCRIPT="$SCRIPT_DIR/task-tracker.php"
ALIAS_NAME="task-tracker"
BASHRC="$HOME/.bashrc"

# --- Prerequisites ----------------------------------------------------------
if ! command -v php >/dev/null 2>&1; then
    error "PHP was not found in PATH. Please install PHP before continuing."
    error "  e.g.: sudo apt install php-cli"
    exit 1
fi

if [[ ! -f "$PHP_SCRIPT" ]]; then
    error "File not found: $PHP_SCRIPT"
    exit 1
fi

# --- Build the alias line ---------------------------------------------------
ALIAS_LINE="alias ${ALIAS_NAME}='php \"${PHP_SCRIPT}\"'"
MARKER="# >>> task-tracker setup >>>"
MARKER_END="# <<< task-tracker setup <<<"

# --- Idempotency: remove old block if it exists -----------------------------
if grep -qF "$MARKER" "$BASHRC" 2>/dev/null; then
    warn "Previous task-tracker config found in $BASHRC — updating."
    # Remove the block between the markers
    sed -i "/$MARKER/,/$MARKER_END/d" "$BASHRC"
fi

# --- Write the new block ----------------------------------------------------
{
    echo ""
    echo "$MARKER"
    echo "$ALIAS_LINE"
    echo "$MARKER_END"
} >> "$BASHRC"

info "Alias '${BOLD}${ALIAS_NAME}${NC}' added to ${BOLD}${BASHRC}${NC}"
echo ""
echo -e "To start using the command ${BOLD}now${NC}, run:"
echo ""
echo -e "    ${BOLD}source ~/.bashrc${NC}"
echo ""
echo -e "Then use it from any directory, for example:"
echo ""
echo -e "    ${BOLD}${ALIAS_NAME} help${NC}"
echo -e "    ${BOLD}${ALIAS_NAME} add \"My task\" \"My description\"${NC}"
echo ""
