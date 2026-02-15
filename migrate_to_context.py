#!/usr/bin/env python3
"""
Migrate PHP files from data_initialization_func() / $initializationArray
to CWA_Context::getInstance() / $context->property.

Usage:
    python3 migrate_to_context.py --dry-run   # preview changes
    python3 migrate_to_context.py             # apply changes
"""

import os
import re
import sys
import glob

# Files to skip entirely (already migrated or special)
SKIP_FILES = {
    'FUNCTION_data_initialization.php',
    'FUNCTION_data_initialization_function_wrapper.php',
    'student_and_advisor_assignments.php',
    'CLASS_CWA_Context.php',
}

# Files that need manual review (edge cases)
FLAG_FILES = {
    'daily_advisor_cron.php': 'writes to $initializationArray before reading',
    'daily_student_cron.php': 'writes to $initializationArray before reading',
    'manage_directory.php': 'uses flatFilePath which does not exist on CWA_Context',
    'display_initialization_array.php': 'calls data_initialization_func() with arguments and has nested call',
}

DRY_RUN = '--dry-run' in sys.argv

def migrate_file(filepath):
    """Apply regex replacements to a single PHP file. Returns (changed, flagged, reason)."""
    filename = os.path.basename(filepath)

    if filename in SKIP_FILES:
        return False, False, 'skipped'

    with open(filepath, 'r', encoding='utf-8', errors='replace') as f:
        original = f.read()

    # Only process files that actually use the old pattern
    if 'data_initialization_func' not in original and '$initializationArray' not in original:
        return False, False, 'no match'

    if filename in FLAG_FILES:
        return False, True, FLAG_FILES[filename]

    content = original

    # 1. Replace the initialization call
    #    $initializationArray = data_initialization_func();
    #    → $context = CWA_Context::getInstance();
    content = re.sub(
        r'\$initializationArray\s*=\s*data_initialization_func\(\s*\)\s*;',
        '$context = CWA_Context::getInstance();',
        content
    )

    # 2. Replace array accesses: $initializationArray['someKey'] → $context->someKey
    content = re.sub(
        r"\$initializationArray\['(\w+)'\]",
        r'$context->\1',
        content
    )
    content = re.sub(
        r'\$initializationArray\["(\w+)"\]',
        r'$context->\1',
        content
    )

    # 3. Replace print_r($initializationArray) → print_r($context->toArray())
    #    Handle both with and without the second argument
    content = re.sub(
        r'print_r\(\$initializationArray\)',
        'print_r($context->toArray())',
        content
    )
    content = re.sub(
        r'print_r\(\$initializationArray,\s*TRUE\)',
        'print_r($context->toArray(), TRUE)',
        content
    )

    # 4. Replace any bare $initializationArray references (e.g. in string interpolation or echo)
    #    that are NOT part of an assignment target on the left side
    #    e.g. echo "... $initializationArray ..." stays as context reference
    #    But skip assignment like $initializationArray = ...
    #    This catches leftover references like: "fudged the initializationArray"
    #    Those are string literals and shouldn't be changed, so we skip this.

    if content == original:
        return False, False, 'no changes needed'

    if not DRY_RUN:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)

    return True, False, 'migrated'


def main():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    php_files = glob.glob(os.path.join(base_dir, '*.php'))
    php_files.sort()

    migrated = []
    flagged = []
    skipped = []
    unchanged = []

    for filepath in php_files:
        changed, is_flagged, reason = migrate_file(filepath)
        filename = os.path.basename(filepath)

        if is_flagged:
            flagged.append((filename, reason))
        elif changed:
            migrated.append(filename)
        elif reason == 'skipped':
            skipped.append(filename)
        else:
            unchanged.append(filename)

    # Report
    mode = 'DRY RUN' if DRY_RUN else 'APPLIED'
    print(f'\n=== Migration Report ({mode}) ===\n')

    print(f'Migrated ({len(migrated)} files):')
    for f in migrated:
        print(f'  [OK] {f}')

    print(f'\nFlagged for manual review ({len(flagged)} files):')
    for f, reason in flagged:
        print(f'  [FLAG] {f} — {reason}')

    print(f'\nSkipped ({len(skipped)} files):')
    for f in skipped:
        print(f'  [SKIP] {f}')

    print(f'\nUnchanged ({len(unchanged)} files):')
    for f in unchanged:
        print(f'  [--] {f}')

    print(f'\nTotal: {len(migrated)} migrated, {len(flagged)} flagged, '
          f'{len(skipped)} skipped, {len(unchanged)} unchanged')


if __name__ == '__main__':
    main()
