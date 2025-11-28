#!/bin/bash

# Cleanup script - Safe, Reversible Repository Cleanup
# This script archives large binary and backup files to cleanup_backups/
# preserving their original directory structure for easy restoration.

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
BACKUP_DIR="$REPO_ROOT/cleanup_backups"
LOG_FILE="$BACKUP_DIR/cleanup.log"

# File patterns to archive
BINARY_PATTERNS=("*.zip" "*.tar.gz" "*.tar" "*.tar.bz2" "*.7z" "*.rar")
BACKUP_PATTERNS=("*.bak" "*.backup" "*.old" "*~")

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging function
log() {
    local level="$1"
    shift
    local message="$*"
    local timestamp
    timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    
    # Only write to log file if it exists (i.e., init_backup_dir was called)
    if [[ -d "$BACKUP_DIR" ]]; then
        echo "[$timestamp] [$level] $message" >> "$LOG_FILE"
    fi
    
    case "$level" in
        ERROR)   echo -e "${RED}[$level] $message${NC}" ;;
        WARNING) echo -e "${YELLOW}[$level] $message${NC}" ;;
        SUCCESS) echo -e "${GREEN}[$level] $message${NC}" ;;
        *)       echo "[$level] $message" ;;
    esac
}

# Create backup directory and initialize log
init_backup_dir() {
    mkdir -p "$BACKUP_DIR"
    echo "=== Cleanup started at $(date) ===" >> "$LOG_FILE"
    log INFO "Backup directory: $BACKUP_DIR"
    log INFO "Repository root: $REPO_ROOT"
}

