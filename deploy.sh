#!/bin/bash

# WordPress Deployment Script
# Usage: ./deploy.sh [ENV_FILE]
# Run this script after git pull to configure WordPress
# If no ENV_FILE is provided, it will use .env

set -e  # Exit on any error

# Start time tracking
START_TIME=$(date +%s)

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to load environment variables from .env file
load_env() {
    local env_file="$1"
    
    if [ ! -f "$env_file" ]; then
        print_error "Environment file not found: $env_file"
        exit 1
    fi
    
    print_status "Loading environment variables from $env_file..."
    
    # Read .env file and export variables
    while IFS= read -r line || [ -n "$line" ]; do
        # Skip comments and empty lines
        if [[ $line =~ ^[[:space:]]*# ]] || [[ -z "${line// }" ]]; then
            continue
        fi
        
        # Export the variable
        export "$line"
    done < "$env_file"
    
    print_status "Environment variables loaded successfully"
}

# Function to validate required environment variables
validate_env_vars() {
    local required_vars=("DB_NAME" "DB_USER" "DB_HOST" "WP_ENV" "COOKIE_DOMAIN")
    local missing_vars=()
    
    for var in "${required_vars[@]}"; do
        if [ -z "${!var}" ]; then
            missing_vars+=("$var")
        fi
    done
    
    # DB_PASSWORD can be empty for some database configurations
    if [ -z "$DB_PASSWORD" ]; then
        print_warning "DB_PASSWORD is empty - this is acceptable for some database configurations"
    fi
    
    if [ ${#missing_vars[@]} -gt 0 ]; then
        print_error "Missing required environment variables: ${missing_vars[*]}"
        print_error "Please check your .env file and ensure all required variables are set"
        exit 1
    fi
}

# Determine which environment file to use
ENV_FILE="${1:-.env}"

# Load environment variables
load_env "$ENV_FILE"

# Validate required environment variables
validate_env_vars

print_status "Starting WordPress deployment..."
print_status "Database Name: $DB_NAME"
print_status "Database User: $DB_USER"
print_status "Database Host: $DB_HOST"
print_status "WordPress Environment: $WP_ENV"
print_status "Cookie Domain: $COOKIE_DOMAIN"

# Get current directory
CURRENT_DIR=$(pwd)
print_status "Working directory: $CURRENT_DIR"

# Step 1: Copy wp-config-sample.php to wp-config.php if it doesn't exist
if [ ! -f "wp-config.php" ] && [ -f "wp-config-samplex.php" ]; then
    print_status "Copying wp-config-samplex.php to wp-config.php..."
    cp wp-config-samplex.php wp-config.php
    print_status "wp-config.php created successfully"
elif [ -f "wp-config.php" ]; then
    print_status "wp-config.php already exists, skipping creation"
else
    print_error "Neither wp-config.php nor wp-config-samplex.php found in current directory"
    exit 1
fi

# Step 2: Verify env-loader.php exists
if [ -f "env-loader.php" ]; then
    print_status "env-loader.php found - environment loading is configured"
else
    print_error "env-loader.php not found. This file is required for environment variable loading."
    exit 1
fi

# Step 3: Update .env file with current values (if they don't exist)
print_status "Updating .env file with deployment values..."

# Function to update or add environment variable in .env file
update_env_var() {
    local var_name="$1"
    local var_value="$2"
    local env_file="$3"
    
    if grep -q "^${var_name}=" "$env_file"; then
        # Variable exists, update it
        sed -i.bak "s/^${var_name}=.*/${var_name}=${var_value}/" "$env_file"
    else
        # Variable doesn't exist, add it
        echo "${var_name}=${var_value}" >> "$env_file"
    fi
}

# Update .env file with current values
update_env_var "DB_NAME" "$DB_NAME" "$ENV_FILE"
update_env_var "DB_USER" "$DB_USER" "$ENV_FILE"
update_env_var "DB_PASSWORD" "$DB_PASSWORD" "$ENV_FILE"
update_env_var "DB_HOST" "$DB_HOST" "$ENV_FILE"
update_env_var "WP_ENV" "$WP_ENV" "$ENV_FILE"
update_env_var "COOKIE_DOMAIN" "$COOKIE_DOMAIN" "$ENV_FILE"

# Remove backup file if it exists
if [ -f "${ENV_FILE}.bak" ]; then
    rm "${ENV_FILE}.bak"
fi

print_status ".env file updated successfully"

# Step 4: Check for composer and run composer update if exists
if [ -f "composer.json" ]; then
    print_status "composer.json found, checking for composer..."
    
    if command -v composer &> /dev/null; then
        print_status "Running composer update..."
        # remove vendor folder if it exists & lock file
        if [ -d "wp-content/vendor" ]; then
            rm -rf wp-content/vendor
        fi
        if [ -f "composer.lock" ]; then
            rm composer.lock
        fi
        composer update --no-dev --optimize-autoloader
        print_status "Composer update completed successfully"
    else
        print_warning "Composer not found in PATH. Please install composer or run 'composer update' manually"
    fi
else
    print_status "No composer.json found, skipping composer update"
fi

# Step 5: Change ownership to ubuntu:nogroup
print_status "Changing ownership of $CURRENT_DIR to ubuntu:nogroup..."

# Check if running on a system that supports the ubuntu user
if id "ubuntu" &>/dev/null; then
    if sudo chown -R ubuntu:nogroup "$CURRENT_DIR"; then
        print_status "Ownership changed successfully"
    else
        print_error "Failed to change ownership. Make sure you have sudo privileges"
        exit 1
    fi
else
    print_warning "User 'ubuntu' not found on this system"
    print_warning "Current system appears to be macOS or other. Skipping ownership change."
    print_warning "On production server, run: sudo chown -R ubuntu:nogroup $CURRENT_DIR"
fi

# Calculate elapsed time
END_TIME=$(date +%s)
ELAPSED_TIME=$((END_TIME - START_TIME))
ELAPSED_MINUTES=$((ELAPSED_TIME / 60))
ELAPSED_SECONDS=$((ELAPSED_TIME % 60))

print_status "Deployment completed successfully! & WordPress is now configured and ready to use"
print_status "Total deployment time: ${ELAPSED_MINUTES}m ${ELAPSED_SECONDS}s"
print_status "Environment file used: $ENV_FILE"
