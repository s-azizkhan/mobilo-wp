# Mobilo Auth - Firebase Authentication Plugin

A comprehensive Firebase Authentication plugin for WordPress with multi-region support, user management, and enhanced security features.

## Features

### 🔐 Authentication
- **Firebase Authentication Integration**: Seamless integration with Firebase Auth
- **Multi-Region Support**: Configure different Firebase projects for different regions
- **WordPress Integration**: Automatic WordPress user creation and management
- **Custom Authentication Hooks**: Extensible authentication system
- **Session Management**: Secure token-based session handling

### 👥 User Management
- **Auto User Creation**: Automatically create WordPress users from Firebase
- **Profile Synchronization**: Keep Firebase and WordPress user data in sync
- **Custom User Meta**: Store Firebase-specific user information
- **User Role Management**: Integrate with WordPress user roles
- **Bulk User Operations**: Admin tools for user management

### 🛡️ Security Features
- **JWT Token Validation**: Secure token verification
- **Password Reset**: Firebase-powered password reset functionality
- **Session Timeout**: Configurable session management
- **IP Logging**: Track authentication attempts
- **Rate Limiting**: Prevent brute force attacks

### 🔧 Developer Features
- **REST API**: Full REST API for authentication operations
- **AJAX Support**: Built-in AJAX handlers for frontend integration
- **Shortcodes**: Easy-to-use shortcodes for forms
- **Hooks & Filters**: Extensive WordPress integration points
- **Logging System**: Comprehensive logging for debugging

### 📊 Admin Interface
- **Dashboard**: Overview of authentication statistics
- **User Management**: Manage Firebase-connected users
- **Settings Panel**: Comprehensive configuration options
- **Logs Viewer**: Monitor authentication activities
- **Region Management**: Configure multi-region Firebase projects

## Requirements

- **PHP**: 7.4 or higher
- **WordPress**: 5.6 or higher
- **Firebase**: Active Firebase project with Authentication enabled
- **Composer**: For dependency management

## Installation

### Method 1: Manual Installation

1. Download the plugin files
2. Upload to `/wp-content/plugins/mobilo-auth/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure Firebase settings in the admin panel

### Method 2: Composer Installation

```bash
composer require mobilo/mobilo-auth
```

### Method 3: WordPress Plugin Repository

1. Go to Plugins > Add New in WordPress admin
2. Search for "Mobilo Auth"
3. Click "Install Now" and then "Activate"

## Configuration

### 1. Firebase Setup

1. Create a Firebase project at [Firebase Console](https://console.firebase.google.com/)
2. Enable Authentication in your Firebase project
3. Download your service account key JSON file
4. Upload the JSON file to your WordPress site (secure location)

### 2. Plugin Configuration

1. Go to **Mobilo Auth > Settings** in WordPress admin
2. Enter your Firebase project details:
   - Project ID
   - Service Account Key file path
   - API Key
   - Auth Domain
3. Configure authentication options
4. Test the connection

### 3. Multi-Region Setup

1. Go to **Mobilo Auth > Firebase Regions**
2. Add regions (e.g., US, Europe)
3. Configure Firebase settings for each region
4. Set default region

## Usage

### Shortcodes

#### Login Form
```php
[mobilo_auth_login redirect="/dashboard" show_register_link="true"]
```

#### Registration Form
```php
[mobilo_auth_register show_login_link="true"]
```

#### Profile Form
```php
[mobilo_auth_profile]
```

#### Password Reset
```php
[mobilo_auth_reset_password]
```

#### Authentication Status
```php
[mobilo_auth_status show_logout="true"]
```

### REST API Endpoints

#### Authentication
- `POST /wp-json/mobilo-auth/v1/login` - User login
- `POST /wp-json/mobilo-auth/v1/register` - User registration
- `POST /wp-json/mobilo-auth/v1/logout` - User logout
- `POST /wp-json/mobilo-auth/v1/reset-password` - Password reset

#### User Management
- `GET /wp-json/mobilo-auth/v1/profile` - Get user profile
- `PUT /wp-json/mobilo-auth/v1/profile` - Update user profile
- `POST /wp-json/mobilo-auth/v1/change-password` - Change password

#### Token Management
- `POST /wp-json/mobilo-auth/v1/verify-token` - Verify Firebase token
- `POST /wp-json/mobilo-auth/v1/refresh-token` - Refresh token

#### Admin (Protected)
- `GET /wp-json/mobilo-auth/v1/admin/stats` - Get authentication statistics
- `GET /wp-json/mobilo-auth/v1/admin/users` - Get user list

### AJAX Handlers

The plugin provides AJAX handlers for frontend integration:

```javascript
// Login
jQuery.post(mobiloAuthFrontend.ajaxUrl, {
    action: 'mobilo_auth_login',
    nonce: mobiloAuthFrontend.nonce,
    email: 'user@example.com',
    password: 'password',
    remember: true
}, function(response) {
    console.log(response);
});

