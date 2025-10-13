# ============================================================
#  Makefile for wconditions project
#  Author: Pierre
#  Description:
#     Unified installation and deployment framework for all
#     wconditions components (WeatherAlerts, Metrics, DB, Web, etc.)
# ============================================================

# ------------------------------------------------------------
# Global Variables
# ------------------------------------------------------------
PROJECT_NAME = wconditions
VERSION_FILE = VERSION
BUILD_TAG    = $(shell git describe --tags --always --dirty)

# Installation paths
PREFIX       = /opt/$(PROJECT_NAME)
ETC_DIR      = /etc/$(PROJECT_NAME)
WWW_DIR      = /var/www/$(PROJECT_NAME)

# Config templates
CONFIG_DIR           = wconditions/etc
CONFIG_JSON_TEMPLATE = $(CONFIG_DIR)/db_config.json
CONFIG_JSON_FINAL    = $(ETC_DIR)/db_config.local.json
CONFIG_PHP_TEMPLATE  = $(CONFIG_DIR)/db_config.php
CONFIG_PHP_FINAL     = $(ETC_DIR)/db_config.local.php
SECRETS_FILE         = $(CONFIG_DIR)/secrets.env

# Submodules (update when new modules are added)
SUBMODULES = WeatherAlerts common dayswherecondition wcExportDaysCondition weatherDBscripts weathermetrics

# ------------------------------------------------------------
# Default Target
# ------------------------------------------------------------
all: help

# ------------------------------------------------------------
# Display Help
# ------------------------------------------------------------
help:
	@echo ""
	@echo "📘 Available targets for $(PROJECT_NAME):"
	@echo "------------------------------------------------------------"
	@echo " make install          -> Install all modules for production"
	@echo " make install-dev      -> Install all modules for development"
	@echo " make install-config   -> Generate secure local configuration files"
	@echo " make update           -> Update all git submodules"
	@echo " make tag              -> Create a git tag from current version"
	@echo " make clean            -> Remove temporary and build files"
	@echo " make help             -> Display this help message"
	@echo "------------------------------------------------------------"

# ------------------------------------------------------------
# Update git submodules
# ------------------------------------------------------------
update:
	@echo "🔄 Updating git submodules..."
	git submodule update --init --recursive
	@echo "✅ All submodules are up to date."

# ------------------------------------------------------------
# Install configuration files (from templates + secrets.env)
# ------------------------------------------------------------
install-config: check-env
	@echo "🔧 Generating configuration files..."
	@mkdir -p $(ETC_DIR)

	@set -a; . $(SECRETS_FILE); set +a; \
	sed "s|%%DB1_USER%%|$$DB1_USER|g; \
	     s|%%DB1_PASSWORD%%|$$DB1_PASSWORD|g; \
	     s|%%DB2_USER%%|$$DB2_USER|g; \
	     s|%%DB2_PASSWORD%%|$$DB2_PASSWORD|g" \
	    $(CONFIG_JSON_TEMPLATE) > $(CONFIG_JSON_FINAL)
	@chmod 600 $(CONFIG_JSON_FINAL)
	@echo "✅ JSON configuration generated: $(CONFIG_JSON_FINAL)"

	@set -a; . $(SECRETS_FILE); set +a; \
	sed "s|%%DB1_USER%%|$$DB1_USER|g; \
	     s|%%DB1_PASSWORD%%|$$DB1_PASSWORD|g; \
	     s|%%DB2_USER%%|$$DB2_USER|g; \
	     s|%%DB2_PASSWORD%%|$$DB2_PASSWORD|g" \
	    $(CONFIG_PHP_TEMPLATE) > $(CONFIG_PHP_FINAL)
	@chmod 600 $(CONFIG_PHP_FINAL)
	@echo "✅ PHP configuration generated: $(CONFIG_PHP_FINAL)"
	@echo "🎉 Configuration installed successfully."

# ------------------------------------------------------------
# Check if secrets.env exists and required tools are available
# ------------------------------------------------------------
check-env:
	@if [ ! -f "$(SECRETS_FILE)" ]; then \
	    echo "❌ Missing secrets file: $(SECRETS_FILE)"; \
	    echo "Please create it before running 'make install-config'."; \
	    exit 1; \
	fi
	@which sed >/dev/null 2>&1 || (echo "❌ 'sed' command not found in PATH" && exit 1)

# ------------------------------------------------------------
# Install for production (minimal setup)
# ------------------------------------------------------------
install: update install-config
	@echo "🚀 Installing $(PROJECT_NAME) for production..."
	@mkdir -p $(PREFIX)
	@mkdir -p $(WWW_DIR)
	@for dir in $(SUBMODULES); do \
	    echo "📦 Installing module: $$dir ..."; \
	    if [ -f $$dir/Makefile ]; then \
	        $(MAKE) -C $$dir install PREFIX=$(PREFIX)/$$dir || exit 1; \
	    else \
	        echo "⚠️  No Makefile found in $$dir, skipping..."; \
	    fi \
	done
	@cp -r web/* $(WWW_DIR)/
	@echo "$(BUILD_TAG)" > $(PREFIX)/VERSION
	@echo "✅ Production installation complete for $(PROJECT_NAME) [$(BUILD_TAG)]"

# ------------------------------------------------------------
# Install for development (includes admin & debug tools)
# ------------------------------------------------------------
install-dev: update install-config
	@echo "🧪 Installing $(PROJECT_NAME) for development..."
	@mkdir -p $(PREFIX)
	@for dir in $(SUBMODULES); do \
	    echo "🔧 Installing module (dev mode): $$dir ..."; \
	    if [ -f $$dir/Makefile ]; then \
	        $(MAKE) -C $$dir install-dev PREFIX=$(PREFIX)/$$dir || exit 1; \
	    else \
	        echo "⚠️  No Makefile found in $$dir, skipping..."; \
	    fi \
	done
	@cp -r web/* $(WWW_DIR)/
	@echo "$(BUILD_TAG)-dev" > $(PREFIX)/VERSION
	@echo "✅ Development installation complete for $(PROJECT_NAME) [$(BUILD_TAG)-dev]"

# ------------------------------------------------------------
# Create Git Tag
# ------------------------------------------------------------
tag:
	@echo "🏷️  Tagging repository with version from $(VERSION_FILE)..."
	@if [ ! -f "$(VERSION_FILE)" ]; then \
	    echo "❌ Missing $(VERSION_FILE)!"; \
	    exit 1; \
	fi
	@TAG=$$(cat $(VERSION_FILE)); \
	git tag -a $$TAG -m "Release $$TAG"; \
	git push origin $$TAG; \
	echo "✅ Tag $$TAG created and pushed."

# ------------------------------------------------------------
# Clean temporary build files
# ------------------------------------------------------------
clean:
	@echo "🧹 Cleaning build files..."
	@find . -type f -name '*.pyc' -delete
	@find . -type d -name '__pycache__' -exec rm -rf {} +
	@rm -f $(CONFIG_JSON_FINAL) $(CONFIG_PHP_FINAL)
	@echo "✅ Cleanup complete."
