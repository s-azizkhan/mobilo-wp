# WordPress Deployment Guide

This guide explains how to use the updated `deploy.sh` script with environment functionality.

## Overview

The `deploy.sh` script uses environment variables from `.env` files for configuration. This makes the deployment process more secure, flexible, and easier to manage across different environments. The script leverages the existing `env-loader.php` functionality that's automatically loaded by WordPress's `wp-load.php`.

## Usage

The `deploy.sh` script uses environment variables from `.env` files for configuration:

```bash
./deploy.sh [ENV_FILE]
```

## Environment Files

### Default Environment File
- **File**: `.env`
- **Usage**: `./deploy.sh` (no arguments)
- **Purpose**: Development environment

### Custom Environment Files
- **Staging**: `./deploy.sh .env.staging`
- **Production**: `./deploy.sh .env.production`
- **Custom**: `./deploy.sh .env.custom`

## Environment Variables

The following environment variables are required in your `.env` file:

### Required Variables
```bash
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASSWORD=your_database_password  # Can be empty for some database configurations
DB_HOST=your_database_host
WP_ENV=dev|staging|production
COOKIE_DOMAIN=your_domain.com
```

### Optional Variables
```bash
WP_DEBUG=true|false
WP_DEBUG_LOG=true|false
WP_DEBUG_DISPLAY=true|false
WP_CACHE=true|false
WP_MEMORY_LIMIT=512M
WP_MAX_EXECUTION_TIME=3000
DISABLE_WP_CRON=true|false
FORCE_SSL_ADMIN=true|false
WP_HOME=https://yourdomain.com
WP_SITEURL=https://yourdomain.com
```

## Setup Instructions

### 1. Create Environment Files

#### Development Environment
```bash
cp .env.example .env
# Edit .env with your development values
```

#### Staging Environment
```bash
cp env-staging.example .env.staging
# Edit .env.staging with your staging values
```

#### Production Environment
```bash
cp .env.example .env.production
# Edit .env.production with your production values
```

### 2. Run Deployment

#### Development
```bash
./deploy.sh
```

#### Staging
```bash
./deploy.sh .env.staging
```

#### Production
```bash
./deploy.sh .env.production
```

## What the Script Does

1. **Loads Environment Variables**: Reads from the specified `.env` file
2. **Validates Configuration**: Ensures all required variables are present
3. **Configures WordPress**: 
   - Creates `wp-config.php` if it doesn't exist
   - Verifies `env-loader.php` exists (loaded by `wp-load.php`)
   - Updates `.env` file with deployment values
4. **Updates Dependencies**: Runs `composer update` if `composer.json` exists
5. **Sets Permissions**: Changes file ownership (on supported systems)

## Security Benefits

- **No Command-Line Arguments**: Sensitive data is not exposed in command history
- **Environment Isolation**: Different configurations for different environments
- **Centralized Configuration**: All settings in one place per environment
- **Version Control Safe**: `.env` files can be excluded from version control

## Error Handling

The script includes comprehensive error handling:

- **Missing Environment File**: Clear error message with file path
- **Missing Required Variables**: Lists all missing variables
- **File Permission Issues**: Handles permission errors gracefully
- **Composer Issues**: Warns if composer is not available

## Examples

### Example 1: Development Deployment
```bash
# Using default .env file
./deploy.sh
```

### Example 2: Staging Deployment
```bash
# Using staging environment
./deploy.sh .env.staging
```

### Example 3: Production Deployment
```bash
# Using production environment
./deploy.sh .env.production
```

## Troubleshooting

### Common Issues

1. **"Environment file not found"**
   - Ensure the `.env` file exists in the current directory
   - Check the file path is correct

2. **"Missing required environment variables"**
   - Verify all required variables are set in your `.env` file
   - Check for typos in variable names

3. **"Permission denied"**
   - Ensure the script is executable: `chmod +x deploy.sh`
   - Check file permissions on the `.env` file

### Debug Mode

To see what the script is doing, you can run it with bash debugging:
```bash
bash -x ./deploy.sh
```



## Best Practices

1. **Never commit `.env` files** to version control
2. **Use different files** for different environments
3. **Keep `.env.example` updated** with all required variables
4. **Test deployments** in staging before production
5. **Backup configurations** before major changes
