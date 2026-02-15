#!/usr/bin/env python3
"""
Generate Code Snippets JSON import files from PHP files.

Connects to the WordPress MySQL database to look up each derived snippet
name in the wpw1_snippets table. If a match is found (case-insensitive),
the existing database id is used in the JSON output. If not found, id is 0.

Usage:
    python3 generate_code_snippets_json.py file1.php file2.php ...
    python3 generate_code_snippets_json.py --file list_of_files.txt

The --file option reads filenames from a text file (one per line).

Database connection options:
    --db-host       MySQL host (default: 127.0.0.1)
    --db-port       MySQL port (default: 3074)
    --db-user       MySQL user (default: cwacwops_wp540)
    --db-password   MySQL password (default: cwacwops)
    --db-name       MySQL database (default: cwacwops_wp540)
    --no-db         Skip database lookup; all ids will be 0

Output: Creates <basename>.code-snippets.json for each input PHP file,
        ready for import into the WordPress Code Snippets plugin.
"""

import json
import datetime
import os
import sys
import re
import argparse

# Keywords that must be uppercase when they appear as the first word (prefix)
PREFIX_KEYWORDS = ['CLASS', 'FUNCTION', 'JAVASCRIPT', 'UTILITY', 'RKSTEST']

# Keywords that must be uppercase anywhere in the name
ALWAYS_KEYWORDS = ['CWA', 'DAL', 'CRUD']


def load_snippet_ids(db_host, db_port, db_user, db_password, db_name):
    """
    Connect to MySQL and return a dict mapping lowercase snippet name -> id
    from the wpw1_snippets table.
    """
    try:
        import pymysql
    except ImportError:
        print("WARNING: pymysql not installed. Run: pip3 install pymysql")
        print("         Falling back to id=0 for all snippets.")
        return None

    try:
        conn = pymysql.connect(
            host=db_host,
            port=db_port,
            user=db_user,
            password=db_password,
            database=db_name,
            connect_timeout=10,
        )
        cursor = conn.cursor()
        cursor.execute("SELECT id, name FROM wpw1_snippets")
        rows = cursor.fetchall()
        cursor.close()
        conn.close()

        # Build lookup dict keyed by lowercase name for case-insensitive matching
        snippet_lookup = {}
        for row_id, row_name in rows:
            snippet_lookup[row_name.lower()] = row_id

        print(f"Database: loaded {len(snippet_lookup)} snippet names from wpw1_snippets")
        return snippet_lookup

    except Exception as e:
        print(f"WARNING: Could not connect to database: {e}")
        print("         Falling back to id=0 for all snippets.")
        return None


def generate_snippet_json(filepath, snippet_lookup=None, output_dir=None):
    """Generate a Code Snippets JSON file from a PHP file."""
    if not os.path.exists(filepath):
        return False, f"NOT FOUND: {filepath}"

    with open(filepath, 'r') as f:
        code = f.read()

    filename = os.path.basename(filepath)
    base = filename.replace('.php', '')

    # Convert filename to snippet name: replace _ and - with spaces, title case,
    # then enforce naming convention for keywords
    name = base.replace('_', ' ').replace('-', ' ').title()

    # Enforce uppercase for prefix keywords (first word only)
    for kw in PREFIX_KEYWORDS:
        m = re.match(r'^(' + kw + r')\b', name, re.IGNORECASE)
        if m and m.group() != kw:
            name = kw + name[m.end():]

    # Enforce uppercase for always-uppercase keywords (any position)
    for kw in ALWAYS_KEYWORDS:
        name = re.sub(r'\b' + kw + r'\b', kw, name, flags=re.IGNORECASE)

    # Look up the derived name in the database
    snippet_id = 0
    db_status = "NEW"
    if snippet_lookup is not None:
        found_id = snippet_lookup.get(name.lower())
        if found_id is not None:
            snippet_id = found_id
            db_status = f"id={snippet_id}"

    now_date = datetime.datetime.now().strftime('%Y-%m-%d %H:%M')
    now_ts = datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')

    snippet = {
        'generator': 'Code Snippets v3.9.5',
        'date_created': now_date,
        'snippets': [
            {
                'id': snippet_id,
                'name': name,
                'code': code,
                'active': True,
                'modified': now_ts,
                'revision': '1'
            }
        ]
    }

    out_name = base + '.code-snippets.json'
    if output_dir:
        out_path = os.path.join(output_dir, out_name)
    else:
        out_path = os.path.join(os.path.dirname(filepath), out_name)

    with open(out_path, 'w') as f:
        json.dump(snippet, f)

    # Validate the output
    with open(out_path, 'r') as f:
        json.load(f)

    return True, f'OK: {out_name} [{db_status}] (snippet: "{name}")'


def main():
    parser = argparse.ArgumentParser(
        description='Generate Code Snippets JSON import files from PHP files.'
    )
    parser.add_argument(
        'php_files',
        nargs='*',
        help='PHP files to convert'
    )
    parser.add_argument(
        '--file', '-f',
        dest='list_file',
        help='Text file containing PHP filenames (one per line)'
    )
    parser.add_argument(
        '--output-dir', '-o',
        dest='output_dir',
        help='Directory for output JSON files (default: same as input)'
    )
    parser.add_argument(
        '--db-host',
        dest='db_host',
        default='127.0.0.1',
        help='MySQL host (default: 127.0.0.1)'
    )
    parser.add_argument(
        '--db-port',
        dest='db_port',
        type=int,
        default=3074,
        help='MySQL port (default: 3074)'
    )
    parser.add_argument(
        '--db-user',
        dest='db_user',
        default='cwacwops_wp540',
        help='MySQL user (default: cwacwops_wp540)'
    )
    parser.add_argument(
        '--db-password',
        dest='db_password',
        default='cwacwops',
        help='MySQL password (default: cwacwops)'
    )
    parser.add_argument(
        '--db-name',
        dest='db_name',
        default='cwacwops_wp540',
        help='MySQL database (default: cwacwops_wp540)'
    )
    parser.add_argument(
        '--no-db',
        dest='no_db',
        action='store_true',
        help='Skip database lookup; all ids will be 0'
    )

    args = parser.parse_args()

    files = []
    if args.list_file:
        with open(args.list_file, 'r') as f:
            files = [line.strip() for line in f if line.strip()]
    if args.php_files:
        files.extend(args.php_files)

    if not files:
        parser.print_help()
        sys.exit(1)

    # Load snippet IDs from database
    snippet_lookup = None
    if not args.no_db:
        snippet_lookup = load_snippet_ids(
            args.db_host,
            args.db_port,
            args.db_user,
            args.db_password,
            args.db_name
        )

    success_count = 0
    error_count = 0
    matched_count = 0
    unmatched_count = 0
    errors = []
    unmatched = []

    for filename in files:
        ok, msg = generate_snippet_json(filename, snippet_lookup, args.output_dir)
        if ok:
            success_count += 1
            if '[NEW]' in msg:
                unmatched_count += 1
                unmatched.append(msg)
            else:
                matched_count += 1
            print(msg)
        else:
            error_count += 1
            errors.append(msg)

    print(f"\n=== RESULTS ===")
    print(f"Successfully created: {success_count}")
    print(f"  Matched in DB:      {matched_count}")
    print(f"  Not found (id=0):   {unmatched_count}")
    print(f"Errors:               {error_count}")
    if errors:
        print("\n--- ERRORS ---")
        for e in errors:
            print(e)
    if unmatched:
        print("\n--- NOT FOUND IN DATABASE (id=0) ---")
        for u in unmatched:
            print(u)


if __name__ == '__main__':
    main()