# Archive a single file while preserving directory structure
archive_file() {
    local file="$1"
    local relative_path
    relative_path="${file#"$REPO_ROOT"/}"
    local target_dir
    target_dir="$BACKUP_DIR/$(dirname "$relative_path")"
    local target_file
    target_file="$target_dir/$(basename "$file")"

    # Skip files already in cleanup_backups or .git
    if [[ "$relative_path" == cleanup_backups/* ]] || [[ "$relative_path" == .git/* ]]; then
        return 0
    fi

    # Create target directory
    mkdir -p "$target_dir"

    # Move file to backup location
    local mv_output
    if mv_output=$(mv "$file" "$target_file" 2>&1); then
        log SUCCESS "Archived: $relative_path -> cleanup_backups/$relative_path"
        return 0
    else
        log ERROR "Failed to archive: $relative_path - $mv_output"
        return 1
    fi
}

# Find and archive files matching patterns
archive_files_by_pattern() {
    local pattern="$1"
    local description="$2"
    local count=0

    log INFO "Searching for $description files matching: $pattern"

    while IFS= read -r -d '' file; do
        if archive_file "$file"; then
            count=$((count + 1))
        fi
    done < <(find "$REPO_ROOT" -type f -name "$pattern" ! -path "$REPO_ROOT/.git/*" ! -path "$BACKUP_DIR/*" -print0 2>/dev/null)

    if [[ $count -gt 0 ]]; then
        log INFO "Archived $count $description file(s) matching: $pattern"
    fi
}

# Main cleanup function for binary files
cleanup_binary_files() {
    log INFO "=== Archiving large binary files ==="
    for pattern in "${BINARY_PATTERNS[@]}"; do
        archive_files_by_pattern "$pattern" "binary"
    done
}

# Main cleanup function for backup files
cleanup_backup_files() {
    log INFO "=== Archiving backup files ==="
    for pattern in "${BACKUP_PATTERNS[@]}"; do
        archive_files_by_pattern "$pattern" "backup"
    done
}

# Show help
show_help() {
    cat << EOF
Usage: $(basename "$0") [OPTIONS]

Safe, reversible repository cleanup script.
Archives large binary and backup files to cleanup_backups/ directory.

OPTIONS:
    -h, --help      Show this help message
    -n, --dry-run   Show what would be archived without making changes
    -b, --binary    Archive only binary files (.zip, .tar.gz, etc.)
    -k, --backup    Archive only backup files (.bak, .backup, etc.)
    -r, --restore   Restore all files from cleanup_backups/

EXAMPLES:
    $(basename "$0")              # Archive all binary and backup files
    $(basename "$0") --dry-run    # Preview what would be archived
    $(basename "$0") --restore    # Restore all archived files

EOF
}

# Dry run mode - show what would be archived (no directories/files created)
dry_run() {
    echo "[INFO] === DRY RUN MODE - No files will be modified ==="
    
    echo ""
    echo "Files that would be archived:"
    echo "=============================="
    
    local count=0
    local relative_path
    
    # Check binary files
    for pattern in "${BINARY_PATTERNS[@]}"; do
        while IFS= read -r -d '' file; do
            relative_path="${file#"$REPO_ROOT"/}"
            if [[ "$relative_path" != cleanup_backups/* ]] && [[ "$relative_path" != .git/* ]]; then
                echo "  [BINARY] $relative_path"
                count=$((count + 1))
            fi
        done < <(find "$REPO_ROOT" -type f -name "$pattern" ! -path "$REPO_ROOT/.git/*" ! -path "$BACKUP_DIR/*" -print0 2>/dev/null)
    done
    
    # Check backup files
    for pattern in "${BACKUP_PATTERNS[@]}"; do
        while IFS= read -r -d '' file; do
            relative_path="${file#"$REPO_ROOT"/}"
            if [[ "$relative_path" != cleanup_backups/* ]] && [[ "$relative_path" != .git/* ]]; then
                echo "  [BACKUP] $relative_path"
                count=$((count + 1))
            fi
        done < <(find "$REPO_ROOT" -type f -name "$pattern" ! -path "$REPO_ROOT/.git/*" ! -path "$BACKUP_DIR/*" -print0 2>/dev/null)
    done
    
    echo ""
    echo "Total files that would be archived: $count"
    echo "Run without --dry-run to perform the actual cleanup."
}

# Restore files from backup
restore_files() {
    if [[ ! -d "$BACKUP_DIR" ]]; then
        log ERROR "Backup directory does not exist: $BACKUP_DIR"
        exit 1
    fi

    log INFO "=== Restoring files from cleanup_backups ==="
    local count=0
    local relative_path
    local target_dir
    local target_file

    while IFS= read -r -d '' file; do
        relative_path="${file#"$BACKUP_DIR"/}"
        target_dir="$REPO_ROOT/$(dirname "$relative_path")"
        target_file="$REPO_ROOT/$relative_path"

        # Skip log file
        if [[ "$relative_path" == "cleanup.log" ]]; then
            continue
        fi

        mkdir -p "$target_dir"

        local mv_output
        if mv_output=$(mv "$file" "$target_file" 2>&1); then
            log SUCCESS "Restored: $relative_path"
            count=$((count + 1))
        else
            log ERROR "Failed to restore: $relative_path - $mv_output"
        fi
    done < <(find "$BACKUP_DIR" -type f ! -name "cleanup.log" -print0 2>/dev/null)

    log INFO "Restored $count file(s)"
    
    # Clean up empty directories in backup
    find "$BACKUP_DIR" -type d -empty -delete 2>/dev/null || true
}

# Main entry point
main() {
    local mode="all"
    
    while [[ $# -gt 0 ]]; do
        case "$1" in
            -h|--help)
                show_help
                exit 0
                ;;
            -n|--dry-run)
                mode="dry-run"
                shift
                ;;
            -b|--binary)
                mode="binary"
                shift
                ;;
            -k|--backup)
                mode="backup"
                shift
                ;;
            -r|--restore)
                mode="restore"
                shift
                ;;
            *)
                log ERROR "Unknown option: $1"
                show_help
                exit 1
                ;;
        esac
    done

    cd "$REPO_ROOT"

    case "$mode" in
        dry-run)
            # Dry-run doesn't create any directories or files
            dry_run
            ;;
        restore)
            restore_files
            ;;
        binary)
            init_backup_dir
            cleanup_binary_files
            ;;
        backup)
            init_backup_dir
            cleanup_backup_files
            ;;
        all)
            init_backup_dir
            cleanup_binary_files
            cleanup_backup_files
            log SUCCESS "=== Cleanup completed successfully ==="
            log INFO "Files archived to: $BACKUP_DIR"
            log INFO "To restore, run: $(basename "$0") --restore"
            ;;
    esac
}

main "$@"
