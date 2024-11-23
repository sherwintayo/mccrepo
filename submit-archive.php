<?php
if (isset($_GET['id']) && $_GET['id'] > 0) {
    $qry = $conn->query("SELECT * FROM archive_list where id = '{$_GET['id']}'");
    if ($qry->num_rows) {
        foreach ($qry->fetch_array() as $k => $v) {
            if (!is_numeric($k))
                $$k = $v;
        }
    }
    if (isset($student_id)) {
        if ($student_id != $_settings->userdata('id')) {
            echo "<script> alert('You don\'t have an access to this page'); location.replace('./'); </script>";
        }
    }
}
?>
<style>
    .banner-img {
        object-fit: scale-down;
        object-position: center center;
        height: 30vh;
        width: calc(100%);
    }
</style>
<div class="content py-4">
    <div class="card card-outline card-primary shadow rounded-0">
        <div class="card-header rounded-0">
            <h5 class="card-title"><?= htmlspecialchars(isset($id) ? "Update Archive-{$archive_code} Details" :
                "Submit Project", ENT_QUOTES, 'UTF-8') ?></h5>
        </div>
        <div class="card-body rounded-0">
            <div class="container-fluid">
                <form action="" id="archive-form" enctype="multipart/form-data">
                    <input type="hidden" name="id"
                        value="<?= htmlspecialchars(isset($id) ? $id : "", ENT_QUOTES, 'UTF-8') ?>">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="title" class="control-label text-navy">Project Title</label>
                                <input type="text" name="title" id="title" autofocus placeholder="Project Title"
                                    class="form-control form-control-border" value="<?= htmlspecialchars(
                                        isset($title) ? $title : "",
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="year" class="control-label text-navy">Year</label>
                                <select name="year" id="year" class="form-control form-control-border" required>
                                    <?php
                                    for ($i = 0; $i < 51; $i++):
                                        ?>
                                        <option <?= htmlspecialchars(isset($year) && $year == date("Y", strtotime(date("Y") . " -{$i} years")) ?
                                            "selected" : "", ENT_QUOTES, 'UTF-8') ?>>
                                            <?= date("Y", strtotime(date("Y") . " -{$i} years")) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="abstract" class="control-label text-navy">Abstract</label>
                                <textarea rows="3" name="abstract" id="abstract" placeholder="abstract"
                                    class="form-control form-control-border summernote"
                                    required><?= isset($abstract) ? html_entity_decode($abstract) : "" ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="members" class="control-label text-navy">Project Members</label>
                                <textarea rows="3" name="members" id="members" placeholder="members"
                                    class="form-control form-control-border summernote-list-only"
                                    required><?= isset($members) ? html_entity_decode($members) : "" ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="img" class="control-label text-muted">Project Image/Banner Image</label>
                                <input type="file" id="img" name="img" class="form-control form-control-border"
                                    accept="image/png,image/jpeg,image/jpg" onchange="displayImg(this,$(this))"
                                    <?= htmlspecialchars(!isset($id) ? "required" : "", ENT_QUOTES, 'UTF-8') ?>>
                            </div>

                            <div class="form-group text-center">
                                <img src="<?= htmlspecialchars(validate_image(isset($banner_path) ? $banner_path : ""), ENT_QUOTES, 'UTF-8') ?>"
                                    alt="My Avatar" id="cimg" class="img-fluid banner-img bg-gradient-dark border">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="pdf" class="control-label text-muted">Project Document (PDF File
                                    Only)</label>
                                <input type="file" id="pdf" name="pdf" class="form-control form-control-border"
                                    accept=".pdf" <?= !isset($id) ? "required" : "" ?>>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="zipfiles" class="control-label text-muted">Create Zip of Multiples Uploded
                                    Files</label>
                                <input type="file" id="zipfiles" name="zipfiles[]"
                                    class="form-control form-control-border" multiple accept=".zip" <?= !isset($id) ? "required" : "" ?>>

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="sql" class="control-label text-muted">SQL File Only</label>
                                <input type="file" id="sql" name="sql" class="form-control form-control-border"
                                    accept=".sql" v<?= !isset($id) ? "required" : "" ?>>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group text-center">
                                <button class="btn btn-default btn-flat" style="background-color: #0062cc;">
                                    Submit</button>
                                <a href="./?page=profile" class="btn btn-light border btn-flat"> Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Upload Progress -->
<div id="uploadModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Progress</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="hideModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="progressDetails">Loaded: 0 MB / Total: 0 MB | Speed: 0 Mbps | Time Left: 0s</p>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
                        aria-valuemax="100"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="cancelUpload" class="btn btn-danger" onclick="cancelUpload()">Cancel</button>
                <button class="btn btn-secondary" onclick="hideModal()">Hide</button>
            </div>
        </div>
    </div>
