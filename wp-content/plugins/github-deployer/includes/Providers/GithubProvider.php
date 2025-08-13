<?php

namespace GithubDeployer\Providers;

use GithubDeployer\Helper;
/**
 * Class GithubProvider
 *
 * @package Wp_Git_Deployer
 */
class GithubProvider extends BaseProvider {
    /**
     * Get provider repository handle
     *
     * @return string Repository handle
     */
    public function get_pretty_name() {
        return 'GitHub';
    }

    /**
     * Get provider repository zip URL
     *
     * @param  string $branch   Branch name or commit hash, default is master.
     * @param  string $token    Access token.
     * @return string           Repository URL to download zip
     */
    public function get_zip_repo_url( $branch = 'master', $token = '' ) {
        $handle = $this->get_handle();
        
        // Check if this is a commit hash (40 characters hexadecimal)
        $is_commit_hash = preg_match('/^[a-f0-9]{40}$/i', $branch);
        
        // If token is not empty and contains 'github_pat_' then it's a personal access token,
        // and we need to use API to get the zip URL from redirect "Location" header.
        if ( !empty( $token ) && Helper::is_github_pat_token( $token ) ) {
            return $this->get_authenticated_zip_url( $handle, $branch, $token );
        }
        
        // For commit hashes, use a different URL format
        if ( $is_commit_hash ) {
            return "https://github.com/{$handle}/archive/{$branch}.zip";
        }
        
        // For branch names, use the standard format
        return "https://github.com/{$handle}/archive/refs/heads/{$branch}.zip";
    }

    /**
     * Get authenticated zip URL using GitHub API
     *
     * @param string $handle Repository handle (owner/repo).
     * @param string $branch Branch name or commit hash.
     * @param string $token  GitHub Personal Access Token.
     * @return string        Authenticated zip URL.
     */
    private function get_authenticated_zip_url( $handle, $branch, $token ) {
        // Parse handle to get owner and repo
        $parts = explode( '/', $handle );
        if ( count( $parts ) !== 2 ) {
            return "https://github.com/{$handle}/archive/refs/heads/{$branch}.zip";
        }

        $owner = $parts[0];
        $repo  = $parts[1];

        // Check if this is a commit hash (40 characters hexadecimal)
        $is_commit_hash = preg_match('/^[a-f0-9]{40}$/i', $branch);

        // Use codeload.github.com endpoint for direct zip download with Authorization header
        $zip_url = "https://codeload.github.com/{$owner}/{$repo}/zip/{$branch}";

        // Set up the request with authentication
        $response = wp_remote_get( $zip_url, array(
            'headers' => array(
                'Authorization' => 'token ' . $token,
                'User-Agent'    => 'WordPress/GithubDeployer',
            ),
            'timeout' => 30,
            'redirection' => 5,
        ) );

        if ( is_wp_error( $response ) ) {
            // Fallback to direct URL if API request fails
            if ( $is_commit_hash ) {
                return "https://github.com/{$handle}/archive/{$branch}.zip";
            }
            return "https://github.com/{$handle}/archive/refs/heads/{$branch}.zip";
        }

        $response_code = wp_remote_retrieve_response_code( $response );

        // If successful, return the codeload URL (the actual download is handled by WP HTTP API)
        if ( $response_code === 200 ) {
            return $zip_url;
        }

        // Fallback to direct URL if codeload doesn't work as expected
        if ( $is_commit_hash ) {
            return "https://github.com/{$handle}/archive/{$branch}.zip";
        }
        return "https://github.com/{$handle}/archive/refs/heads/{$branch}.zip";
    }

    /**
     * Validate repository url
     *
     * @return WP_Error|boolean
     */
    protected function validate_repo_url() {
        parent::validate_repo_url();
        // check if string has exact format in a URL like this: https://github.com/company/wordpress-theme .
        if ( !preg_match( '/^https:\\/\\/github.com\\/[a-zA-Z0-9-_]+\\/[a-zA-Z0-9-_]+$/', $this->get_repo_url() ) ) {
            return new \WP_Error('invalid', __( 'Repository url must be a valid GitHub repository url', 'github-deployer' ));
        }
    }

}
