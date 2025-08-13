'use strict';

var j = jQuery.noConflict();

j(function () {
	init_provider_changed_listener();
	init_private_repo_changed_listener();
	init_dashboard_listenes();
	init_theme_installation_enhancements();
	init_enhanced_update_functionality();
});

function init_provider_changed_listener() {
	j('.gd_install_package_form').on('change', 'select[name="provider_type"]', function (e) {
		const selected_provider_name = e.target.value;

		// Repo URL descriptions
		j(`.gd_install_package_form .gd_repo_url_description`).addClass('gd_hidden');
		j(`.gd_install_package_form #${selected_provider_name}-repo-url-description`).removeClass('gd_hidden');

		// Show "is private repository" row
		j('.gd_is_private_repository_row').removeClass('gd_hidden');

		// Refresh private repo fields
		refresh_private_repo_fields();
	});
}

function init_private_repo_changed_listener() {
	j('.gd_install_package_form').on('change', 'input[name="is_private_repository"]', function (e) {
		refresh_private_repo_fields();
	});
}

function refresh_private_repo_fields() {
	const is_checked = j('.gd_install_package_form input[name="is_private_repository"]').is(':checked');
	const provider_type = j('.gd_install_package_form select[name="provider_type"]').val();

	j(`.gd_install_package_form .gd_username_row,
		 .gd_install_package_form .gd_password_row,
		 .gd_install_package_form .gd_access_token_row,
		 .gd_install_package_form .gd_access_token_description`).addClass('gd_hidden');

	if (is_checked) {
		if (provider_type === 'github') {
			j('.gd_install_package_form .gd_access_token_row').removeClass('gd_hidden');
			j(`.gd_install_package_form #github-access-token-description`).removeClass('gd_hidden');
		}

		if (provider_type === 'bitbucket') {
			j(`.gd_install_package_form .gd_username_row,
				 .gd_install_package_form .gd_password_row`).removeClass('gd_hidden');
		}

		if (provider_type === 'gitlab') {
			j('.gd_install_package_form .gd_access_token_row').removeClass('gd_hidden');
			j(`.gd_install_package_form #gitlab-access-token-description`).removeClass('gd_hidden');
		}

		if (provider_type === 'gitea') {
			j('.gd_install_package_form .gd_access_token_row').removeClass('gd_hidden');
			j(`.gd_install_package_form #gitea-access-token-description`).removeClass('gd_hidden');
		}
	}
}

function init_theme_installation_enhancements() {
	// Repository validation
	j('#validate_repo_btn').on('click', function (e) {
		e.preventDefault();
		validateRepository();
	});

	// Fetch repository data
	j('#fetch_repo_data_btn').on('click', function (e) {
		e.preventDefault();
		fetchRepositoryData();
	});

	// Enable validation on Enter key in repository URL field
	j('#repository_url').on('keypress', function (e) {
		if (e.which === 13) {
			e.preventDefault();
			validateRepository();
		}
	});

	// Handle branch selection change
	j('#repository_branch').on('change', function () {
		const selectedBranch = j(this).val();
		if (selectedBranch) {
			// Enable commit selection if we have commits for this branch
			if (window.gd_repo_data && window.gd_repo_data.commits && window.gd_repo_data.commits.length > 0) {
				j('.gd_commit_selection').removeClass('gd_hidden');
				populateCommits(selectedBranch);
			}
		}
	});

	// Handle commit selection change
	j('#specific_commit').on('change', function () {
		updateInstallButton();
	});
}

function init_enhanced_update_functionality() {
	// console.log('[Github Deployer]: Initializing enhanced update functionality...');

	// Open update modal
	j('.gd-update-with-options-btn').on('click', function (e) {
		// console.log('[Github Deployer]: Update with options button clicked');
		e.preventDefault();
		const packageSlug = j(this).data('package-slug');
		const packageType = j(this).data('package-type');

		// console.log('[Github Deployer]: Package slug:', packageSlug);
		// console.log('[Github Deployer]: Package type:', packageType);

		openUpdateModal(packageSlug, packageType);
	});

	// Close modal
	j('.gd-modal-close, .gd-modal-cancel').on('click', function () {
		// console.log('[Github Deployer]: Closing modal');
		closeUpdateModal();
	});

	// Close modal on outside click
	j('.gd-modal').on('click', function (e) {
		if (e.target === this) {
			// console.log('[Github Deployer]: Closing modal (outside click)');
			closeUpdateModal();
		}
	});

	// Handle branch selection in update modal
	j('#gd-update-branch').on('change', function () {
		const selectedBranch = j(this).val();
		// console.log('[Github Deployer]: Branch selected:', selectedBranch);
		if (selectedBranch && window.gd_update_repo_data && window.gd_update_repo_data.commits && window.gd_update_repo_data.commits.length > 0) {
			j('.gd-commit-section').removeClass('gd_hidden');
			populateUpdateCommits(selectedBranch);
		} else {
			j('.gd-commit-section').addClass('gd_hidden');
		}
		updateUpdateConfirmButton();
	});

	// Handle commit selection in update modal
	j('#gd-update-commit').on('change', function () {
		updateUpdateConfirmButton();
	});

	// Confirm update
	j('.gd-update-confirm').on('click', function () {
		// console.log('[Github Deployer]: Confirming update');
		performEnhancedUpdate();
	});

	// console.log('[Github Deployer]: Enhanced update functionality initialized');
}

