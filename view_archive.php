<?php
session_start();

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

if (!$is_logged_in) {
    header("Location: login.php");
    exit;
}

// Database and privilege validation for file download
if (isset($_GET['id']) && $_GET['id'] > 0) {
    // Fetch archive details from the database
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

    // Record count execution
    $stmt = $conn->prepare("INSERT INTO archive_counter (archive_id) VALUES (?)");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
}
?>
<style>
    #document_field {
        min-height: 80vh;
    }

    .download-info {
        border: 1px solid #ccc;
        padding: 10px;
        border-radius: 5px;
        margin-top: 10px;
    }


    #reasonTextarea,
    #submitReasonButton,
    #requestForm {
        display: none;
    }

    /* Hide textarea and submit button initially */
</style>
</head>
</style>
<div class="content py-4">
    <div class="col-12">
        <div class="card card-outline card-primary shadow rounded-0">
            <div class="card-header">
                <h3 class="card-title">
                    Archives - <?= isset($archive_code) ? htmlspecialchars($archive_code) : "" ?>
                </h3>
            </div>
            <div class="card-body rounded-0">
                <div class="container-fluid">
                    <h2><b><?= isset($title) ? htmlspecialchars($title) : "" ?></b></h2>
                    <small class="text-muted">Submitted by <b class="text-info"><?= htmlspecialchars($submitted) ?></b>
                        on <?= isset($date_created) ? date("F d, Y h:i A", strtotime($date_created)) : "" ?></small>
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


                    <!-- Download Button with Login Check -->
                    <div style="display: flex; align-items: center; margin-top: 20px;">
                        <h5 class="text-navy" style="flex: 1;">Download all Files</h5>
                        <button class="btn btn-success btn-flat" id="downloadButton">
                            <i class="fa fa-download"></i> Download All Files
                        </button>
                    </div>

                    <!-- Download Request Form (initially hidden) -->
                    <div id="requestForm" class="download-info">
                        <textarea id="reasonTextarea" class="form-control"
                            placeholder="Please provide a reason for downloading the files"></textarea>
                        <button class="btn btn-primary btn-flat mt-2" id="submitReasonButton">Submit Request</button>
                    </div>


                    <!-- File Information -->
                    <div class="download-info">
                        <p><strong>Project File:</strong>
                            <?= isset($folder_path) ? basename($folder_path) : "Not available" ?></p>
                        <p><strong>SQL File:</strong> <?= isset($sql_path) ? basename($sql_path) : "Not available" ?>
                        </p>
                        <p><strong>Document File:</strong>
                            <?= isset($document_path) ? basename($document_path) : "Not available" ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function () {
            $('.delete-data').click(function () {
                _conf("Are you sure to delete <b>Archive-<?= isset($archive_code) ? htmlspecialchars($archive_code) : "" ?></b>", "delete_archive")
            });
            $('.summernote').summernote({
                height: 200
            });
            $("#summernote").summernote("disable");
        });

        function delete_archive() {
            start_loader();
            $.ajax({
                url: _base_url_ + "classes/Master.php?f=delete_archive",
                method: "POST",
                data: { id: "<?= isset($id) ? htmlspecialchars($id) : "" ?>" },
                dataType: "json",
                error: err => {
                    console.log(err);
                    alert_toast("An error occurred.", 'error');
                    end_loader();
                },
                success: function (resp) {
                    if (typeof resp == 'object' && resp.status == 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted Successfully',
                            showConfirmButton: false,
                            timer: 1000
                        }).then(() => {
                            location.replace("./");
                        });
                    } else {
                        alert_toast("An error occurred.", 'error');
                        end_loader();
                    }
                }
            });
        }

    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://stuk.github.io/jszip-utils/dist/jszip-utils.js"></script>

    <!-- JavaScript for Handling Download Privilege -->
    <script>
        $(document).ready(function () {
            const isLoggedIn = <?= json_encode($is_logged_in) ?>;

            $('#downloadButton').click(function () {
                // Validate login state using an AJAX call to the server
                $.ajax({
                    url: 'validate_login.php',
                    method: 'POST',
                    success: function (response) {
                        const resp = JSON.parse(response);
                        if (resp.is_logged_in) {
                            $('#reasonTextarea').show();
                            $('#submitReasonButton').show();
                            $('#requestForm').show();
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Login Required',
                                text: 'You need to log in to request downloads.',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "login.php";
                                }
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to validate login state.'
                        });
                    }
                });
            });

            // Handle download request submission
            $('#submitReasonButton').click(function () {
                console.log('Submit button clicked'); // Debug log
                const reason = $('#reasonTextarea').val();
                const fileId = <?= isset($id) ? htmlspecialchars($id) : "null" ?>;

                if (reason.trim() === "") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Reason Required',
                        text: 'Please enter a reason for your download request.'
                    });
                    return;
                }

                console.log('Sending AJAX request...'); // Debug log
                $.ajax({
                    url: 'process_download_request.php',
                    method: 'POST',
                    data: { file_id: fileId, reason: reason },
                    success: function (response) {
                        console.log('AJAX response:', response); // Debug log
                        const resp = JSON.parse(response);
                        if (resp.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Request Submitted',
                                text: 'Your download request has been submitted for review.'
                            });
                            $('#reasonTextarea').val('').hide();
                            $('#submitReasonButton').hide();
                            $('#requestForm').hide();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Could not submit your request. Please try again later.'
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX error:', status, error); // Debug log
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred. Please try again.'
                        });
                    }
                });
            });

        });
    </script>