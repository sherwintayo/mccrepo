<?php

session_start(); // Start session

// Check if user has download privileges (logged in and can download)
$user_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == true;
$can_download = isset($_SESSION['can_download']) && $_SESSION['can_download'] == true;

if (isset($_GET['id']) && $_GET['id'] > 0) {
    $stmt = $conn->prepare("SELECT a.* FROM `archive_list` a WHERE a.id = ?");
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
            $submitted = $res['email'];
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
        min-height: 80vh
    }
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
                            <large><?= isset($abstract) ? html_entity_decode($abstract) : "" ?></large>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="text-navy">Members:</legend>
                        <div class="pl-4">
                            <large><?= isset($members) ? html_entity_decode($members) : "" ?></large>
                        </div>
                    </fieldset>
                    <!-- Project Files -->
                    <fieldset>
                        <legend class="text-navy">Project Files:</legend>
                        <?php if ($user_logged_in && $can_download): ?>
                            <a class="btn btn-success"
                                href="<?= base_url . 'uploads/files/Files-' . htmlspecialchars($id) . '.zip' ?>">Download
                                Project files</a>
                        <?php else: ?>
                            <a class="btn btn-success"
                                href="login.php?redirect=download&file_type=zip&id=<?= htmlspecialchars($id) ?>">Download
                                Project files</a>
                        <?php endif; ?>
                    </fieldset>

                    <!-- SQL File -->
                    <fieldset>
                        <legend class="text-navy">SQL file:</legend>
                        <?php if ($user_logged_in && $can_download): ?>
                            <a class="btn btn-success"
                                href="<?= base_url . 'uploads/sql/SQL-' . htmlspecialchars($id) . '.zip' ?>">Download SQL
                                file</a>
                        <?php else: ?>
                            <a class="btn btn-success"
                                href="login.php?redirect=download&file_type=sql&id=<?= htmlspecialchars($id) ?>">Download
                                SQL file</a>
                        <?php endif; ?>
                    </fieldset>

                    <!-- Project Document -->
                    <fieldset>
                        <legend class="text-navy">Project Document:</legend>
                        <?php if ($user_logged_in && $can_download): ?>
                            <a class="btn btn-success"
                                href="<?= base_url . 'uploads/pdf/Document-' . htmlspecialchars($id) . '.zip' ?>">Download
                                Project Document</a>
                        <?php else: ?>
                            <a class="btn btn-success"
                                href="login.php?redirect=download&file_type=pdf&id=<?= htmlspecialchars($id) ?>">Download
                                Project Document</a>
                        <?php endif; ?>
                    </fieldset>
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