// Register
jQuery.post(mobiloAuthFrontend.ajaxUrl, {
    action: 'mobilo_auth_register',
    nonce: mobiloAuthFrontend.nonce,
    email: 'user@example.com',
    password: 'password',
    display_name: 'User Name'
}, function(response) {
    console.log(response);
});
```

### Hooks and Filters

#### Actions
```php
// User authentication events
do_action('mobilo_auth_user_login', $user_id, $firebase_uid);
do_action('mobilo_auth_user_register', $user_id, $firebase_uid);
do_action('mobilo_auth_user_logout', $user_id, $firebase_uid);

// Firebase connection events
do_action('mobilo_auth_firebase_connected', $region);
do_action('mobilo_auth_firebase_error', $error_message);
```

#### Filters
```php
// Customize authentication behavior
add_filter('mobilo_auth_auto_create_user', '__return_false');
add_filter('mobilo_auth_user_role', function($role, $firebase_user) {
    return 'subscriber';
}, 10, 2);

// Customize redirect URLs
add_filter('mobilo_auth_login_redirect', function($url, $user) {
    return home_url('/dashboard');
}, 10, 2);
```

## Development

### Project Structure

```
mobilo-auth/
├── includes/
│   ├── Core/           # Core functionality
│   ├── Admin/          # Admin interface
│   ├── API/            # REST API
│   ├── Ajax/           # AJAX handlers
│   ├── Shortcodes/     # Frontend shortcodes
│   └── Utils/          # Utility classes
├── assets/             # CSS, JS, images
├── languages/          # Translation files
├── views/              # Admin view templates
├── composer.json       # Dependencies
└── mobilo-auth.php     # Main plugin file
```

### Adding Custom Features

#### Custom Authentication Provider

```php
class CustomAuthProvider extends \MobiloAuth\Core\FirebaseAuth
{
    public function customAuthMethod($credentials)
    {
        // Custom authentication logic
        return $this->authenticate($credentials);
    }
}
```

#### Custom Shortcode

```php
add_shortcode('custom_auth_form', function($atts) {
    // Custom form rendering
    return '<div>Custom form</div>';
});
```

### Testing

```bash
# Run tests
composer test

# Code style check
composer phpcs

# Fix code style issues
composer phpcbf
```

## Troubleshooting

### Common Issues

#### Firebase Connection Failed
- Verify service account key file path
- Check Firebase project settings
- Ensure Authentication is enabled in Firebase

#### Users Not Syncing
- Check auto-create users setting
- Verify Firebase user permissions
- Check error logs

#### Shortcodes Not Working
- Ensure plugin is activated
- Check for JavaScript errors
- Verify AJAX nonce

### Debug Mode

Enable debug logging in plugin settings:

1. Go to **Mobilo Auth > Settings > Advanced**
2. Enable "Debug Logging"
3. Check WordPress debug log for detailed information

### Error Logs

Check the following locations for error logs:
- WordPress debug log
- Plugin-specific logs in admin panel
- Firebase console logs

## Security Considerations

### Best Practices

1. **Secure File Storage**: Store Firebase service account keys outside web root
2. **HTTPS Only**: Use HTTPS in production
3. **Regular Updates**: Keep plugin and dependencies updated
4. **User Permissions**: Limit admin access to trusted users
5. **Rate Limiting**: Implement rate limiting for authentication endpoints

### Data Privacy

- User data is stored in WordPress database
- Firebase tokens are encrypted and secured
- No sensitive data is logged
- GDPR compliance features included

## Support

### Documentation
- [Plugin Documentation](https://docs.mobilocard.com/mobilo-auth)
- [API Reference](https://docs.mobilocard.com/mobilo-auth/api)
- [Developer Guide](https://docs.mobilocard.com/mobilo-auth/developer)

### Support Channels
- **Email**: support@mobilocard.com
- **GitHub Issues**: [Report Issues](https://github.com/mobilo/mobilo-auth/issues)
- **Community Forum**: [WordPress.org Support](https://wordpress.org/support/plugin/mobilo-auth)

### Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## Changelog

### Version 1.0.0
- Initial release
- Firebase Authentication integration
- Multi-region support
- WordPress user management
- REST API endpoints
- Admin interface
- Shortcodes and AJAX handlers

## License

This plugin is licensed under the GPL v2 or later.

## Credits

- **Firebase PHP SDK**: [Kreait](https://github.com/kreait/firebase-php)
- **WordPress**: [WordPress Foundation](https://wordpress.org/)
- **Development**: [LogicWind](https://logicwind.com)

---

**Mobilo Auth** - Secure, scalable Firebase authentication for WordPress.
