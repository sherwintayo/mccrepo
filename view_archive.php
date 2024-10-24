<?php
session_start(); // Start session to track login status.

if(isset($_GET['id']) && $_GET['id'] > 0){
    $stmt = $conn->prepare("SELECT a.* FROM `archive_list` a WHERE a.id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $qry = $stmt->get_result();

    if($qry->num_rows > 0){
        foreach($qry->fetch_array() as $k => $v){
            if(!is_numeric($k))
            $$k = $v;
        }
    }
    $submitted = "N/A";
    if(isset($student_id)){
        $stmt = $conn->prepare("SELECT * FROM student_list WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $student = $stmt->get_result();

        if($student->num_rows > 0){
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
    #document_field{
        min-height:80vh
    }
    /* Custom styles to ensure modal is properly centered */
.modal-dialog {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
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
                    <small class="text-muted">Submitted by <b class="text-info"><?= htmlspecialchars($submitted) ?></b> on <?= isset($date_created) ? date("F d, Y h:i A", strtotime($date_created)) : "" ?></small>
                    <?php if(isset($student_id) && $_settings->userdata('login_type') == "2" && $student_id == $_settings->userdata('id')): ?>
                        <div class="form-group">
                            <a href="./?page=submit-archive&id=<?= isset($id) ? htmlspecialchars($id) : "" ?>" class="btn btn-flat btn-default bg-navy btn-sm"><i class="fa fa-edit"></i> Edit</a>
                            <button type="button" data-id="<?= isset($id) ? htmlspecialchars($id) : "" ?>" class="btn btn-flat btn-danger btn-sm delete-data"><i class="fa fa-trash"></i> Delete</button>
                        </div>
                    <?php endif; ?>
                    <hr>
                    <center>
                        <img src="<?= validate_image(isset($banner_path) ? htmlspecialchars($banner_path) : "") ?>" alt="Banner Image" id="banner-img" class="img-fluid border bg-gradient-dark">
                    </center>
                    <fieldset>
                        <legend class="text-navy">Project Year:</legend>
                        <div class="pl-4"><large><?= isset($year) ? htmlspecialchars($year) : "----" ?></large></div>
                    </fieldset>
                    <fieldset>
                        <legend class="text-navy">Abstract:</legend>
                        <div class="pl-4">
                            <large><?= isset($abstract) ? htmlspecialchars($abstract) : "" ?></large>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="text-navy">Members:</legend>
                        <div class="pl-4"><large><?= isset($members) ? htmlspecialchars($members) : "" ?></large></div>
                    </fieldset>

                    <?php if(isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == true): ?>
                    <!-- Display download buttons that open the modal -->
                    <fieldset>
                        <legend class="text-navy">Project Files:</legend>
                        <button class="btn btn-success request-download-btn" data-file-id="<?= htmlspecialchars($id) ?>" data-file-type="files">Download Project files</button>
                    </fieldset>
                    <fieldset>
                        <legend class="text-navy">SQL file:</legend>
                        <button class="btn btn-success request-download-btn" data-file-id="<?= htmlspecialchars($id) ?>" data-file-type="sql">Download SQL file</button>
                    </fieldset>
                    <fieldset>
                        <legend class="text-navy">Project Document:</legend>
                        <button class="btn btn-success request-download-btn" data-file-id="<?= htmlspecialchars($id) ?>" data-file-type="pdf">Download Project Document</button>
                    </fieldset>
                <?php else: ?>
                    <!-- Redirect to login if not logged in -->
                    <fieldset>
                        <legend class="text-navy">Project Files:</legend>
                        <a class="btn btn-success" href="login.php?redirect=download&file_type=zip&id=<?= htmlspecialchars($id) ?>">Download Project files</a>
                    </fieldset>
                    <fieldset>
                        <legend class="text-navy">SQL file:</legend>
                        <a class="btn btn-success" href="login.php?redirect=download&file_type=sql&id=<?= htmlspecialchars($id) ?>">Download SQL file</a>
                    </fieldset>
                    <fieldset>
                        <legend class="text-navy">Project Document:</legend>
                        <a class="btn btn-success" href="login.php?redirect=download&file_type=pdf&id=<?= htmlspecialchars($id) ?>">Download Project Document</a>
                    </fieldset>
                <?php endif; ?>
                </div>
                                <!-- Modal HTML -->
                <div class="modal fade" id="requestDownloadModal"  role="dialog" aria-labelledby="requestDownloadModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="requestDownloadModalLabel">Request Download</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="downloadRequestForm">
                                    <div class="form-group">
                                        <label for="reason">Reason for Download</label>
                                        <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Please provide the reason for downloading this file." required></textarea>
                                    </div>
                                    <input type="hidden" id="fileId" name="fileId" value="">
                                    <button type="submit" class="btn btn-primary">Submit Request</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $('.delete-data').click(function(){
            _conf("Are you sure to delete <b>Archive-<?= isset($archive_code) ? htmlspecialchars($archive_code) : "" ?></b>", "delete_archive")
        });
        $('.summernote').summernote({
            height: 200
        });
        $("#summernote").summernote("disable");
    });

    function delete_archive(){
        start_loader();
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=delete_archive",
            method: "POST",
            data: {id: "<?= isset($id) ? htmlspecialchars($id) : "" ?>"},
            dataType: "json",
            error: err => {
                console.log(err);
                alert_toast("An error occurred.", 'error');
                end_loader();
            },
            success: function(resp){
                if(typeof resp == 'object' && resp.status == 'success'){
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
    $(document).ready(function () {
        // Show the modal when the download button is clicked
        $('.request-download-btn').click(function () {
            var fileId = $(this).data('file-id');
            var fileType = $(this).data('file-type');
            $('#fileId').val(fileId);
            $('#requestDownloadModal').modal('show');
        });

        // Handle the form submission to send the download request to the server
        $('#downloadRequestForm').submit(function (e) {
            e.preventDefault();
            var reason = $('#reason').val();
            var fileId = $('#fileId').val();

            if (reason.trim() === '') {
                alert_toast("Please provide a reason for your request.");
                return;
            }

            // Send AJAX request to the server to process the download request
            $.ajax({
                url: 'process_download_request.php',
                method: 'POST',
                data: { fileId: fileId, reason: reason },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        alert_toast("Your request has been sent to the admin.");
                        $('#requestDownloadModal').modal('hide');
                    } else {
                        alert_toast("Failed to send request. Please try again.");
                    }
                },
                error: function () {
                    alert_toast("An error occurred while sending your request. Please try again.");
                }
            });
        });
    });
</script>
