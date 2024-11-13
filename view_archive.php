<?php
session_start(); // Start session to track login status.

// Assume $user_id is obtained from the database after successful login
$_SESSION['user_id'] = $user_id;
$_SESSION['user_logged_in'] = true;

if (isset($_GET['id']) && $_GET['id'] > 0) {
    $stmt = $conn->prepare("SELECT a.* FROM archive_list a WHERE a.id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $qry = $stmt->get_result();

    if ($qry->num_rows > 0) {
        foreach ($qry->fetch_array() as $k => $v) {
            if (!is_numeric($k))
                $$k = $v;
        }
    }
    $submitted = "N/A";
    if (isset($student_id)) {
        $stmt = $conn->prepare("SELECT * FROM student_list WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $student = $stmt->get_result();

        if ($student->num_rows > 0) {
            $res = $student->fetch_array();
            $submitted = $res['lastname'];
        }
    }

    // Insert view record in archive_counter table
    $stmt = $conn->prepare("INSERT INTO archive_counter (archive_id) VALUES (?)");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
}
?>
<style>
    #document_field {
        min-height: 80vh;
    }

    .modal-dialog {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
    }

    .request-form {
        display: none;
        margin-top: 10px;
    }
</style>

<div class="content py-4">
    <div class="col-12">
        <div class="card card-outline card-primary shadow rounded-0">
            <div class="card-header">
                <h3 class="card-title">Archives - <?= isset($archive_code) ? htmlspecialchars($archive_code) : "" ?>
                </h3>
            </div>
            <div class="card-body rounded-0">
                <div class="container-fluid">
                    <h2><b><?= isset($title) ? htmlspecialchars($title) : "" ?></b></h2>
                    <small class="text-muted">Submitted by <b class="text-info"><?= htmlspecialchars($submitted) ?></b>
                        on <?= isset($date_created) ? date("F d, Y h:i A", strtotime($date_created)) : "" ?></small>

                    <!-- Edit/Delete Buttons if Student is Owner -->
                    <?php if (isset($student_id) && $_settings->userdata('login_type') == "2" && $student_id == $_settings->userdata('id')): ?>
                        <div class="form-group">
                            <a href="./?page=submit-archive&id=<?= isset($id) ? htmlspecialchars($id) : "" ?>"
                                class="btn btn-flat btn-default bg-navy btn-sm"><i class="fa fa-edit"></i> Edit</a>
                            <button type="button" data-id="<?= isset($id) ? htmlspecialchars($id) : "" ?>"
                                class="btn btn-flat btn-danger btn-sm delete-data"><i class="fa fa-trash"></i>
                                Delete</button>
                        </div>
                    <?php endif; ?>
                    <hr>
                    <center>
                        <img src="<?= validate_image(isset($banner_path) ? htmlspecialchars($banner_path) : "") ?>"
                            alt="Banner Image" id="banner-img" class="img-fluid border bg-gradient-dark">
                    </center>

                    <!-- Display Project Details -->
                    <fieldset>
                        <legend class="text-navy">Project Year:</legend>
                        <div class="pl-4">
                            <large><?= isset($year) ? htmlspecialchars($year) : "----" ?></large>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="text-navy">Abstract:</legend>
                        <div class="pl-4">
                            <large><?= isset($abstract) ? htmlspecialchars($abstract) : "" ?></large>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="text-navy">Members:</legend>
                        <div class="pl-4">
                            <large><?= isset($members) ? htmlspecialchars($members) : "" ?></large>
                        </div>
                    </fieldset>

                    <!-- Download Request Buttons and Textareas for Reason -->
                    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == true): ?>
                        <fieldset>
                            <legend class="text-navy">Project Files:</legend>
                            <button class="btn btn-success request-download-btn" data-file-id="<?= htmlspecialchars($id) ?>"
                                data-file-type="files">Download Project Files</button>
                            <div class="request-form" id="request-form-files">
                                <textarea class="form-control reason" placeholder="Reason for download" rows="2"></textarea>
                                <button class="btn btn-primary submit-request-btn"
                                    data-file-id="<?= htmlspecialchars($id) ?>" data-file-type="files">Submit
                                    Request</button>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend class="text-navy">SQL File:</legend>
                            <button class="btn btn-success request-download-btn" data-file-id="<?= htmlspecialchars($id) ?>"
                                data-file-type="sql">Download SQL File</button>
                            <div class="request-form" id="request-form-sql">
                                <textarea class="form-control reason" placeholder="Reason for download" rows="2"></textarea>
                                <button class="btn btn-primary submit-request-btn"
                                    data-file-id="<?= htmlspecialchars($id) ?>" data-file-type="sql">Submit Request</button>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend class="text-navy">Project Document:</legend>
                            <button class="btn btn-success request-download-btn" data-file-id="<?= htmlspecialchars($id) ?>"
                                data-file-type="pdf">Download Project Document</button>
                            <div class="request-form" id="request-form-pdf">
                                <textarea class="form-control reason" placeholder="Reason for download" rows="2"></textarea>
                                <button class="btn btn-primary submit-request-btn"
                                    data-file-id="<?= htmlspecialchars($id) ?>" data-file-type="pdf">Submit Request</button>
                            </div>
                        </fieldset>
                    <?php else: ?>
                        <!-- Redirect to login if not logged in -->
                        <fieldset>
                            <legend class="text-navy">Project Files:</legend>
                            <a class="btn btn-success"
                                href="login.php?redirect=download&file_type=zip&id=<?= htmlspecialchars($id) ?>">Download
                                Project Files</a>
                        </fieldset>
                        <fieldset>
                            <legend class="text-navy">SQL File:</legend>
                            <a class="btn btn-success"
                                href="login.php?redirect=download&file_type=sql&id=<?= htmlspecialchars($id) ?>">Download
                                SQL File</a>
                        </fieldset>
                        <fieldset>
                            <legend class="text-navy">Project Document:</legend>
                            <a class="btn btn-success"
                                href="login.php?redirect=download&file_type=pdf&id=<?= htmlspecialchars($id) ?>">Download
                                Project Document</a>
                        </fieldset>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Show the specific request form below the clicked button
        $('.request-download-btn').click(function () {
            var fileType = $(this).data('file-type');
            $('.request-form').hide(); // Hide all other request forms
            $('#request-form-' + fileType).toggle(); // Show the selected form
        });

        // Submit the download request via AJAX
        $('.submit-request-btn').click(function () {
            var fileId = $(this).data('file-id');
            var fileType = $(this).data('file-type');
            var reason = $('#request-form-' + fileType + ' .reason').val();

            if ($.trim(reason) === '') {
                alert("Please provide a reason for your request.");
                return;
            }

            // Console log the data being sent for debugging
            console.log("Submitting request:", { fileId, reason, fileType });

            $.ajax({
                url: 'process_download_request.php',
                method: 'POST',
                data: { fileId: fileId, reason: reason, fileType: fileType },
                dataType: 'json',
                success: function (response) {
                    console.log("Server response:", response); // Debugging output
                    if (response.status === 'success') {
                        alert("Your request has been submitted for review.");
                        $('#request-form-' + fileType).hide();
                        $('#request-form-' + fileType + ' .reason').val(''); // Clear the textarea
                    } else {
                        alert("Failed to submit request. Please try again.");
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error("AJAX error:", textStatus, errorThrown); // Log error
                    alert("An error occurred while sending your request.");
                }
            });
        });
    });

</script>