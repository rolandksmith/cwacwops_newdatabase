# CW Academy — GitHub Branch Protection Rules

## Recommended Settings for the `main` Branch

Go to **Settings → Branches → Add branch protection rule** and set `main` as the
branch name pattern. Apply the following settings.

---

### 1. Require a Pull Request Before Merging

- **Required approving reviews: 1**
  Roland (lead developer) reviews all PRs before merge. With a four-person team
  this keeps quality high without creating a bottleneck that would exist with
  two required reviewers.

- **Dismiss stale pull request approvals when new commits are pushed: ON**
  If a developer pushes new commits after approval, the approval resets and
  Roland must re-review. This prevents "approve then sneak in changes."

- **Require review from Code Owners: OFF** (for now)
  With only four developers this adds overhead without benefit. Revisit if the
  team grows or the codebase splits into distinct ownership areas.

- **Require approval of the most recent reviewable push: ON**
  Ensures the final state of the PR is what was actually approved.

---

### 2. Require Status Checks to Pass Before Merging

- **Require branches to be up to date before merging: ON**
  Forces developers to merge or rebase against the latest `main` before their
  PR can be merged. Prevents "works on my branch" conflicts.

- **Required status checks:** Add these as you set them up:
  - Linting (PHP CodeSniffer or similar)
  - Any automated tests you add later

  Even if you don't have CI yet, turn this on and add checks later. It's easier
  to enable the setting now than to retrofit it.

---

### 3. Other Recommended Settings

| Setting | Value | Reason |
|---------|-------|--------|
| Require signed commits | OFF | Adds friction, not critical for a four-person team |
| Require linear history | OFF | Allow merge commits — they're clearer in git log for this project size |
| Include administrators | ON | Even Roland's commits go through PRs. Leads by example and provides an audit trail |
| Allow force pushes | OFF | Never on `main` — protects history |
| Allow deletions | OFF | Prevents accidental deletion of `main` |
| Restrict who can push to matching branches | Optional | If enabled, set to Roland only as a safety net |

---

### 4. Recommended Branch Naming Convention

All developers should follow this pattern:

| Prefix | Use |
|--------|-----|
| `feature/` | New functionality (e.g., `feature/advisor-crud-interface`) |
| `fix/` | Bug fixes (e.g., `fix/semester-date-calculation`) |
| `refactor/` | Code restructuring (e.g., `refactor/student-dal-security`) |
| `docs/` | Documentation only (e.g., `docs/update-claude-md`) |
| `hotfix/` | Urgent production fix (e.g., `hotfix/login-redirect-loop`) |

Keep names lowercase, use hyphens, and be descriptive. The branch name should
tell you what the PR is about without opening it.

---

### 5. Suggested Workflow Summary

```
main (protected)
  │
  ├── feature/advisor-email-system     (Developer A)
  ├── fix/mysql-port-mapping           (Developer B)
  ├── refactor/registration-classes    (Developer C)
  └── feature/user-master-crud         (Developer D)
```

1. Developer pulls latest `main`
2. Creates a branch following the naming convention
3. Works, commits, pushes to GitHub
4. Opens a Pull Request targeting `main`
5. Roland reviews, requests changes if needed
6. Developer addresses feedback, pushes updates
7. Roland approves and merges
8. Developer deletes the feature branch (GitHub can auto-delete after merge)

---

### 6. GitHub Settings for Auto-Cleanup

Under **Settings → General → Pull Requests**:

- **Allow squash merging: ON** — Produces a clean single commit on `main`
- **Allow merge commits: ON** — Useful when the full commit history matters
- **Allow rebase merging: OFF** — Avoids confusion for less experienced git users
- **Automatically delete head branches: ON** — Cleans up merged branches