function openUpdateModal(packageSlug, packageType) {
	// console.log('[Github Deployer]: Opening update modal for:', packageSlug, packageType);

	const $modal = j('#gd-update-modal');
	const $loading = $modal.find('.gd-update-loading');
	const $options = $modal.find('.gd-update-options');
	const $error = $modal.find('.gd-update-error');
	const $confirmBtn = $modal.find('.gd-update-confirm');

	// console.log('[Github Deployer]: Modal element found:', $modal.length > 0);

	// Reset modal state
	$loading.removeClass('gd_hidden');
	$options.addClass('gd_hidden');
	$error.addClass('gd_hidden');
	$confirmBtn.prop('disabled', true);

	// Store package info
	window.gd_update_package_info = {
		slug: packageSlug,
		type: packageType
	};

	// Show modal
	$modal.addClass('gd-modal-active');
	// console.log('[Github Deployer]: Modal should be visible now');

	// Fetch repository data
	fetchUpdateRepositoryData(packageSlug, packageType);
}

function closeUpdateModal() {
	const $modal = j('#gd-update-modal');
	$modal.removeClass('gd-modal-active');

	// Clear stored data
	window.gd_update_package_info = null;
	window.gd_update_repo_data = null;
}

function fetchUpdateRepositoryData(packageSlug, packageType) {
	// console.log('[Github Deployer]: Fetching update repository data for:', packageSlug, packageType);
	// console.log('[Github Deployer]: Nonce available:', typeof gd_update_repo_data_nonce !== 'undefined');

	j.ajax({
		url: ajaxurl,
		type: 'POST',
		data: {
			action: 'fetch_update_repository_data',
			package_slug: packageSlug,
			package_type: packageType,
			nonce: gd_update_repo_data_nonce
		},
		success: function (response) {
			// console.log('[Github Deployer]: AJAX response:', response);
			if (response.success) {
				window.gd_update_repo_data = response.data;
				populateUpdateModal(response.data);
			} else {
				showUpdateError(response.data.message);
			}
		},
		error: function (xhr, status, error) {
			// console.log('[Github Deployer]: AJAX error:', status, error);
			// console.log('[Github Deployer]: XHR:', xhr);
			showUpdateError('Failed to fetch repository data. Please try again.');
		}
	});
}

function populateUpdateModal(repoData) {
	const $modal = j('#gd-update-modal');
	const $loading = $modal.find('.gd-update-loading');
	const $options = $modal.find('.gd-update-options');
	const $currentBranch = $modal.find('.gd-current-branch');
	const $branchSelect = $modal.find('#gd-update-branch');
	const $commitSelect = $modal.find('#gd-update-commit');

	// Hide loading, show options
	$loading.addClass('gd_hidden');
	$options.removeClass('gd_hidden');

	// Set current branch
	$currentBranch.text(repoData.current_branch);

	// Populate branches
	$branchSelect.empty();
	$branchSelect.append('<option value="">Select a branch</option>');

	repoData.branches.forEach(function (branch) {
		const selected = branch === repoData.current_branch ? 'selected' : '';
		$branchSelect.append(`<option value="${branch}" ${selected}>${branch}</option>`);
	});

	// Clear commit select
	$commitSelect.empty();
	$commitSelect.append('<option value="">Use latest commit</option>');

	// Enable confirm button if branch is selected
	updateUpdateConfirmButton();
}

function populateUpdateCommits(branch) {
	const $commitSelect = j('#gd-update-commit');
	$commitSelect.empty();

	// Add default option
	$commitSelect.append('<option value="">Use latest commit</option>');

	// Add commits for the selected branch
	if (window.gd_update_repo_data && window.gd_update_repo_data.commits) {
		window.gd_update_repo_data.commits.forEach(function (commit) {
			const shortSha = commit.sha.substring(0, 7);
			const shortMessage = commit.message.length > 50 ? commit.message.substring(0, 50) + '...' : commit.message;
			$commitSelect.append(`<option value="${commit.sha}">${shortSha} - ${shortMessage}</option>`);
		});
	}
}

