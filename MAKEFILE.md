
It explains **how to use the main Makefile**, what each target does, the configuration logic, environment handling, and versioning principles.

---

## 📘 `Makefile.md` — Documentation for `wconditions` Build System

### 🧩 Overview

The `Makefile` at the root of the **wconditions** project provides a unified way to:

* install and configure all project submodules,
* generate secure configuration files,
* manage development or production environments,
* tag releases in Git, and
* perform maintenance (clean, update, etc.).

Each submodule (`weathermetrics`, `WeatherAlerts`, `weatherDBscripts`, etc.) can have its own `Makefile` defining local install logic.
The **root Makefile** acts as the central controller, coordinating all submodules.

---

### ⚙️ Global Structure

```
wconditions/
├── Makefile          # Main build system
├── VERSION           # Current version number
├── etc/              # Configuration templates and secrets
│   ├── db_config.json
│   ├── db_config.php
│   └── secrets.env
├── WeatherAlerts/
├── weathermetrics/
├── weatherDBscripts/
└── ...
```

---

### 🧠 Environment Variables and Configuration

The Makefile relies on a secrets file:

```
wconditions/etc/secrets.env
```

This file must contain your database credentials and any other sensitive environment variables used to generate configuration files.

Example:

```bash
# wconditions/etc/secrets.env
DB1_USER=meteo_user1
DB1_PASSWORD=securepassword1
DB2_USER=meteo_user2
DB2_PASSWORD=securepassword2
```

> ⚠️ **Do not commit `secrets.env`** to version control.
> This file is intentionally excluded from `.gitignore`.

---

### 🧩 Configuration Templates

Templates for configuration files are provided as:

* `db_config.json` → used by Python modules
* `db_config.php` → used by PHP web interfaces

Each contains placeholders in the form of `%%VAR_NAME%%`, which are dynamically replaced with values from `secrets.env` during installation.

Example (`db_config.json` template):

```json
{
  "db1": {
    "user": "%%DB1_USER%%",
    "password": "%%DB1_PASSWORD%%"
  },
  "db2": {
    "user": "%%DB2_USER%%",
    "password": "%%DB2_PASSWORD%%"
  }
}
```

During installation, these placeholders are replaced, and resulting local config files are generated:

```
/etc/wconditions/db_config.local.json
/etc/wconditions/db_config.local.php
```

---
Excellent point — yes, it **is** important to include that in the documentation.
Here’s why and how to do it properly 👇

---

### 🧭 Why it matters

1. **New contributors or users** won’t always have the repository locally — the first logical step is to clone it.
2. Your `Makefile.md` will likely be used both by:

   * **Developers**, who may clone specific tags or branches (for bug fixing or testing), and
   * **Deployers**, who may just want the **latest stable version**.
3. Adding a short note about **cloning the repository** ensures the Makefile workflow makes sense from the start.

---

````markdown
## Getting Started

Before using the Makefile, you need to clone the main repository:

