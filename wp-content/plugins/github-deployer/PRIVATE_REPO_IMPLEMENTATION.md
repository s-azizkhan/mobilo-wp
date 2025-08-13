# Private Repository Access Implementation

This document describes the implementation of private repository access using authentication tokens in the Github Deployer plugin.

## Overview

The implementation adds support for accessing private GitHub repositories using Personal Access Tokens (PAT) and other authentication methods.

## Key Components

### 1. GitHub Provider Enhancement (`GithubProvider.php`)

The `GithubProvider` class has been enhanced to handle private repository access:

- **`get_zip_repo_url()` method**: Now checks if a GitHub PAT token is provided and uses the GitHub API to get authenticated download URLs
- **`get_authenticated_zip_url()` method**: New private method that makes API calls to GitHub to get authenticated download URLs

#### How it works:

1. When a token is provided and it's a GitHub PAT (starts with `github_pat_`), the provider uses the GitHub API
2. The API call is made to `https://api.github.com/repos/{owner}/{repo}/zipball/{branch}`
3. The response is checked for redirect headers (302) or direct download URLs
4. If the API call fails, it falls back to the direct GitHub URL

### 2. Authenticated Upgraders

Two new upgrader classes have been created to handle authentication during package downloads:

- **`AuthenticatedThemeUpgrader.php`**: Extends `Theme_Upgrader` to add authentication headers
- **`AuthenticatedPluginUpgrader.php`**: Extends `Plugin_Upgrader` to add authentication headers

#### Features:

- Automatically adds authentication headers to HTTP requests for GitHub URLs
- Supports both token-based authentication (`Authorization: token <token>`) and basic auth (`Authorization: Basic <base64>`)
- Only applies authentication to GitHub URLs to avoid conflicts with other providers
- Adds proper User-Agent headers for GitHub API compliance

### 3. Installation Process Enhancement (`InstallPackage.php`)

The installation process has been updated to:

- Check if the repository is private based on the provided options
- Use the appropriate authenticated upgrader when dealing with private repositories
- Pass authentication options to the upgrader for proper authentication

## Authentication Methods Supported

### 1. GitHub Personal Access Token (PAT)
- **Format**: `github_pat_<token>`
- **Usage**: Automatically detected and used for API calls
- **Scope**: Requires `repo` access for private repositories

### 2. Basic Authentication
- **Format**: Username and password combination
- **Usage**: Used as fallback when token is not available
- **Scope**: Requires repository access permissions

## Implementation Details

### Token Detection
The `Helper::is_github_pat_token()` method checks if a token is a GitHub PAT by looking for the `github_pat_` prefix.

### API Integration
When a PAT is detected:
1. The repository handle is parsed to extract owner and repository name
2. A GitHub API call is made to get the authenticated download URL
3. The response is processed to extract the actual download URL
4. If the API call fails, the system falls back to the direct GitHub URL

### HTTP Request Filtering
The authenticated upgraders use WordPress's `http_request_args` filter to:
- Add authentication headers only to GitHub URLs
- Support both token and basic authentication
- Add proper User-Agent headers for API compliance

## Usage

### For Public Repositories
No changes required. The plugin works as before.

### For Private Repositories
1. Mark the repository as private in the installation form
2. Provide either:
   - A GitHub Personal Access Token (recommended)
   - Username and password combination
3. The plugin will automatically use the appropriate authentication method

## Error Handling

The implementation includes comprehensive error handling:

- **API Failures**: Falls back to direct URLs if GitHub API calls fail
- **Invalid Tokens**: Gracefully handles invalid or expired tokens
- **Network Issues**: Provides fallback mechanisms for network connectivity problems
- **Malformed URLs**: Validates repository URLs before processing

## Security Considerations

1. **Token Storage**: Tokens are stored securely in WordPress options
2. **HTTPS Only**: All API calls use HTTPS for secure communication
3. **Scope Limitation**: Only necessary permissions are requested
4. **Error Logging**: Failed authentication attempts are logged for debugging

## Testing

The implementation can be tested by:
1. Installing a private GitHub repository
2. Providing a valid GitHub PAT token
3. Verifying that the installation completes successfully

## Future Enhancements

Potential improvements include:
- Support for other Git providers (GitLab, Bitbucket)
- Token refresh mechanisms
- Enhanced error reporting
- Rate limiting protection
- Audit logging for security compliance 