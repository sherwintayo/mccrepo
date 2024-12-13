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

                    <fieldset>
                        <legend class="text-navy">Project Files:</legend>
                        <div class="pl-4">
                            <a class="btn btn-success"
                                href="<?php echo base_url . 'uploads/files/Files-' . $id . '.zip' ?>"><i
                                    class="fa fa-download"></i> Download Project Files</a>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="text-navy">SQL File:</legend>
                        <div class="pl-4">
                            <textarea id="summernote" class="form-control form-control-border summernote" readonly>
                                <?= isset($sql_path) ? nl2br(file_get_contents(html_entity_decode(base_url . $sql_path))) : "" ?>
                            </textarea>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="text-navy">Project Document:</legend>
                        <div class="pl-4">
                            <iframe src="<?= isset($document_path) ? base_url . $document_path : "" ?>" frameborder="0"
                                id="document_field" class="text-center w-100">Loading Document...</iframe>
                        </div>
                    </fieldset>
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