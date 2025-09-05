<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Organization proration admin view page
 */
?>
<!-- eslint-disable tailwindcss/no-custom-classname -->
<div class="wrap">
    <h1>
        <?php _e('Org Proration', 'mobilo'); ?>
    </h1>

    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary mt-4" data-bs-toggle="modal" data-bs-target="#addOrgProrationModal">
        Add new Organization Proration
    </button>
</div>
<div class="wrap">

    <!-- Table UI -->
    <table id="orgProrationTable" class="widefat fixed" cellspacing="0" style="width:100%">
        <thead>
            <tr>
                <th class="manage-column column-org-id" scope="col">
                    <?php _e('OrgId', 'mobilo'); ?>
                </th>
                <th class="manage-column column-org-id" scope="col">
                    <?php _e('UserIds', 'mobilo'); ?>
                </th>
                <th class="manage-column column-proration-date" scope="col">
                    <?php _e('Proration Date', 'mobilo'); ?>
                </th>
                <th class="manage-column column-proration-date" scope="col">
                    <?php _e('Updated At', 'mobilo'); ?>
                </th>
                <th class="manage-column column-actions" scope="col">
                    <?php _e('Actions', 'mobilo'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!$data || empty($data)) {
                echo '<tr><td colspan="4">' . __('No data found', 'mobilo') . '</td></tr>';
            } else {
                foreach ($data as $row) {
                    echo '<tr>
                    <td>' . $row->org_id . '</td>
                    <td>' . $row->admin_user_ids . '</td>
                    <td>' . $row->expiry_date . '</td>
                    <td>' . $row->updated_at . '</td>
                    <td><button class="btn btn-danger delete-row" data-id="' . $row->id . '"
                        >Delete</button></td>
                    </tr>';
                }
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade mt-5" id="addOrgProrationModal" tabindex="-1" aria-labelledby="addOrgProrationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="addOrgProrationModalLabel">Add New Organization Proration</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post" id="addOrgProrationForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="org_id" class="form-label">Organization Id</label>
                        <input type="text" class="form-control" id="org_id" placeholder="Org id"
                            aria-describedby="orgIdHelpBlock">
                        <div id="orgIdHelpBlock" class="form-text">
                            Enter the organization id.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="admin_emails" class="form-label">Email addresses</label>
                        <input type="text" class="form-control" id="admin_emails"
                            placeholder="name@example.com,admin@abc.com" aria-describedby="emailsHelpBlock">
                        <div id="emailsHelpBlock" class="form-text">
                            Multiple emails should be separated by comma(,).
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Proration date</label>
                        <input type="date" class="form-control" id="expiry_date" aria-describedby="expiryDateHelpBlock">
                        <div id="expiryDateHelpBlock" class="form-text">
                            Choose the proration date (future date only).
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="addOrgProration">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        try {
            jQuery('#orgProrationTable').DataTable({
                responsive: true
            });
        } catch (error) {
            console.error('DataTable-error :>> ', error);
        }

        const handleFormSubmission = async (e) => {
            e.preventDefault();
            // disable submit button
            jQuery('#addOrgProration').attr('disabled', 'disabled');

            const orgId = jQuery('#org_id').val();
            const adminMails = jQuery('#admin_emails').val();
            const expiryDate = jQuery('#expiry_date').val();

            if (!orgId || !adminMails || !expiryDate) {
                alert('All fields are required, please try again.');
                return;
            }
            // expiryDate must be in future
            if (new Date(expiryDate) < new Date()) {
                alert('Proration date must be in the future, please try again.');
                return;
            }

            // create unique set of admin emails
            const adminMailArray = new Set(adminMails.split(','));
            // validate admin emails
            for (let i = 0; i < adminMailArray.length; i++) {
                if (!adminMailArray[i].includes('@')) {
                    alert('Invalid email address, please try again.');
                    return;
                }
            }

            const data = {
                action: `${lwmc.ajaxPrefix}add_org_proration`,
                org_id: orgId,
                admin_mails: [...adminMailArray],
                expiry_date: expiryDate,
            };

            const existingBody = jQuery('.modal-body').html();
            jQuery('.modal-body').html('<div class="spinner-border text-primary" role="status"></div> <span class="">Creating proration...</span>');
            try {
                jQuery.ajax({
                    type: 'POST',
                    url: lwmc.ajaxUrl,
                    data: data,
                    success: function (res) {
                        console.log('res :>> ', res);
                        if (res?.errors) {
                            const errorMsg = Object.values(res?.errors)[0]
                            alert(`${errorMsg || 'An error occurred while processing your request. Please try again.'}`);
                            jQuery('.modal-body').html(existingBody);
                            return;
                        }
                        alert(res?.message || 'Proration created successfully.');
                        jQuery('#addOrgProrationModal').modal('hide');
                        location.reload();
                        jQuery('.modal-body').html(existingBody);
                    },
                    error: function (error, textStatus, errorThrown) {
                        let errorMsg = error.responseJSON.errors
                        errorMsg = Object.values(errorMsg)[0]
                        alert(`${errorMsg || 'An error occurred while processing your request. Please try again.'}`);
                        jQuery('.modal-body').html(existingBody);

                        // enable submit button
                        jQuery('#addOrgProration').removeAttr('disabled');
                    }
                });

            } catch (error) {
                console.log('error :>> ', error);
            }
        };

        jQuery("#addOrgProrationForm").on("submit", handleFormSubmission);


        // Function to attach the delete event
        const attachDeleteEvent = () => {
            jQuery('.delete-row').off('click').on('click', function () {
                const id = jQuery(this).data('id');
                if (confirm('Are you sure you want to delete this entry?')) {
                    // disabled the all the delete button
                    jQuery('.delete-row').attr('disabled', 'disabled');

                    jQuery.ajax({
                        type: 'POST',
                        url: lwmc.ajaxUrl,
                        data: {
                            action: `${lwmc.ajaxPrefix}delete_org_proration`,
                            id,
                        },
                        success: function (res) {
                            if (res?.errors) {
                                const errorMsg = Object.values(res?.errors)[0];
                                alert(`${errorMsg || 'An error occurred while processing your request. Please try again.'}`);
                                return;
                            }
                            alert(res?.message || 'Proration deleted successfully.');
                            location.reload();
                        },
                        error: function (error, textStatus, errorThrown) {
                            let errorMsg = error.responseJSON.errors;
                            errorMsg = Object.values(errorMsg)[0];
                            alert(`${errorMsg || 'An error occurred while processing your request. Please try again.'}`);

                            // enable the delete button
                            jQuery('.delete-row').removeAttr('disabled');
                        }
                    });
                }
            });
        };

        // Attach delete event initially
        attachDeleteEvent();

        // Re-attach delete event on table draw (pagination, sorting, etc.)
        jQuery('#orgProrationTable').on('draw.dt', function () {
            attachDeleteEvent();
        });
    });
</script>