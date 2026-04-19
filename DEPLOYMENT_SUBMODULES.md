Here is your complete documentation in **English, Markdown format**, ready to be saved as a file (e.g. `DEPLOYMENT_SUBMODULES.md`).

---

````markdown
# wconditions — Production Deployment Guide (with Git Submodules)

## 🎯 Purpose

This document describes the correct procedure to:

- Update individual submodules
- Propagate changes to the `wconditions` parent repository
- Deploy a consistent and reproducible version in production

The goal is to ensure that **all components (parent + submodules) are always version-aligned**.

---

# 🧭 1. Development workflow (inside a submodule)

Each submodule evolves independently.

## Example: modifying `weatherDBscripts`

```bash
cd weatherDBscripts
git checkout -b feature/rain-fix

# make changes
git commit -am "Fix rain anomaly handling"
git push origin feature/rain-fix
````

---

## 📌 1.1 Stabilize a version (recommended)

When ready for production:

```bash
git checkout main
git pull
git tag -a v1.2.0 -m "Stable rain correction release"
git push origin v1.2.0
```

> Tags are strongly recommended to ensure reproducibility.

---

# 🧭 2. Updating the parent repository (wconditions)

Go to the main repository:

```bash
cd wconditions
```

---

## 📌 2.1 Update submodules to latest versions

### Option A (recommended for controlled environments)

Manually checkout stable tags:

```bash
cd weatherDBscripts
git checkout v1.2.0
cd ..
```

### Option B (track latest remote branch)

```bash
git submodule update --remote --merge
```

---

## 📌 2.2 Verify changes

```bash
git status
git diff
```

You should see:

```text
modified: weatherDBscripts (new commits)
```

---

## 📌 2.3 Commit updated submodule pointers

This step is critical: it locks the exact submodule versions.

```bash
git add .
git commit -m "Update submodules to latest stable versions"
```

---

## 📌 2.4 Create a global release tag (recommended)

```bash
git tag -a v1.3 -m "wconditions release v1.3 (submodules synchronized)"
git push origin v1.3
```

This tag represents a **fully consistent system state**.

---

# 🚀 3. Production deployment procedure

On the production server:

```bash
cd /prod/wconditions
```

---

## 📌 3.1 Fetch latest tags and commits

```bash
git fetch --all --tags
```

---

## 📌 3.2 Checkout the desired release

```bash
git checkout v1.3
```

> This puts the repository in a **detached HEAD state (expected)**

---

## 📌 3.3 Synchronize submodules

```bash
git submodule sync --recursive
git submodule update --init --recursive
```

This ensures all submodules match the exact commits defined by the tag.

---

## 📌 3.4 Verify consistency

```bash
git submodule status
```

✔ Correct state = **no leading `+` symbols**

Example:

```text
f3d0d29 WeatherAlerts
9370abd common
0afac78 weatherDBscripts
```

---

## 📌 3.5 Functional validation

Run a quick validation:

```bash
python weatherDBscripts/src/WC_RealTimeUpdDB.py --dry-run
```

or full test suite:

```bash
pytest
```

---

# 🔁 4. Summary workflow

## 🔧 Submodule lifecycle

```text
development → commit → tag (optional but recommended)
```

## 📦 Parent repository (wconditions)

```text
update submodules → commit pointers → create global tag
```

## 🚀 Production deployment

```text
fetch tags → checkout release → sync submodules → validate
```

---

# ⚠️ Important rules

## ❌ Never in production

* `git pull` without checking out a release tag
* running mismatched submodule commits
* partial submodule updates

## ✅ Always

* deploy using a **global `wconditions` tag**
* ensure submodules are explicitly synchronized
* verify `git submodule status`

---

# 🧠 Best practice (strongly recommended)

Add this file to the repository:

```
DEPLOYMENT_SUBMODULES.md
```

It helps ensure consistent operations across teams and future maintenance.

---

# 🚀 Optional automation (recommended)

You can later automate deployment with:

```bash
git fetch --all --tags \
&& git checkout vX.Y \
&& git submodule update --init --recursive
```

---

