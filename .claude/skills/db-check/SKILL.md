---
name: db-check
description: Run queries against the CWA MySQL database in the Docker development environment. Use for verifying schema, checking data, and debugging.
allowed-tools: Bash(docker *), Bash(mysql *)
argument-hint: [query or table name]
---

# Database Check

Run queries against the CWA MySQL database in the Docker development environment.

## Finding the Database Container

1. List running containers: `docker ps --format '{{.Names}}\t{{.Image}}\t{{.Ports}}'`
2. Identify the MySQL container (image name contains `mysql` or `mariadb`)
3. If no containers are running, tell the user to start Docker first: `docker-compose up -d`

## Connecting

Use `docker exec` to run MySQL commands inside the container:

```
docker exec -i <container_name> mysql -u root -p<password> <database_name>
```

Common credentials to try (check `wp-config.php` or `docker-compose.yml` for actual values):
- Look for `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST` in any `wp-config.php` in the project or Docker volumes
- Try: `docker exec -i <container> mysql -u root -proot`
- Try: `docker exec -i <container> mysql -u wordpress -pwordpress wordpress`

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