function updateUpdateConfirmButton() {
	const $modal = j('#gd-update-modal');
	const $confirmBtn = $modal.find('.gd-update-confirm');
	const selectedBranch = $modal.find('#gd-update-branch').val();

	if (selectedBranch) {
		$confirmBtn.prop('disabled', false);
	} else {
		$confirmBtn.prop('disabled', true);
	}
}

function showUpdateError(message) {
	const $modal = j('#gd-update-modal');
	const $loading = $modal.find('.gd-update-loading');
	const $error = $modal.find('.gd-update-error');
	const $errorMessage = $modal.find('.gd-error-message');

	$loading.addClass('gd_hidden');
	$error.removeClass('gd_hidden');
	$errorMessage.text(message);
}

function performEnhancedUpdate() {
	const $modal = j('#gd-update-modal');
	const selectedBranch = $modal.find('#gd-update-branch').val();
	const selectedCommit = $modal.find('#gd-update-commit').val();
	const $confirmBtn = $modal.find('.gd-update-confirm');
	const $confirmText = $confirmBtn.find('.text');

	if (!window.gd_update_package_info) {
		return;
	}

	// Determine the ref to use (commit takes precedence over branch)
	const selectedRef = selectedCommit || selectedBranch;

	// Show loading state
	$confirmBtn.prop('disabled', true);
	$confirmText.text('Updating...');

	j.ajax({
		url: ajaxurl,
		type: 'POST',
		data: {
			action: 'update_package_with_ref',
			package_slug: window.gd_update_package_info.slug,
			package_type: window.gd_update_package_info.type,
			selected_ref: selectedRef,
			nonce: gd_update_package_nonce
		},
		success: function (response) {
			if (response.success) {
				// Show success message
				$confirmText.text('Updated Successfully!');
				setTimeout(function () {
					closeUpdateModal();
					// Reload page to show updated state
					location.reload();
				}, 2000);
			} else {
				$confirmText.text('Update Failed');
				showUpdateError(response.data.message);
			}
		},
		error: function () {
			$confirmText.text('Update Failed');
			showUpdateError('Failed to update package. Please try again.');
		},
		complete: function () {
			setTimeout(function () {
				$confirmBtn.prop('disabled', false);
				$confirmText.text('Update Theme');
			}, 3000);
		}
	});
}

function validateRepository() {
	const repoUrl = j('#repository_url').val();
	const providerType = j('#provider_type').val();
	const $btn = j('#validate_repo_btn');
	const $result = j('#validation_result');

	if (!repoUrl || !providerType) {
		showValidationResult('Please enter both repository URL and select a provider.', 'error');
		return;
	}

	// Show loading state
	$btn.prop('disabled', true);
	$btn.find('.dashicons').removeClass('dashicons-search').addClass('dashicons-update');
	$btn.find('.dashicons-update').css('animation', 'rotation 1s infinite linear');

	j.ajax({
		url: ajaxurl,
		type: 'POST',
		data: {
			action: 'validate_repository',
			repo_url: repoUrl,
			provider_type: providerType,
			nonce: gd_validation_nonce
		},
		success: function (response) {
			if (response.success) {
				showValidationResult(response.data.message, 'success');
				// Store repository data for later use
				window.gd_validated_repo = {
					url: repoUrl,
					provider: providerType,
					slug: response.data.repo_slug,
					handle: response.data.repo_handle
				};
				// Show deployment options
				j('#deployment_options').removeClass('gd_hidden');
				updateInstallButton();
			} else {
				showValidationResult(response.data.message, 'error');
			}
		},
		error: function () {
			showValidationResult('Failed to validate repository. Please try again.', 'error');
		},
		complete: function () {
			// Reset button state
			$btn.prop('disabled', false);
			$btn.find('.dashicons').removeClass('dashicons-update').addClass('dashicons-search');
			$btn.find('.dashicons-search').css('animation', '');
		}
	});
}