```bash
git clone https://github.com/<your-org>/wconditions.git
cd wconditions
````

By default, this will clone the latest version (usually the `main` or `master` branch).

If you want to clone a specific version or release tag:

```bash
git clone --branch v1.2.3 https://github.com/<your-org>/wconditions.git
```

You can list available tags with:

```bash
git tag -l
```

---

### 🔧 Why mention tags

Including the tag cloning option:

* Helps reproduce **past releases** (for debugging or historical comparisons),
* Allows **controlled deployments** in production,
* Makes your CI/CD workflows clearer if they rely on Makefile targets.

---


### 🚀 Main Makefile Targets

#### `make install`

Installs **all modules** for **production** use.
This is the standard deployment command.

Steps:

1. Updates all Git submodules.
2. Generates secure local configuration files from templates.
3. Calls each submodule’s `Makefile` (if it exists) with `make install`.
4. Copies necessary files to `/opt/wconditions` and `/var/www/wconditions`.

Example:

```bash
make install
```

---

#### `make install-dev`

Same as `make install`, but optimized for **development**:

* May include debug utilities or non-essential scripts.
* Each submodule’s local `install-dev` target is executed instead of `install`.

Example:

```bash
make install-dev
```

---

#### `make install-config`

Only generates configuration files, without running any installation logic.
Useful when you’ve just updated credentials or want to refresh local config.

Example:

```bash
make install-config
```

Expected output:

```
✅ JSON configuration generated: /etc/wconditions/db_config.local.json
✅ PHP configuration generated: /etc/wconditions/db_config.local.php
🎉 Configuration installed successfully.
```

---

#### `make update`

Synchronizes and updates all Git submodules recursively.

Example:

```bash
make update
```

Output:

```
🔄 Updating git submodules...
✅ All submodules are up to date.
```

---

#### `make tag`

Creates and pushes a new **Git tag** based on the version number stored in the `VERSION` file.

Example:

```bash
echo "v1.4.2" > VERSION
make tag
```

Output:

```
🏷️  Tagging repository with version from VERSION...
✅ Tag v1.4.2 created and pushed.
```

---

#### `make clean`

Removes temporary or generated files (e.g., compiled Python caches and local config files).

Example:

```bash
make clean
```

Removes:

```
__pycache__ directories
*.pyc files
/etc/wconditions/db_config.local.*
```

---

### 🧩 Submodule Integration

Each submodule may include its own `Makefile`, supporting at least these targets:

| Target        | Description                            |
| ------------- | -------------------------------------- |
| `install`     | Installs the submodule for production  |
| `install-dev` | Installs the submodule for development |
| `clean`       | Cleans local build or temp files       |
| `version`     | Returns submodule version              |

Example in `weathermetrics/Makefile`:

```makefile
install:
	@echo "Installing weathermetrics module..."
	mkdir -p $(PREFIX)
	cp -r src/* $(PREFIX)/
	@echo "weathermetrics installed successfully."

install-dev:
	@echo "Installing weathermetrics (dev mode)..."
	mkdir -p $(PREFIX)
	cp -r src/* $(PREFIX)/
	cp -r test/* $(PREFIX)/tests/
```

The root Makefile automatically calls these sub-Makefiles when you run `make install` or `make install-dev`.

---

### 🧾 Versioning System

Each build is tagged using the Git tag or commit hash returned by:

```bash
git describe --tags --always --dirty
```

During installation, a `VERSION` file is written to `/opt/wconditions/VERSION`.

Example content:

```
v1.4.2-15-gd3e99e9
```

---

### 🧰 Typical Workflow

| Scenario                                  | Command               |
| ----------------------------------------- | --------------------- |
| Initial installation (production)         | `make install`        |
| Installation for local dev                | `make install-dev`    |
| Refresh only configs after secrets change | `make install-config` |
| Update submodules from remote repos       | `make update`         |
| Tag a new release                         | `make tag`            |
| Clean generated files                     | `make clean`          |

---

### 🧱 Directory Layout After Installation

```
/opt/wconditions/
├── VERSION
├── WeatherAlerts/
├── weathermetrics/
├── common/
└── weatherDBscripts/

/etc/wconditions/
├── db_config.local.json
├── db_config.local.php
└── secrets.env

/var/www/wconditions/
├── index.php
└── assets/
```

---

### 🧩 Extending the Makefile

If you add new submodules:

1. Add their folder name to the `SUBMODULES` list in the root `Makefile`.
2. Create a `Makefile` inside the submodule with `install` and `install-dev` targets.
3. Optionally include a local `VERSION` file or `README.md`.

Example:

```makefile
SUBMODULES = WeatherAlerts common dayswherecondition wcExportDaysCondition weatherDBscripts weathermetrics newModule
```

---

### ✅ Best Practices

* Always run `make update` after pulling new code.
* Keep `secrets.env` outside of version control.
* Use `make install-config` after changing credentials.
* Tag stable releases via `make tag`.
* Run `make clean` occasionally to remove obsolete artifacts.

---

### 🧾 License and Credits

This build framework was designed for the **wconditions** project to support modular installation, configuration security, and reproducible deployment.
Maintainer: **Pierre/Billoven**

---

