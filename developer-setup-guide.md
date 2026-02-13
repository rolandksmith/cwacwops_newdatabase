# CW Academy — Developer Setup and Workflow Guide

A step-by-step guide for CWA developers to set up their environment,
install Claude Code, and follow the team's development workflow.

---

## Table of Contents

1. [Prerequisites](#1-prerequisites)
2. [Clone the Repository](#2-clone-the-repository)
3. [Set Up the Docker Development Environment](#3-set-up-the-docker-development-environment)
4. [Install Claude Code](#4-install-claude-code)
5. [Daily Development Workflow](#5-daily-development-workflow)
6. [Using Claude Code Effectively](#6-using-claude-code-effectively)
7. [Pull Request Process](#7-pull-request-process)
8. [Troubleshooting](#8-troubleshooting)

---

## 1. Prerequisites

### All Platforms

- **Git** installed and configured with your GitHub credentials
- **Docker Desktop** installed and running
- **GitHub account** with access to the CWA repository
- **Claude subscription** (Pro at minimum, Max recommended for heavy use)
  - Sign up at https://claude.com/pricing

### Windows 11 Specific

- **WSL 2** (Windows Subsystem for Linux) — required for Claude Code
  - Open PowerShell as Administrator:
    ```powershell
    wsl --install
    ```
  - Restart your computer after installation
  - Docker Desktop should be configured to use WSL 2 backend
    (Settings → General → "Use the WSL 2 based engine")

### macOS Specific

1. **Xcode Command Line Tools** (required before Homebrew):
   ```bash
   xcode-select --install
   ```
2. **Homebrew** (https://brew.sh):
   ```bash
   /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
   ```
3. **Git** (current version via Homebrew):
   ```bash
   brew install git
   ```

---

## 2. Clone the Repository

### Setup your personal access token (if not already done)
- Go to your main GitHub page and click on your profile picture / icon in the upper right
- Scroll down and click on 'settings'
- Scroll down to the bottom of the menus displayed in the right panel
- Click on 'Developer Settings'
- Click on 'Personal Access Tokens' in the right panel menu
- Click on Tokens (classic)
- Click on 'Generate New Token' in the upper right of the page
- Click 'Generate New Token (classic)'
- Follow the instructions. BE SURE TO COPY YOUR NEW TOKEN!!!!!
- Navigate to your home directory
- Run the following command

On MacOS:
```Bash
git config --global credential.helper store
```

- Now clone the repository
```Bash
cd ~/projects    # macOS
cd /mnt/c/projects    # Windows (WSL)
git clone https://github.com/rolandksmith/cwacwops_newdatabse.git
Username: <your GitHub username>
Password: <paste your token here>
```

The repository will be cloned and your token stored. Git will use the stored token until it expires

### Verify you can see the project files

```
ls -la
```

You should see `CLAUDE.md`, and other project files.

---

## 3. Set Up the Docker Development Environment
**** NOTE **** The steps in this section should already be done
If not, refer to the instructions Roland sent on how to set up 
your development environment.

### Start the Containers

```bash
docker-compose up -d
```

This starts WordPress, MySQL, and phpMyAdmin. First run may take a few
minutes to pull images.

### Import the Database

Get the current database snapshot from the team (ask Roland for the latest
export file).

```bash
# Option A: Import via command line
docker exec -i <mysql-container-name> mysql -u root -p<password> <database> < snapshot.sql

# Option B: Import via phpMyAdmin
# Open http://localhost:8080 (or your configured port)
# Select the database, go to Import, upload the .sql file
```

### Verify the Site

Open your browser and go to http://localhost:8000 (or your configured port).
You should see the CWA site running with the Twenty Seventeen child theme.

### Check PHP Version

```bash
docker exec -it <wordpress-container-name> php -v
```

This must match the production PHP version. If it doesn't, update the
WordPress image version in `docker-compose.yml`.

### Common Port Configuration

If the default ports conflict with other services on your machine, edit
`docker-compose.yml`:

```yaml
ports:
  - "3073:80"     # WordPress (change 3073 to any free port)
  - "3307:3306"   # MySQL (change 3307 if 3306 is in use)
  - "8080:80"     # phpMyAdmin
```

After changing ports, restart:

```bash
docker-compose down
docker-compose up -d
```

---

## 4. Install Claude Code

### macOS

```bash
# Option A: Homebrew (recommended)
brew install claude-code

# Option B: Native installer
# Download from https://code.claude.com and follow the installer
```

### Windows 11 (run inside WSL)

```bash
# Option A: WinGet (from PowerShell, not WSL)
winget install Anthropic.ClaudeCode

# Option B: Inside WSL (Ubuntu)
# Download the native installer from https://code.claude.com
```

### First Run

```bash
# Navigate to the CWA project directory
cd ~/projects/cwacwops_newdatabase

# Start Claude Code
claude
```

On first run:
1. You'll be prompted to log in — follow the authentication flow
2. Claude Code will read the `CLAUDE.md` file automatically
3. You'll see a prompt where you can start typing natural language commands

### Verify Claude Code Sees the Project

Type this into the Claude Code prompt:

```
Describe the project structure and what this codebase does
```

Claude should respond with an accurate description of the CWA project based
on the files it reads and the `CLAUDE.md` context.

---

## 5. Daily Development Workflow

### Starting Your Day

```bash
# 1. Navigate to the project
cd ~/projects/cwacwops_newdatabase

# 2. Make sure you're on main and pull latest
git checkout main
git pull origin main

# 3. Start Docker if it's not running
docker-compose up -d

# 4. Create your feature branch
git checkout -b feature/your-feature-name

# 5. Start Claude Code
claude
```

### Branch Naming Convention

| Prefix       | Use                                    | Example                              |
|--------------|----------------------------------------|--------------------------------------|
| `feature/`   | New functionality                      | `feature/advisor-email-rework`       |
| `fix/`       | Bug fixes                              | `fix/semester-date-calculation`       |
| `refactor/`  | Code restructuring                     | `refactor/student-dal-security`       |
| `docs/`      | Documentation only                     | `docs/update-readme`                  |
| `hotfix/`    | Urgent production fix                  | `hotfix/login-redirect-loop`          |

### Making Changes

Work normally — edit files in your IDE, test in the browser, use Claude Code
for assistance. Commit regularly with descriptive messages:

```bash
git add -A
git commit -m "Add email verification check to advisor registration"
```

Or let Claude Code handle it:

```
commit these changes with a descriptive message
```

### Ending Your Day

Push your branch so your work is backed up on GitHub:

```bash
git push origin feature/your-feature-name
```

---

## 6. Using Claude Code Effectively

### The CLAUDE.md Advantage

The `CLAUDE.md` file in the project root is loaded automatically every
session. It tells Claude Code about:

- Project architecture and conventions
- Coding standards (prepared statements, OOP, naming)
- Security requirements
- The CRUD interface pattern
- Common pitfalls

You don't need to re-explain these things. Claude Code already knows them.

### Good Prompts for CWA Work

**Understanding code:**
```
Explain how CWA_Advisor_DAL handles semester transitions
```

**Writing new code:**
```
Create a new DAL class for managing announcements following the
existing CWA_Student_DAL pattern with prepared statements
```

**Debugging:**
```
I'm getting a MySQL syntax error on line 142 of CWA_Registration.php.
The error is: [paste error]. Find and fix the issue.
```

**Refactoring:**
```
Refactor this method to use prepared statements instead of
string concatenation for the SQL query
```

**Git operations:**
```
Create a commit for the changes I just made
```

```
Show me what's changed since my last commit
```

### Things Claude Code Can Do That Chat Cannot

- Read every file in the project without you pasting anything
- Edit multiple files in a single operation
- Run shell commands (linting, testing, git)
- Verify its own changes by running the code
- Trace a bug across multiple classes and files

### Tips

- **Be specific** — "Fix the bug" is worse than "Fix the SQL syntax error
  in the get_students_by_semester method in CWA_Student_DAL"
- **Let it read first** — Ask Claude to examine relevant files before making
  changes
- **Review the diffs** — Claude Code shows you what it changed. Read the
  diffs before accepting.
- **Use Escape to stop** — If Claude Code is going in the wrong direction,
  hit Escape (not Ctrl+C, which exits entirely)

---

## 7. Pull Request Process

### Creating a PR

```bash
# Make sure all changes are committed
git status

# Push your branch
git push origin feature/your-feature-name
```

Then on GitHub:
1. Go to the repository page
2. You'll see a "Compare & pull request" banner — click it
3. Fill in the PR description:
   - **What changed** — Brief summary of the changes
   - **Why** — The problem being solved or feature being added
   - **Testing** — How you verified the changes work
   - **Screenshots** — If there are UI changes, include before/after
4. Assign Roland as reviewer
5. Submit the PR

### What Roland Checks in Review

- Security: Prepared statements, input sanitization, role checks
- Pattern compliance: Follows existing DAL/class patterns
- Code quality: Clear naming, proper error handling, no dead code
- Functionality: Does what the PR description says it does

### Addressing Review Feedback

If Roland requests changes:

```bash
# Make sure you're on your feature branch
git checkout feature/your-feature-name

# Make the requested changes
# ... edit files ...

# Commit and push
git add -A
git commit -m "Address PR feedback: add input validation to form handler"
git push origin feature/your-feature-name
```

The PR updates automatically. Roland will re-review.

### After Merge

GitHub auto-deletes merged branches (configured in repo settings).
Update your local environment:

```bash
git checkout main
git pull origin main
# Your old branch is still local — clean it up
git branch -d feature/your-feature-name
```

---

## 8. Troubleshooting

### Docker Issues

**Containers won't start — port conflict:**
```bash
# Check what's using the port
# macOS/Linux:
lsof -i :3306
# Windows (PowerShell):
netstat -ano | findstr :3306
```
Fix: Change the port mapping in `docker-compose.yml`.

**Database connection refused:**
Check that the MySQL container is running:
```bash
docker-compose ps
```
If the MySQL container is restarting in a loop, check logs:
```bash
docker-compose logs mysql
```

**Changes not appearing in the browser:**
1. Hard refresh: `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)
2. Clear any WordPress caching plugin
3. Check that you're editing files in the correct directory (the one
   mounted into Docker, not a copy)

### Git Issues

**"Your branch is behind main":**
```bash
git checkout main
git pull origin main
git checkout feature/your-branch
git merge main
# Resolve any conflicts, then commit
```

**Accidentally committed to main:**
```bash
# Move the commit to a new branch
git branch feature/accidental-work
git reset --hard HEAD~1
git checkout feature/accidental-work
```

### Claude Code Issues

**Claude Code doesn't see my files:**
Make sure you started Claude Code from the project root directory:
```bash
cd ~/projects/cwa-project
claude
```

**Claude Code is making wrong assumptions:**
Check that `CLAUDE.md` is up to date. If Claude is doing something that
violates project conventions, remind it:
```
Check CLAUDE.md for our coding standards before making changes
```

**Hit usage limits:**
- Wait for the 5-hour reset window
- Consider upgrading from Pro to Max if this happens regularly
- Use Claude Code for complex multi-file tasks and your IDE for
  simple edits

### Windows-Specific Issues

**WSL and Docker not communicating:**
Ensure Docker Desktop has WSL integration enabled:
Settings → Resources → WSL Integration → Enable for your distro

**File permission issues in WSL:**
If files created by Docker have wrong permissions:
```bash
sudo chown -R $USER:$USER .
```

**Line ending issues (CRLF vs LF):**
Configure Git to handle this:
```bash
git config --global core.autocrlf input
```
This converts CRLF to LF on commit but doesn't modify files on checkout.

---

## Quick Reference Card

| Task | Command |
|------|---------|
| Start Docker | `docker-compose up -d` |
| Stop Docker | `docker-compose down` |
| Start Claude Code | `claude` (from project directory) |
| Pull latest main | `git checkout main && git pull` |
| Create branch | `git checkout -b feature/name` |
| Commit changes | `git add -A && git commit -m "message"` |
| Push branch | `git push origin feature/name` |
| Update branch from main | `git merge main` (while on your branch) |
| Check container status | `docker-compose ps` |
| View container logs | `docker-compose logs <service>` |
| Check PHP version | `docker exec -it <container> php -v` |
