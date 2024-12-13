<?php
session_start();

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

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

    .card {
        margin: 10px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        font-size: 1.25rem;
        font-weight: bold;
        background-color: #f7f7f7;
        padding: 10px;
    }

    .status-icon {
        display: flex;
        align-items: center;
        font-size: 1.25rem;
        margin-bottom: 10px;
    }

    .status-icon span {
        margin-left: 10px;
    }

    .available {
        color: green;
    }

    .not-available {
        color: gray;
    }

    .download-info {
        margin-top: 20px;
    }

    .abstract,
    .details,
    .members {
        margin-top: 15px;
    }

    /* Hide textarea and submit button initially */
</style>
</head>
</style>
<div class="content py-4">
    <div class="col-12">
        <div class="card shadow rounded-0">
            <div class="card-header">
                Archives - <?= isset($archive_code) ? htmlspecialchars($archive_code) : "" ?>
            </div>
            <div class="card-body rounded-0">
                <img src="<?= validate_image(isset($banner_path) ? htmlspecialchars($banner_path) : '') ?>"
                    alt="Banner Image" class="img-fluid border bg-gradient-dark mb-3">
                <h2><?= isset($title) ? htmlspecialchars($title) : "" ?></h2>
                <small class="text-muted">Submitted by <b><?= htmlspecialchars($submitted) ?></b> on
                    <?= isset($date_created) ? date("F d, Y h:i A", strtotime($date_created)) : "" ?></small>

                <div class="details">
                    <h5>Project Year:</h5>
                    <p><?= isset($year) ? htmlspecialchars($year) : "----" ?></p>
                </div>

                <div class="abstract">
                    <h5>Abstract:</h5>
                    <p><?= isset($abstract) ? html_entity_decode($abstract) : "" ?></p>
                </div>

                <div class="members">
                    <h5>Members:</h5>
                    <p><?= isset($members) ? html_entity_decode($members) : "" ?></p>
                </div>

                <!-- File Cards -->
                <div class="download-info">
                    <?php
                    $files = [
                        'Project File' => $folder_path ?? null,
                        'SQL File' => $sql_path ?? null,
                        'Document File' => $document_path ?? null
                    ];
                    foreach ($files as $file_type => $file_path): ?>
                        <div class="card">
                            <img src="placeholder-image.png" alt="<?= $file_type ?>" class="img-fluid">
                            <div class="status-icon <?= $file_path ? 'available' : 'not-available' ?>">
                                <i class="fa <?= $file_path ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                                <span><?= $file_path ? 'Available' : 'Not Available' ?></span>
                            </div>
                            <p><strong><?= $file_type ?>:</strong>
                                <?= $file_path ? basename($file_path) : "Not available" ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Download Button -->
                <div style="display: flex; align-items: center; margin-top: 20px;">
                    <h5 class="text-navy" style="flex: 1;">Download all Files</h5>
                    <button class="btn btn-success btn-flat" id="downloadButton">
                        <i class="fa fa-download"></i> Download All Files
                    </button>
                </div>

                <!-- Download Request Form -->
                <div id="requestForm" class="download-info">
                    <textarea id="reasonTextarea" class="form-control"
                        placeholder="Please provide a reason for downloading the files"></textarea>
                    <button class="btn btn-primary btn-flat mt-2" id="submitReasonButton">Submit Request</button>
                </div>
            </div>
        </div>



        <!-- <div class="card card-outline card-primary shadow rounded-0">
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
                            <large><?= isset($abstract) ? html_entity_decode($abstract) : "" ?></large>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="text-navy">Members:</legend>
                        <div class="pl-4">
                            <large><?= isset($members) ? html_entity_decode($members) : "" ?></large>
                        </div>
                    </fieldset>

                    <div style="display: flex; align-items: center; margin-top: 20px;">
                        <h5 class="text-navy" style="flex: 1;">Download all Files</h5>
                        <button class="btn btn-success btn-flat" id="downloadButton">
                            <i class="fa fa-download"></i> Download All Files
                        </button>
                    </div>

                  
                    <div id="requestForm" class="download-info">
                        <textarea id="reasonTextarea" class="form-control"
                            placeholder="Please provide a reason for downloading the files"></textarea>
                        <button class="btn btn-primary btn-flat mt-2" id="submitReasonButton">Submit Request</button>
                    </div>


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
        </div> -->
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
                if (isLoggedIn) {
                    // Show request form when logged in
                    $('#reasonTextarea').show();
                    $('#submitReasonButton').show();
                    $('#requestForm').show();
                } else {
                    // Redirect to login page when not logged in
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
                                text: 'Could not submit your request. You need to login again.',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "login.php";
                                }
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