</div>
<script>
    function displayImg(input, _this) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#cimg').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        } else {
            $('#cimg').attr('src', "<?= validate_image(isset($avatar) ? $avatar : "") ?>");
        }
    }
    $(function () {
        $('.summernote').summernote({
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ol', 'ul', 'paragraph', 'height']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['undo', 'redo', 'help']]
            ]
        })
        $('.summernote-list-only').summernote({
            height: 200,
            toolbar: [
                ['font', ['bold', 'italic', 'clear']],
                ['fontname', ['fontname']]
                ['color', ['color']],
                ['para', ['ol', 'ul']],
                ['view', ['undo', 'redo', 'help']]
            ]
        })

        // $('#archive-form').submit(function (e) {
        //     e.preventDefault();

        //     const formData = new FormData(this);

        //     $.ajax({
        //         url: _base_url_ + 'classes/Master.php?f=save_archive',
        //         method: 'POST',
        //         data: formData,
        //         contentType: false,
        //         processData: false,
        //         success: function (response) {
        //             const resp = JSON.parse(response);

        //             if (resp.status === 'success') {
        //                 Swal.fire({
        //                     title: 'Submission Successful!',
        //                     text: resp.msg,
        //                     icon: 'success',
        //                     confirmButtonText: 'OK'
        //                 }).then(() => {
        //                     window.location.href = './?page=studentprofile';
        //                 });
        //             } else {
        //                 Swal.fire({
        //                     title: 'Submission Failed',
        //                     text: resp.msg,
        //                     icon: 'error',
        //                     confirmButtonText: 'Try Again'
        //                 });
        //             }
        //         },
        //         error: function () {
        //             Swal.fire({
        //                 title: 'Error',
        //                 text: 'An unexpected error occurred while submitting the form.',
        //                 icon: 'error',
        //                 confirmButtonText: 'Close'
        //             });
        //         },
        //     });
        // });
    })
</script>
<!-- Modal for Upload Progress -->
<div id="uploadModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Progress</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="hideModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="progressDetails">Loaded: 0 MB / Total: 0 MB | Speed: 0 Mbps | Time Left: 0s</p>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
                        aria-valuemax="100"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="cancelUpload" class="btn btn-danger" onclick="cancelUpload()">Cancel</button>
                <button class="btn btn-secondary" onclick="hideModal()">Hide</button>
            </div>
        </div>
    </div>
</div>

<script>
    let uploadXHR = null;

    // Display Modal
    function showModal() {
        $('#uploadModal').modal('show');
    }

    // Hide Modal
    function hideModal() {
        $('#uploadModal').modal('hide');
    }

    // Cancel Upload
    function cancelUpload() {
        if (uploadXHR) {
            uploadXHR.abort();
            alert('Upload canceled!');
            $('.progress-bar').css('width', '0%').attr('aria-valuenow', 0);
            $('#progressDetails').text('Upload canceled.');
        }
    }

    $('#archive-form').submit(function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        showModal();

        // Start Ajax Upload
        uploadXHR = $.ajax({
            url: _base_url_ + 'classes/Master.php?f=save_archive',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            xhr: function () {
                const xhr = new XMLHttpRequest();

                // Progress Event
                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        const loadedMB = (e.loaded / (1024 * 1024)).toFixed(2);
                        const totalMB = (e.total / (1024 * 1024)).toFixed(2);
                        const timeElapsed = (new Date().getTime() - startTime) / 1000;
                        const bps = e.loaded / timeElapsed;
                        const Mbps = (bps / (1024 * 1024)).toFixed(2);
                        const timeRemaining = ((e.total - e.loaded) / bps).toFixed(2);

                        // Update Progress Bar and Details
                        $('.progress-bar').css('width', percentComplete + '%').attr('aria-valuenow', percentComplete);
                        $('#progressDetails').text(
                            `Loaded: ${loadedMB} MB / Total: ${totalMB} MB | Speed: ${Mbps} Mbps | Time Left: ${timeRemaining}s`
                        );
                    }
                });

                return xhr;
            },
            success: function (response) {
                const resp = JSON.parse(response);

                if (resp.status === 'success') {
                    alert('Upload completed successfully!');
                    window.location.href = './?page=studentprofile';
                } else {
                    alert('Upload failed: ' + resp.msg);
                }
            },
            error: function () {
                alert('An error occurred during the upload. Please try again.');
            },
        });
    });
</script>