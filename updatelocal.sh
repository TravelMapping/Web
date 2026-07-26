#!/usr/bin/env bash
#
# Copy all web files locally from Web repository to a target web root directory
# Usage: ./install-local.sh /web/highways
#
set -e
shopt -s nullglob

TARGET_DIR="$1"

if [ -z "$TARGET_DIR" ]; then
    echo "Error: Target directory required. Usage: $0 <target_dir>"
    exit 1
fi

# Include all optional directories directly in the copy list
all_dirs="user lib devel devel/manual hb css graphs shields wptedit fonts"

echo "Installing Web assets into local directory: $TARGET_DIR"

# 1. Create target base and subdirectories
mkdir -p "$TARGET_DIR"
for dir in $all_dirs; do
    mkdir -p "$TARGET_DIR/$dir"
done

# 2. Copy root level files
cp *.php favicon.* "$TARGET_DIR/" 2>/dev/null || true

# 3. Copy files for each subdirectory
for dir in $all_dirs; do
    if [ -d "$dir" ]; then
        cp "$dir"/*.{php,js,svg,css,png,gif,ttf,woff,woff2,html,eot,txt,csv} "$TARGET_DIR/$dir/" 2>/dev/null || true
    fi
done

# 4. Create/re-create shields cache directory
if [ -d "$dir/shields/cache" ]; then
    rm -rf $dir/shields/cache
fi
mkdir $dir/shields/cache
chmod 777 $dir/shields/cache

echo "Local web deployment to $TARGET_DIR complete."
