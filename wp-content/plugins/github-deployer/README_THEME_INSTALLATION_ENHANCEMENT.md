# Theme Installation Enhancements

This document describes the enhanced theme installation and update functionality that has been implemented for the GitHub Deployer plugin.

## New Features

### 1. Grouped Repository Input
- Repository information is now organized in a clear, grouped interface
- Separate sections for "Repository Information" and "Deployment Options"
- Better visual hierarchy and user experience

### 2. Repository Validation
- Real-time validation of repository URLs
- Validates against the selected provider (GitHub, GitLab, Bitbucket, Gitea)
- Shows validation results with success/error messages
- Validates repository accessibility and format

### 3. Private Repository Support
- Checkbox to indicate if the repository is private
- Automatic showing/hiding of access token field based on provider
- Enhanced security for private repository access

### 4. Repository Data Fetching
- "Fetch Repository Data" button to retrieve available branches and commits
- Fetches repository information via provider APIs
- Supports authentication for private repositories

### 5. Branch Selection
- Dropdown to select from available branches
- Automatically selects the default branch
- Real-time branch loading from repository

### 6. Commit Selection (Optional)
- Optional dropdown to select a specific commit
- Shows recent commits for the selected branch
- Displays commit hash and message
- Allows deployment of specific commit versions

### 7. Enhanced Update Functionality
- **"Update with Options" button** for existing themes
- Modal interface for selecting branch and commit during updates
- Real-time repository data fetching for updates
- Support for updating to specific commits or branches
- Maintains existing "Update Theme" button for quick updates

## Technical Implementation

### AJAX Endpoints
- `validate_repository`: Validates repository URL and accessibility
- `fetch_repository_data`: Fetches branches and commits from repository
- `fetch_update_repository_data`: Fetches repository data for updates
- `update_package_with_ref`: Updates package with specific branch or commit

### Provider Support
- **GitHub**: Full API support for branches and commits
- **GitLab**: Full API support for branches and commits  
- **Bitbucket**: Full API support for branches and commits
- **Gitea**: Basic support (limited API availability)

### Security Features
- Nonce verification for all AJAX requests
- Input sanitization and validation
- Secure token handling for private repositories

## Usage Flow

### Theme Installation
1. **Select Provider**: Choose from GitHub, GitLab, Bitbucket, or Gitea
2. **Enter Repository URL**: Input the repository URL
3. **Validate Repository**: Click "Validate" to verify the repository
4. **Set Private Status**: Check if repository is private (if applicable)
5. **Enter Access Token**: For private repositories, enter access token
6. **Fetch Repository Data**: Click to load available branches and commits
7. **Select Branch**: Choose the branch to deploy from
8. **Select Commit (Optional)**: Choose a specific commit (optional)
9. **Install Theme**: Click "Install Theme" to deploy

### Theme Updates
1. **Quick Update**: Click "Update Theme" for standard update (uses stored branch)
2. **Enhanced Update**: Click "Update with Options" to open update modal
3. **Select Branch**: Choose from available branches in the modal
4. **Select Commit (Optional)**: Choose a specific commit (optional)
5. **Confirm Update**: Click "Update Theme" to perform the update

## File Changes

### Modified Files
- `includes/Subpages/InstallThemePage/InstallTheme.php`: Added AJAX handlers and repository data fetching
- `includes/Subpages/InstallThemePage/template.php`: Updated UI with grouped inputs and validation
- `includes/Subpages/InstallPackage.php`: Added specific commit handling
- `includes/Providers/GithubProvider.php`: Enhanced to support commit hashes
- `includes/ApiRequests/PackageUpdate.php`: Added enhanced update functionality
- `includes/Subpages/DashboardPage/partials/_themes.php`: Added enhanced update modal and buttons
- `assets/js/github-deployer-main.js`: Added client-side validation, data fetching, and modal functionality
- `assets/css/github-deployer-main.css`: Added styles for new UI elements and modal

### New Features
- Repository validation with real-time feedback
- Branch and commit selection dropdowns
- Enhanced UI with grouped sections
- Loading states and error handling
- Responsive design for mobile devices
- **Enhanced Update Modal**: Modern modal interface for update options
- **Dual Update Options**: Quick update and detailed update with options
- **Real-time Repository Data**: Fetches current repository state for updates

## API Integration

The implementation integrates with various Git provider APIs:

- **GitHub API**: Uses GitHub's REST API v3
- **GitLab API**: Uses GitLab's REST API v4
- **Bitbucket API**: Uses Bitbucket's REST API v2.0
- **Gitea**: Basic support with fallback options

## Error Handling

- Comprehensive error messages for validation failures
- Graceful fallbacks for API failures
- User-friendly error display
- Automatic retry mechanisms for network issues

## Browser Compatibility

- Modern browsers with ES6 support
- Responsive design for mobile devices
- Graceful degradation for older browsers

## Security Considerations

- All user inputs are sanitized
- Nonce verification prevents CSRF attacks
- Access tokens are handled securely
- Repository URLs are validated before processing

## Enhanced Update Features

### Modal Interface
- **Modern Design**: Clean, responsive modal with smooth animations
- **Loading States**: Visual feedback during data fetching
- **Error Handling**: Clear error messages for failed operations
- **Current Branch Display**: Shows the currently deployed branch

### Update Options
- **Branch Selection**: Choose from all available repository branches
- **Commit Selection**: Optionally select a specific commit from the chosen branch
- **Smart Defaults**: Automatically selects current branch and latest commit
- **Validation**: Ensures valid selections before allowing update

### User Experience
- **Dual Update Paths**: Quick update for standard deployments, detailed update for specific needs
- **Real-time Feedback**: Loading states and success/error messages
- **Responsive Design**: Works on desktop and mobile devices
- **Keyboard Navigation**: ESC key closes modal, Enter confirms actions

## Implementation Details

### Update Flow
1. User clicks "Update with Options"
2. Modal opens and fetches repository data
3. User selects branch (and optionally commit)
4. System validates selections
5. Update is performed with selected ref
6. Success/error feedback is provided
7. Page reloads to show updated state

### Data Handling
- Repository data is cached during modal session
- Commit data is fetched based on selected branch
- All selections are validated before update
- Error states are handled gracefully

### Performance
- Lazy loading of repository data
- Efficient API calls with proper caching
- Minimal impact on page load times
- Responsive UI with smooth animations 