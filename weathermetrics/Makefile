# Makefile

# Define variables
REPO_URL = https://github.com/billoven/wconditions.git

# SOURCE_DIR is the directory where the initial clone has been made (and where the makefile is located)
# SOURCE_DIR = wconditions/src/weathermetrics
# Set SOURCE_DIR to the current working directory
CURRENT_DIR := $(shell pwd)
PROJECT = weathermetrics
BIN_DIR = $(CURRENT_DIR)/bin/$(PROJECT)
SOURCE_DIR = $(BIN_DIR)/$(PROJECT)/src
DEST_DIR = /var/www/html/$(PROJECT)
DEST_USER = pierre
DEST_IP = 192.168.17.10

# Fetch the latest tags
TAGS := $(shell git ls-remote --tags $(REPO_URL) | awk -F/ '{print $$3}' | grep -E 'wconditions_[0-9.]+$$' | sort -V)

# Get the latest tag starting with 'wconditions'
LATEST_TAG := $(lastword $(TAGS))

# Build target
build:
	# Create the bin directory if it doesn't exist
	mkdir -p $(BIN_DIR)

	# Clone the repository and checkout the latest tag directly in the bin directory
	git clone --depth 1 --branch $(LATEST_TAG) $(REPO_URL) $(BIN_DIR)

	# Build the project
	# Adjust this command based on your actual build process
	# e.g., make -C $(SOURCE_DIR) or $(YOUR_BUILD_COMMAND)
	# Create the release_installed.txt file with the release information
	echo "RELEASE=$(LATEST_TAG)" > $(SOURCE_DIR)/release_installed.txt


install:
	# Deploy the project to the destination server using scp
	scp -r $(SOURCE_DIR)/* ${DEST_USER}@${DEST_IP}:$(DEST_DIR)

clean:
	# Remove the bin directory
	rm -rf $(BIN_DIR)

.PHONY: build install clean
