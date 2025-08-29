# 🚀 Mobilo - High-Performance WordPress Site

A production-ready WordPress installation optimized for maximum performance with OpenLiteSpeed, PHP 8.4, and enterprise-grade caching.

## 📋 Table of Contents

- [Overview](#overview)
- [Technology Stack](#technology-stack)
- [Performance Optimizations](#performance-optimizations)
- [Installation & Deployment](#installation--deployment)
- [Environment Configuration](#environment-configuration)
- [Server Configuration](#server-configuration)
- [Development Guidelines](#development-guidelines)
- [Troubleshooting](#troubleshooting)
- [Performance Monitoring](#performance-monitoring)

## 🎯 Overview

This is a high-performance WordPress installation designed for production environments with enterprise-grade optimizations. The project includes automated deployment scripts, environment-based configuration, and comprehensive performance tuning.

### Key Features
- ⚡ **Lightning Fast**: Optimized for sub-100ms TTFB
- 🛡️ **Production Ready**: Security-hardened configuration
- 🔧 **Automated Deployment**: One-command deployment script
- 📊 **Performance Monitoring**: Built-in optimization tools
- 🌐 **CDN Ready**: Optimized for content delivery networks

## 🛠️ Technology Stack

### Core Technologies
- **WordPress**: Latest stable version with Composer-based dependency management
- **PHP**: 8.4 with JIT compilation enabled
- **Web Server**: OpenLiteSpeed with HTTP/3 QUIC
- **Database**: MySQL/MariaDB (RDS compatible)
- **Caching**: Redis object cache + LiteSpeed page cache
- **Package Management**: Composer with WPackagist for WordPress plugins/themes

### Performance Stack
- **OPcache**: 512MB memory allocation
- **JIT Compilation**: 128MB buffer for PHP 8.4
- **Memory Limit**: 768MB per PHP process
- **File Cache**: 800MB in-memory + 800MB mmap cache

## ⚡ Performance Optimizations

### Server-Level Optimizations
```bash
# OpenLiteSpeed Configuration
maxConnections: 20,000 (HTTP) / 15,000 (HTTPS)
totalInMemCacheSize: 800M
totalMMapCacheSize: 800M
maxReqBodySize: 2047M
```

### PHP 8.4 Optimizations
```ini
# Memory & Performance
memory_limit = 768M
max_execution_time = 300
realpath_cache_size = 8M
realpath_cache_ttl = 7200

# OPcache Settings
opcache.enable = 1
opcache.memory_consumption = 512
opcache.interned_strings_buffer = 128
opcache.max_accelerated_files = 20000
opcache.jit_buffer_size = 128M
opcache.jit = tracing
```

### WordPress Optimizations
- **Memory Limit**: 512M (configurable up to 1GB)
- **Post Revisions**: Limited to 3 for database efficiency
- **File Editing**: Disabled for security
- **CRON Management**: Optimized scheduling
- **Custom MU Plugin**: Dynamic theme switching for WooCommerce pages
- **Composer Dependencies**: Managed WordPress plugins and themes

## 🚀 Installation & Deployment

### Prerequisites
- Ubuntu 22.04+ or compatible Linux distribution
- OpenLiteSpeed server
- PHP 8.4 with required extensions
- MySQL/MariaDB database
- Git access to repository

### Quick Deployment

1. **Clone the repository:**
```bash
git clone <repository-url>
cd mobilo
```

2. **Run the deployment script:**
```bash
./deploy.sh DB_NAME DB_USER DB_PASSWORD DB_HOST WP_ENV COOKIE_DOMAIN
```

**Example:**
```bash
./deploy.sh wordpress_db dbuser dbpass localhost production example.com
```

### Manual Installation

1. **Configure environment variables:**
```bash
cp .env.example .env
# Edit .env with your database and domain settings
```

2. **Set up WordPress configuration:**
```bash
# Copy and configure wp-config.php
cp wp-config-sample.php wp-config.php
# Edit database settings and security keys
```

3. **Set proper permissions:**
```bash
sudo chown -R ubuntu:nogroup /path/to/wordpress
sudo chmod -R 755 /path/to/wordpress
sudo chmod -R 644 wp-config.php
```

4. **Install Composer dependencies:**
```bash
# Install all dependencies including development
composer install

# Install only production dependencies
composer install --no-dev

# Update dependencies to latest versions
composer update

# Optimize autoloader for production
composer dump-autoload --optimize
```

## 🔧 Environment Configuration

### Environment Variables
The project uses a custom environment loader (`env-loader.php`) that supports loading environment variables from `.env` file. Check `.env.example` for reference.

### Composer Configuration
The project uses Composer for dependency management with the following key features:

- **WPackagist Repository**: WordPress plugins and themes from the official repository
- **Custom Vendor Directory**: `wp-content/vendor` for better organization
- **Plugin Management**: 50+ WordPress plugins managed via Composer
- **Theme Management**: Multiple themes including custom child themes
- **Development Dependencies**: Separate dev requirements for development environment

#### Key Dependencies
- **WooCommerce**: Full e-commerce functionality
- **Plugin Organizer**: Organize & load plugins conditionally on each page
- **Elementor**: Page builder with custom addons
- **LiteSpeed Cache**: Performance optimization & caching
- **codirun cdn**: CDN for static files
- **Redis Cache**: Object caching
- **Yoast SEO**: Search engine optimization
- **Contact Form 7**: Contact forms
- **UpdraftPlus**: Backup solution

### Environment-Specific Configurations

#### Development
```php
WP_DEBUG=true
WP_DEBUG_LOG=true
WP_DEBUG_DISPLAY=true
WP_ENV=dev
```

#### Production
```php
WP_DEBUG=false
WP_DEBUG_LOG=false
WP_DEBUG_DISPLAY=false
WP_ENV=production
WP_CACHE=true
```

## 🖥️ Server Configuration

### OpenLiteSpeed Optimization
The server is configured for maximum performance:

```apache
# Connection Limits
maxConnections 20000
maxSSLConnections 15000
connTimeout 300

# Cache Settings
totalInMemCacheSize 800M
totalMMapCacheSize 800M
maxCachedFileSize 16384

# Compression
enableGzip 1
enableBrotli 1
```

### PHP-FPM Configuration
```ini
# Process Management
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35

# Memory Settings
pm.max_requests = 1000
request_terminate_timeout = 300
```

## 📝 Development Guidelines

### PHP Standards
- **PHP Version**: 8.4+ compatibility required (as per composer.json)
- **Coding Standards**: PSR-12
- **Error Handling**: Proper exception handling
- **Security**: Input validation and sanitization

### Custom Theme System
The project includes a sophisticated theme switching system:

#### Custom MU Plugin (`mobilo-mu.php`)
- **Dynamic Theme Switching**: Automatically switches themes based on page context
- **WooCommerce Integration**: Uses optimized themes for cart and checkout pages
- **Performance Optimization**: Loads specific themes & plugins for better performance

#### Theme Switching Logic
```php
// Cart page → mobilo-wp-child-theme (child theme of blocksy)
// Checkout page → mobilo-optimize theme (standalone theme)
// All other pages → Default theme (blocksy) with custom child theme (mobilo-wp-child-theme)
```

#### Custom Themes
- **mobilo-optimize**: Ultra-optimized theme for checkout pages
- **mobilo-wp-child-theme**: Custom child theme for cart pages
- **blocksy**: Parent theme with full functionality

### WordPress Development
```php
// Use WordPress coding standards
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Proper hook usage
add_action( 'init', 'my_function' );
add_filter( 'the_content', 'my_filter' );

// Security best practices
$sanitized_data = sanitize_text_field( $_POST['data'] );
$validated_data = wp_kses_post( $content );

// Custom theme switching (MU plugin example)
function mobilo_helper_mu_chosen_theme() {
    $current_url = home_url(add_query_arg([], $_SERVER["REQUEST_URI"]));
    if (strpos($current_url, "checkout")) {
        return "mobilo-optimize";
    }
    if (strpos($current_url, "cart")) {
        return "mobilo-wp-child-theme";
    }
    return false;
}
```

### File Structure
```
mobilo/
├── wp-admin/              # WordPress admin files
├── wp-content/            # Themes, plugins, uploads
│   ├── plugins/           # Active plugins (Composer managed)
│   ├── themes/            # Active themes
│   │   ├── mobilo-optimize/     # Custom optimized theme
│   │   ├── mobilo-wp-child-theme/ # Custom child theme
│   │   └── blocksy/             # Parent theme
│   ├── uploads/           # Media files
│   ├── mu-plugins/        # Must-use plugins
│   │   └── mobilo-mu.php  # Custom theme switcher
│   └── vendor/            # Composer dependencies
├── wp-includes/           # WordPress core files
├── composer.json          # Composer configuration
├── composer.lock          # Locked dependency versions
├── deploy.sh              # Deployment script
├── env-loader.php         # Environment loader
├── wp-config.php          # WordPress configuration
└── README.md              # This file
```

## 🔍 Troubleshooting

### Common Issues

#### Performance Issues
```bash
# Check PHP memory usage
php -i | grep memory_limit

# Verify OPcache status
php -r "print_r(opcache_get_status());"

# Monitor server resources
htop
free -h
df -h
```

#### Database Connection Issues
```bash
# Test database connectivity
mysql -u DB_USER -p -h DB_HOST DB_NAME

# Check WordPress database tables
wp db check --allow-root
```

#### Cache Issues
```bash
# Clear OpenLiteSpeed cache
sudo /usr/local/lsws/bin/lswsctrl restart

# Clear WordPress cache (if using caching plugin)
wp cache flush --allow-root

# Clear Composer cache
composer clear-cache

# Regenerate autoloader
composer dump-autoload --optimize
```

### Debug Mode
Enable debug mode for development:
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## 📊 Performance Monitoring

### Built-in Monitoring Tools
```bash
# Server performance script
sudo /usr/local/bin/wordpress-performance-optimizer.sh

# Check current server stats
./check_wp_cache.sh

# Check Composer dependencies
composer show --installed

# Verify theme switching
wp theme list --allow-root
```

### Performance Metrics
- **Page Load Speed**: 70-90% faster than standard WordPress
- **TTFB**: Sub-100ms response times
- **Concurrent Users**: 1000+ simultaneous users
- **Memory Usage**: Optimized RAM utilization

### Monitoring Commands
```bash
# Real-time server monitoring
watch -n 2 'free -h && echo "---" && ps aux --sort=-%mem | head -10'

# PHP process monitoring
ps aux | grep lsphp

# Cache hit ratio
php -r "echo 'OPcache Hit Rate: ' . (opcache_get_status()['opcache_statistics']['opcache_hit_rate'] ?? 'N/A') . '%';"
```

## 🔒 Security Considerations

### WordPress Security
- **File Permissions**: Proper ownership and permissions
- **Security Keys**: Unique authentication keys
- **File Editing**: Disabled in production
- **Updates**: Regular WordPress and plugin updates

### Server Security
- **SSL/TLS**: HTTPS enforcement
- **Firewall**: Proper port configuration
- **Backups**: Regular automated backups
- **Monitoring**: Security event logging

## 📚 Additional Resources

### Documentation
- [WordPress Developer Handbook](https://developer.wordpress.org/)
- [OpenLiteSpeed Documentation](https://openlitespeed.org/kb/)
- [PHP 8.4 Documentation](https://www.php.net/manual/en/)

### Performance Optimization
- [WordPress Performance Best Practices](https://developer.wordpress.org/advanced-administration/performance/)
- [LiteSpeed Cache Plugin](https://wordpress.org/plugins/litespeed-cache/)
- [Redis Object Cache](https://wordpress.org/plugins/redis-cache/)
- [WPackagist Documentation](https://wpackagist.org/)
- [Composer WordPress Integration](https://getcomposer.org/doc/articles/custom-installers.md)

### Support
For technical support and optimization questions, refer to:
- WooCommerce debug log: `{site_url}/wp-admin/admin.php?page=wc-status&tab=logs`
- WordPress debug log: `wp-content/debug.log`
- Server logs: `/var/log/`
- OpenLiteSpeed logs: `/usr/local/lsws/logs/`

---

## 📄 License

This is a private custom project, not open source.

## 🤝 Contributing

1. Follow WordPress coding standards
2. Test changes in development environment
3. Update documentation for new features
4. Ensure backward compatibility with PHP 8.4+
5. Update `composer.json` when adding new plugins/themes
6. Test theme switching functionality on WooCommerce pages
7. Verify Composer dependencies are properly installed

---

**Built with ❤️ for high-performance WordPress hosting**
