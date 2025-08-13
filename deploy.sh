#!/bin/bash

# WordPress Deployment Script
# Usage: ./deploy.sh DB_NAME DB_USER DB_PASSWORD DB_HOST WP_ENV COOKIE_DOMAIN
# Run this script after git pull to configure WordPress

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

# Check if correct number of arguments provided
if [ "$#" -ne 6 ]; then
    print_error "Usage: $0 DB_NAME DB_USER DB_PASSWORD DB_HOST WP_ENV COOKIE_DOMAIN"
    print_error "Example: $0 wordpress_db dbuser dbpass localhost production example.com"
    exit 1
fi

# Assign arguments to variables
DB_NAME="$1"
DB_USER="$2"
DB_PASSWORD="$3"
DB_HOST="$4"
WP_ENV="$5"
COOKIE_DOMAIN="$6"

print_status "Starting WordPress deployment..."
print_status "Database Name: $DB_NAME"
print_status "Database User: $DB_USER"
print_status "Database Host: $DB_HOST"
print_status "WordPress Environment: $WP_ENV"
print_status "Cookie Domain: $COOKIE_DOMAIN"

# Get current directory
CURRENT_DIR=$(pwd)
print_status "Working directory: $CURRENT_DIR"

# Step 1: Copy wp-config-sample.php to wp-config.php
if [ -f "wp-config-sample.php" ]; then
    print_status "Copying wp-config-sample.php to wp-config.php..."
    cp wp-config-sample.php wp-config.php
    print_status "wp-config.php created successfully"
else
    print_error "wp-config-sample.php not found in current directory"
    exit 1
fi

# Step 2: Replace database configuration in wp-config.php
print_status "Configuring database settings in wp-config.php..."

# Replace database name
sed -i.bak "s/database_name_here/$DB_NAME/g" wp-config.php

# Replace database user
sed -i.bak "s/username_here/$DB_USER/g" wp-config.php

# Replace database password
sed -i.bak "s/password_here/$DB_PASSWORD/g" wp-config.php

# Replace database host
sed -i.bak "s/localhost/$DB_HOST/g" wp-config.php

# Remove backup file
rm wp-config.php.bak

# Step 2b: Add or update WP_ENV and COOKIE_DOMAIN
print_status "Configuring WP_ENV and COOKIE_DOMAIN..."

# Check if WP_ENV already exists, if not add it
if grep -q "WP_ENV" wp-config.php; then
    # Replace existing WP_ENV
    sed -i.bak "s/define('WP_ENV',.*/define('WP_ENV', '$WP_ENV');/g" wp-config.php
    rm wp-config.php.bak
else
    # Add WP_ENV before the "That's all" comment
    sed -i.bak "/\/\* That's all, stop editing!/i\\
// Environment\\\ndefine('WP_ENV', '$WP_ENV');\\\n" wp-config.php
    rm wp-config.php.bak
fi

# Check if COOKIE_DOMAIN already exists, if not add it
if grep -q "COOKIE_DOMAIN" wp-config.php; then
    # Replace existing COOKIE_DOMAIN
    sed -i.bak "s/define('COOKIE_DOMAIN',.*/define('COOKIE_DOMAIN', '$COOKIE_DOMAIN');/g" wp-config.php
    rm wp-config.php.bak
else
    # Add COOKIE_DOMAIN before the "That's all" comment
    sed -i.bak "/\/\* That's all, stop editing!/i\\
// Cookie Domain\\\ndefine('COOKIE_DOMAIN', '$COOKIE_DOMAIN');\\\n" wp-config.php
    rm wp-config.php.bak
fi

print_status "WP_ENV and COOKIE_DOMAIN configuration updated successfully"
print_status "Database configuration updated successfully"

# Step 3: Check for composer and run composer update if exists
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

# Step 4: Change ownership to ubuntu:nogroup
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
