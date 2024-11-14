<?php
session_start();
require 'config.php'; // Ensure this includes the database connection

// Check if the user is logged in
$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

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
    #submitReasonButton {
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


                    <!-- Single "Download All Files" Button with Description -->
                    <div style="display: flex; align-items: center; margin-top: 20px;">
                        <h5 class="text-navy" style="flex: 1;">Download all Files</h5>
                        <button class="btn btn-success btn-flat" id="downloadButton">
                            <i class="fa fa-download"></i> Download All Files
                        </button>
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
    <script>
        $(document).ready(function () {
            // Check login status from PHP in JavaScript
            const isLoggedIn = <?= json_encode($is_logged_in) ?>;

            // Handle download button click
            $('#downloadButton').click(function () {
                if (!isLoggedIn) {
                    // Redirect to login page if not logged in
                    window.location.href = "login.php";
                    return;
                }

                // If logged in, proceed with file download
                const zip = new JSZip();

                // Define file paths from PHP variables
                const documentPath = "<?= base_url . 'uploads/pdf/Document-' . htmlspecialchars($id) . '.zip' ?>";
                const projectPath = "<?= base_url . 'uploads/files/Files-' . htmlspecialchars($id) . '.zip' ?>";
                const sqlPath = "<?= base_url . 'uploads/sql/SQL-' . htmlspecialchars($id) . '.zip' ?>";

                // Add files to the zip if they exist
                if ("<?= isset($document_path) ?>") {
                    zip.file("Document_File.zip", urlToPromise(documentPath));
                }
                if ("<?= isset($folder_path) ?>") {
                    zip.file("Project_File.zip", urlToPromise(projectPath));
                }
                if ("<?= isset($sql_path) ?>") {
                    zip.file("SQL_File.zip", urlToPromise(sqlPath));
                }

                // Generate and download the zip
                zip.generateAsync({ type: "blob" }).then(function (content) {
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(content);
                    link.download = "All_Files.zip";
                    link.click();
                });
            });

            // Convert URL to Promise to fetch files
            function urlToPromise(url) {
                return new Promise(function (resolve, reject) {
                    JSZipUtils.getBinaryContent(url, function (err, data) {
                        if (err) {
                            reject(err);
                        } else {
                            resolve(data);
                        }
                    });
                });
            }
        });
    </script>