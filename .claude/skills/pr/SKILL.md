---
name: pr
description: Create a pull request for the current branch with a well-structured title and description.
allowed-tools: Bash(git *), Bash(gh *)
---

# Create Pull Request

Create a pull request for the current branch against `main`.

## Steps

1. **Gather context** — run these in parallel:
   - `git status` to check for uncommitted changes
   - `git log main..HEAD --oneline` to see all commits on this branch
   - `git diff main...HEAD --stat` to see the scope of changes
   - `git branch --show-current` to get the branch name
   - Check if branch is pushed: `git rev-parse --abbrev-ref @{upstream}` — if not, push with `git push -u origin HEAD`

2. **Warn about uncommitted changes** — if there are staged or unstaged changes, tell the user before proceeding.

3. **Draft the PR** based on ALL commits (not just the latest):
   - **Title**: Short (under 70 chars), starts with a verb, describes the overall change
   - **Body**: Use the template below

4. **Create the PR** using `gh pr create` with a HEREDOC for the body.

5. **Return the PR URL** to the user.

## PR Body Template

```
## Summary
<1-3 bullet points covering what changed and why>

## Test plan
<Bulleted checklist of testing steps relevant to the changes>

Generated with [Claude Code](https://claude.com/claude-code)
```

## Rules

- Always target `main` unless the user specifies otherwise
- Never force-push
- If the branch has no commits ahead of main, tell the user there's nothing to PR
- Include the full PR URL in the response so the user can click it
