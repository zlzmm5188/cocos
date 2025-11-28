#!/bin/bash

# Cleanup script - Archives large binary and backup files
# This script is safe and reversible - files are moved, not deleted
# Usage: ./cleanup.sh [--dry-run]

set -e

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
BACKUP_DIR="$REPO_ROOT/cleanup_backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
LOG_FILE="$BACKUP_DIR/cleanup_log_$TIMESTAMP.txt"

# Common exclusion patterns for find commands
EXCLUSION_ARGS=(-not -path "$BACKUP_DIR/*" -not -path "*/.git/*")

# Parse arguments
DRY_RUN=false
if [[ "$1" == "--dry-run" ]]; then
    DRY_RUN=true
    echo "=== DRY RUN MODE - No files will be moved ==="
fi

# Initialize
mkdir -p "$BACKUP_DIR"

# Track counts (global for use in functions)
archived_count=0
archived_size=0

log() {
    local message="$1"
    echo "$message"
    if [[ "$DRY_RUN" == false ]]; then
        echo "$(date '+%Y-%m-%d %H:%M:%S') - $message" >> "$LOG_FILE"
    fi
}

get_file_size() {
    local file="$1"
    stat -c%s "$file" 2>/dev/null || stat -f%z "$file" 2>/dev/null || echo 0
}

archive_file() {
    local file="$1"
    local category="$2"
    
    if [[ ! -f "$file" ]]; then
        return
    fi
    
    local relative_path="${file#$REPO_ROOT/}"
    local dest_dir="$BACKUP_DIR/$category/$(dirname "$relative_path")"
    local dest_file="$dest_dir/$(basename "$file")"
    local size
    size=$(get_file_size "$file")
    
    if [[ "$DRY_RUN" == true ]]; then
        log "[DRY-RUN] Would archive: $relative_path -> $category/"
    else
        mkdir -p "$dest_dir"
        mv "$file" "$dest_file"
        log "Archived: $relative_path -> $category/$(dirname "$relative_path")/$(basename "$file")"
    fi
    
    ((archived_count++)) || true
    ((archived_size+=size)) || true
}

archive_directory() {
    local dir="$1"
    local category="$2"
    
    if [[ ! -d "$dir" ]]; then
        return
    fi
    
    local relative_path="${dir#$REPO_ROOT/}"
    local dest_dir="$BACKUP_DIR/$category"
    
    if [[ "$DRY_RUN" == true ]]; then
        log "[DRY-RUN] Would archive directory: $relative_path -> $category/"
    else
        mkdir -p "$dest_dir"
        mv "$dir" "$dest_dir/"
        log "Archived directory: $relative_path -> $category/"
    fi
}

log "=========================================="
log "Repository Cleanup Script"
log "Started at: $(date)"
log "Repository root: $REPO_ROOT"
log "Backup directory: $BACKUP_DIR"
log "=========================================="

# 1. Archive large archive files (.zip, .tar.gz, .tar)
log ""
log "--- Archiving large archive files ---"
while IFS= read -r -d '' file; do
    size=$(get_file_size "$file")
    if [[ $size -gt 102400 ]]; then  # Greater than 100KB
        archive_file "$file" "archives"
    fi
done < <(find "$REPO_ROOT" -type f \( -name "*.zip" -o -name "*.tar.gz" -o -name "*.tar" \) "${EXCLUSION_ARGS[@]}" -print0 2>/dev/null)

# 2. Archive backup files (.bak, .backup)
log ""
log "--- Archiving backup files ---"
while IFS= read -r -d '' file; do
    archive_file "$file" "backup_files"
done < <(find "$REPO_ROOT" -type f \( -name "*.bak" -o -name "*.backup" \) "${EXCLUSION_ARGS[@]}" -print0 2>/dev/null)

# 3. Archive backup directories (backup/, backups/, js-backups-*)
log ""
log "--- Archiving backup directories ---"
for dir in "$REPO_ROOT/backup" "$REPO_ROOT/backups"; do
    if [[ -d "$dir" ]]; then
        archive_directory "$dir" "backup_dirs"
    fi
done

# Archive js-backups-* directories
while IFS= read -r -d '' dir; do
    archive_directory "$dir" "backup_dirs"
done < <(find "$REPO_ROOT" -maxdepth 1 -type d -name "js-backups-*" "${EXCLUSION_ARGS[@]}" -print0 2>/dev/null)

# 4. Archive files with backup patterns in name (excludes utility scripts)
log ""
log "--- Archiving files with backup patterns ---"
while IFS= read -r -d '' file; do
    # Skip shell scripts that are utilities for managing backups
    if [[ "$file" == *.sh ]]; then
        continue
    fi
    archive_file "$file" "backup_files"
done < <(find "$REPO_ROOT" -maxdepth 1 -type f \( -name "*-backup*" -o -name "*_backup*" -o -name "*.tokenbak" -o -name "*.puter-backup" \) "${EXCLUSION_ARGS[@]}" -print0 2>/dev/null)

log ""
log "=========================================="
log "Cleanup Summary"
log "=========================================="
log "Files archived: $archived_count"
log "Approximate size archived: $((archived_size / 1024)) KB"
log "Backup location: $BACKUP_DIR"
log ""
log "To restore files, copy them from $BACKUP_DIR back to their original locations."
log "See the log file at: $LOG_FILE"
log "=========================================="
log "Cleanup completed at: $(date)"

if [[ "$DRY_RUN" == true ]]; then
    echo ""
    echo "=== DRY RUN COMPLETE - No files were moved ==="
    echo "Run without --dry-run to actually archive files."
fi
