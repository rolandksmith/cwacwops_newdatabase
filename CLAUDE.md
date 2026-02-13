# CLAUDE.md — CW Academy Project Context

This file is read by Claude Code at the start of every session. It provides
project context, coding standards, and architectural decisions so that Claude
can work effectively across the entire CWA codebase.

---

## Project Overview

CW Academy (CWA) is a WordPress-based web application that manages amateur
radio Morse code training programs. It handles student enrollment, advisor
management, semester scheduling, and automated email communications.

- **Platform:** WordPress (Twenty Seventeen child theme)
- **Language:** PHP (object-oriented, refactored from legacy procedural code)
- **Database:** MySQL (accessed via custom Data Access Layer classes)
- **Development environment:** Docker (cloned from production)
- **Repository:** GitHub with branch protection on `main`
- **Team:** Multiple developers (Roland is lead developer)

---

## Architecture

### Directory Structure

```
wp-content/themes/twentyseventeen_child/
├── style.css              # Child theme overrides + CWA custom styles
├── functions.php          # Theme functions, enqueues parent/child styles
├── vendor/                # Composer dependencies (autoloaded)
├── includes/
│   ├── classes/           # OOP business logic classes
│   ├── dal/               # Data Access Layer classes (CWA_*_DAL)
│   └── helpers/           # Utility/helper functions
└── templates/             # Page templates
```

### Data Access Layer (DAL) Pattern

All database access goes through `CWA_*_DAL` classes. These classes:
- Use `$wpdb` with **prepared statements** for all queries — no exceptions
- Return associative arrays or `false` on failure
- Handle their own error logging
- Are named after the entity they manage (e.g., `CWA_Student_DAL`,
  `CWA_Advisor_DAL`, `CWA_Semester_DAL`)

### Business Logic Classes

Separate from DAL classes. A business logic class may call one or more DAL
classes but never executes SQL directly. Example: the advisor registration
system was refactored from a monolithic 2000+ line file into:
- Registration logic class
- Action logging class
- Display/rendering class

### Action Logging

All significant user actions are logged through the centralized logging
system. Log entries include: user ID, action type, timestamp, and details.
Any new CRUD interface or workflow must integrate with this logging system.

---

## Coding Standards

### PHP

- **PHP version:** Target 7.4+ compatibility (production server constraint)
- **OOP:** All new code must be object-oriented with proper class structure
- **Prepared statements:** Always use `$wpdb->prepare()` — never concatenate
  user input into SQL strings
- **Naming:**
  - Classes: `CWA_Feature_Name` (e.g., `CWA_Student_DAL`)
  - Methods: `snake_case` (e.g., `get_student_by_id()`)
  - Variables: `$snake_case`
  - Constants: `UPPER_SNAKE_CASE`
- **Role-based access control:** Check user capabilities before any
  admin-only operations using WordPress role checks
- **Error handling:** Use try/catch blocks. Log errors. Never expose
  database errors to end users.
- **Input validation:** Validate and sanitize all user input at the point
  of entry using WordPress sanitization functions

### CSS

- **Child theme only:** Never modify the parent Twenty Seventeen `style.css`
- **Child theme structure:**
  - Sections 1-4: Theme overrides (layout, typography, forms)
  - Section 5: CWA-specific custom styles
- **CWA form classes:** Use `.formInputText`, `.formSelect`,
  `.formInputButton` for CWA application form elements
- **Naming:** Use descriptive hyphenated class names for new components
  (e.g., `.announcement-display-box`, `.hover-popup`)

### SQL / MySQL

- **Always use prepared statements** via `$wpdb->prepare()`
- **Prefix custom tables** with the WordPress table prefix
- **Schema changes** must be coordinated with the lead developer (Roland)
  before implementation — do not modify the database schema without approval
- **Date handling:** Semester transitions use specific date calculation logic.
  Verify edge cases around semester boundaries.

---

## Security Requirements

These are non-negotiable. Every PR will be checked against these rules.

1. **No SQL injection vectors** — Prepared statements everywhere
2. **No direct `$_GET` / `$_POST` usage** without sanitization
3. **Role-based access** on all admin-facing pages and AJAX handlers
4. **No credentials in code** — Use `wp-config.php` or environment variables
5. **CSRF protection** — Use WordPress nonces on all forms and AJAX calls
6. **No `eval()` or `exec()`** — ever

---

## CRUD Interface Pattern

When building new CRUD interfaces, follow the established pattern:

1. **List view** with search/filter functionality
2. **Add/Edit form** with validation and prepared statements
3. **Delete** with confirmation dialog and soft-delete where appropriate
4. **Admin-only access** via WordPress capability checks
5. **Action logging** for all create, update, and delete operations
6. **Success/error feedback** via WordPress admin notices

---

## Development Environment

All developers run a Docker-based environment that mirrors production:

- **Docker Compose** manages WordPress, MySQL, and phpMyAdmin containers
- **MySQL port mapping** may differ per developer — check `.env` or
  `docker-compose.yml` for local port
- **Database migrations** are coordinated through the lead developer
- **PHP version** in Docker should match production (verify before deploying)

### Getting Started

```bash
git clone <repo-url>
cd cwa-project
docker-compose up -d
# Import database snapshot (see team wiki for current snapshot location)
```

---

## Git Workflow

- **Branch from `main`** — always pull latest first
- **Branch naming:** `feature/`, `fix/`, `refactor/`, `docs/`, `hotfix/`
- **Commit messages:** Start with a verb, be specific
  - Good: `Add semester date validation to CWA_Semester_DAL`
  - Good: `Fix MySQL syntax error in advisor registration query`
  - Bad: `updates` / `fix stuff` / `WIP`
- **Pull Requests:** Required for all changes to `main`
  - Roland reviews all PRs
  - PR must be up to date with `main` before merge
  - Describe what changed and why in the PR description
- **Never force-push to `main`**

---

## Common Pitfalls

- **MySQL port conflicts:** If Docker MySQL won't start, check if another
  MySQL instance is using port 3306. Remap in `docker-compose.yml`.
- **PHP version mismatches:** Windows and Mac Docker images may default to
  different PHP versions. Check `php -v` inside the container.
- **WordPress caching:** If CSS or PHP changes don't appear, clear any
  caching plugin and hard-refresh the browser (`Ctrl+Shift+R` / `Cmd+Shift+R`).
- **Semester date logic:** The semester transition calculations have edge
  cases. Test with dates near semester boundaries.
- **The child theme's `style.css`** uses `filemtime()` cache-busting in
  `functions.php`. If styles aren't updating, the issue is server-side
  caching, not the enqueue.

---

## Key Contacts

- **Lead Developer:** Roland (all PRs, schema changes, architecture decisions)
- **Production deployments:** Coordinated through Roland
