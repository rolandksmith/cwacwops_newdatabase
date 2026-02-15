---
name: db-check
description: Run queries against the CWA MySQL database in the Docker development environment. Use for verifying schema, checking data, and debugging.
allowed-tools: Bash(docker *), Bash(mysql *)
argument-hint: [query or table name]
---

# Database Check

Run queries against the CWA MySQL database in the Docker development environment.

## Connecting

```
docker exec -i mysql mysql -u cwacwops_wp540 -pcwacwops cwacwops_wp540 -e "SQL_HERE;"
```

- **Container:** `mysql`
- **User:** `cwacwops_wp540`
- **Password:** `cwacwops`
- **Database:** `cwacwops_wp540`

If the container is not running, tell the user to start Docker first.

## Argument Handling

If the user provides:
- **A table name** (e.g., `db-check advisor`): Show the table structure with `DESCRIBE wpw1_cwa_<table>` and a sample of rows with `SELECT * FROM wpw1_cwa_<table> LIMIT 5`
- **A SQL query**: Run it directly (read-only queries only — see Rules)
- **Nothing**: List all CWA tables with `SHOW TABLES LIKE 'wpw1_cwa_%'`

## Output Format

- Show results in a readable format
- For `DESCRIBE`, highlight the column name, type, and whether it's nullable
- For `SELECT`, show row count and the data
- For errors, show the MySQL error message and suggest fixes

## Rules

- **Read-only**: Only run SELECT, DESCRIBE, SHOW, and EXPLAIN queries
- **Never run** INSERT, UPDATE, DELETE, DROP, ALTER, TRUNCATE, or CREATE
- If the user asks for a write operation, tell them to use the application or a DAL class instead
- Always use the WordPress table prefix `wpw1_` for CWA tables
- If credentials fail, check `wp-config.php` for the correct values and tell the user
