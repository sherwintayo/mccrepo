<?php
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $qry = $conn->query("SELECT a.* FROM `archive_list` a where a.id = '{$_GET['id']}'");
    if ($qry->num_rows) {
        foreach ($qry->fetch_array() as $k => $v) {
            if (!is_numeric($k))
                $$k = $v;
        }
    }
    $submitted = "N/A";
    if (isset($student_id)) {
        $student = $conn->query("SELECT * FROM student_list where id = '{$student_id}'");
        if ($student->num_rows > 0) {
            $res = $student->fetch_array();
            $submitted = $res['email'];
        }
    }
}
?>
<style>
    #document_field {
        min-height: 80vh
    }
</style>
<link rel="stylesheet" href="<?php echo base_url ?>myStyles/projectdetails.css?v=<?php echo time(); ?>">

<div class="content py-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Project Details
                </h3>
            </div>
            <div class="card-body rounded-0">
                <div class="container-fluid">
                    <center>
                        <img src="<?= validate_image(isset($banner_path) ? htmlspecialchars($banner_path) : "") ?>"
                            alt="Banner Image" id="banner-img" class="img-fluid border bg-gradient-dark">
                    </center>
                    <div class="title">
                        <h2><b><?= isset($title) ? htmlspecialchars($title) : "" ?></b></h2>
                        <p>Archive Code - <?= isset($archive_code) ? htmlspecialchars($archive_code) : "" ?></p>
                        <p class="text-muted">Submitted by <b class="text-info"><?= htmlspecialchars($submitted) ?></b>
                            on <?= isset($date_created) ? date("F d, Y h:i A", strtotime($date_created)) : "" ?></p>
                    </div>

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

                    <fieldset>
                        <legend class="text-navy">Project Year:</legend>
                        <div class="pl-4">
                            <large><?= isset($year) ? htmlspecialchars($year) : "----" ?></large>
                        </div>
                    </fieldset>

                    <div class="download-info">
                        <div class="row mt-4">
                            <!-- Project File Card -->
                            <div class="col-md-4">
                                <div class="file-card">
                                    <div class="card-image">
                                        <img src="dist/img/projects.gif" alt="Project File">
                                    </div>
                                    <div class="card-header-p text-center">
                                        <h5 class="card-title-p">Project File</h5>
                                    </div>
                                    <div class="card-body-p text-center">
                                        <?= isset($folder_path) && !empty($folder_path) ?
                                            "<span class='text-success'><i class='fa fa-check-circle fa-3x'></i><br>Available</span>" :
                                            "<span class='text-secondary'><i class='fa fa-exclamation-circle fa-3x'></i><br>Not Available</span>" ?>
                                    </div>
                                </div>
                            </div>

                            <!-- SQL File Card -->
                            <div class="col-md-4">
                                <div class="file-card">
                                    <div class="card-image">
                                        <img src="dist/img/sql.gif" alt="SQL File">
                                    </div>
                                    <div class="card-header-p text-center">
                                        <h5 class="card-title-p">SQL File</h5>
                                    </div>
                                    <div class="card-body-p text-center">
                                        <?= isset($sql_path) && !empty($sql_path) ?
                                            "<span class='text-success'><i class='fa fa-check-circle fa-3x'></i><br>Available</span>" :
                                            "<span class='text-secondary'><i class='fa fa-exclamation-circle fa-3x'></i><br>Not Available</span>" ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Document File Card -->
                            <div class="col-md-4">
                                <div class="file-card">
                                    <div class="card-image">
                                        <img src="dist/img/documents.gif" alt="Document File">
                                    </div>
                                    <div class="card-header-p text-center">
                                        <h5 class="card-title-p">Document File</h5>
                                    </div>
                                    <div class="card-body-p text-center">
                                        <?= isset($document_path) && !empty($document_path) ?
                                            "<span class='text-success'><i class='fa fa-check-circle fa-3x'></i><br>Available</span>" :
                                            "<span class='text-secondary'><i class='fa fa-exclamation-circle fa-3x'></i><br>Not Available</span>" ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('.delete-data').click(function () {
            _conf("Are you sure to delete <b>Archive-<?= isset($archive_code) ? $archive_code : "" ?></b>", "delete_archive")
        })
        $('.summernote').summernote({
            height: 200
        })
        $("#summernote").summernote("disable");
    })
    function delete_archive() {
        start_loader();
        $.ajax({
            url: _base_url_ + "classes/Master.php?f=delete_archive",
            method: "POST",
            data: { id: "<?= isset($id) ? $id : "" ?>" },
            dataType: "json",
            error: err => {
                console.log(err)
                alert_toast("An error occured.", 'error');
                end_loader();
            },
            success: function (resp) {
                if (typeof resp == 'object' && resp.status == 'success') {
                    location.replace("./");
                } else {
                    alert_toast("An error occured.", 'error');
                    end_loader();
                }
            }
        })
    }
</script>