function fetchRepositoryData() {
	const repoUrl = j('#repository_url').val();
	const providerType = j('#provider_type').val();
	const isPrivate = j('#is_private_repository').is(':checked');
	const accessToken = j('#access_token').val();
	const $btn = j('#fetch_repo_data_btn');
	const $branchSelect = j('#repository_branch');

	if (!repoUrl || !providerType) {
		alert('Please validate the repository first.');
		return;
	}

	// Show loading state
	$btn.prop('disabled', true);
	$btn.find('.dashicons').removeClass('dashicons-update').addClass('dashicons-update');
	$btn.find('.dashicons-update').css('animation', 'rotation 1s infinite linear');
	$branchSelect.html('<option value="">Loading branches...</option>');

	j.ajax({
		url: ajaxurl,
		type: 'POST',
		data: {
			action: 'fetch_repository_data',
			repo_url: repoUrl,
			provider_type: providerType,
			is_private: isPrivate,
			access_token: accessToken,
			nonce: gd_repo_data_nonce
		},
		success: function (response) {
			if (response.success) {
				// Store repository data
				window.gd_repo_data = response.data;

				// Populate branches
				populateBranches(response.data.branches, response.data.default_branch);

				// Show success message
				showValidationResult('Repository data fetched successfully!', 'success');
			} else {
				showValidationResult(response.data.message, 'error');
			}
		},
		error: function () {
			showValidationResult('Failed to fetch repository data. Please try again.', 'error');
		},
		complete: function () {
			// Reset button state
			$btn.prop('disabled', false);
			$btn.find('.dashicons').removeClass('dashicons-update').addClass('dashicons-update');
			$btn.find('.dashicons-update').css('animation', '');
		}
	});
}

function populateBranches(branches, defaultBranch) {
	const $branchSelect = j('#repository_branch');
	$branchSelect.empty();

	// Add default option
	$branchSelect.append('<option value="">Select a branch</option>');

	// Add branches
	branches.forEach(function (branch) {
		const selected = branch === defaultBranch ? 'selected' : '';
		$branchSelect.append(`<option value="${branch}" ${selected}>${branch}</option>`);
	});
}

function populateCommits(branch) {
	const $commitSelect = j('#specific_commit');
	$commitSelect.empty();

	// Add default option
	$commitSelect.append('<option value="">Use latest commit</option>');

	// Add commits for the selected branch
	if (window.gd_repo_data && window.gd_repo_data.commits) {
		window.gd_repo_data.commits.forEach(function (commit) {
			const shortSha = commit.sha.substring(0, 7);
			const shortMessage = commit.message.length > 50 ? commit.message.substring(0, 50) + '...' : commit.message;
			$commitSelect.append(`<option value="${commit.sha}">${shortSha} - ${shortMessage}</option>`);
		});
	}
}

function showValidationResult(message, type) {
	const $result = j('#validation_result');
	const className = type === 'success' ? 'gd_validation_success' : 'gd_validation_error';

	$result.removeClass('gd_validation_success gd_validation_error')
		.addClass(className)
		.html(`<span class="dashicons dashicons-${type === 'success' ? 'yes' : 'no'}"></span> ${message}`)
		.show();

	// Auto-hide after 5 seconds
	setTimeout(function () {
		$result.fadeOut();
	}, 5000);
}

function updateInstallButton() {
	const isValidated = window.gd_validated_repo;
	const hasBranch = j('#repository_branch').val();

	if (isValidated && hasBranch) {
		j('#install_theme_btn').prop('disabled', false);
	} else {
		j('#install_theme_btn').prop('disabled', true);
	}
}

function init_dashboard_listenes() {
	j('.gd_package_boxes a[data-copy-url-btn]').on('click', function (e) {
		e.preventDefault();
		const url = j(this).data('copy-url-btn');
		let $text_el = j(this).find('.text');
		navigator.clipboard.writeText(url).then(function () {
			$text_el.text(gd.copied_url_label);
			setTimeout(function () {
				$text_el.text(gd.copy_url_label);
			}, 2000);
		});
	});

	j('.gd_package_boxes a[data-show-ptd-btn]').on('click', function (e) {
		e.preventDefault();

		let $package_box_action = j(this).parent().find('.gd_package_box_action');
		$package_box_action.toggleClass('visible');
	});

	j('.gd_package_boxes button[data-trigger-ptd-btn]').on('click', function (e) {
		e.preventDefault();
		const endpoint_url = j(this).data('trigger-ptd-btn');
		let $parent_el = j(this);
		let $parent_text_el = j(this).find('.text');

		j.ajax({
			url: endpoint_url,
			type: 'GET',
			dataType: 'json',
			beforeSend: function () {
				$parent_el.addClass('loading');
				$parent_el.attr('disabled', true);

				$parent_text_el.text(gd.updating_now_label);
			}
		})
			.done(function (response) {
				if (response.success) {
					$parent_text_el.text(gd.update_completed_label);

					setTimeout(function () {
						if ($parent_el.data('package-type') === 'theme') {
							$parent_text_el.text(gd.update_theme_label);
						} else {
							$parent_text_el.text(gd.update_plugin_label);
						}
						$parent_el.removeAttr('disabled');
					}, 2000);
				} else {
					$parent_text_el.text(gd.error_label);
				}

				$parent_el.removeClass('loading');
			});
	});
}