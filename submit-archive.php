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
    .progress {
        background-color: #f0f0f0;
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 15px;
    }

    .progress-bar {
        background-color: #007bff;
        height: 100%;
        width: 0%;
    }

    .upload-details p {
        margin: 5px 0;
    }
</style>
<style>
    .banner-img {
        object-fit: scale-down;
        object-position: center center;
        height: 30vh;
        width: calc(100%);
    }


    .card {
        width: 100%;
    }

    .card-header {
        display: flex;
        justify-content: center;
        background-color: #cfcfcf;
    }

    .card-title {
        color: #272424;
        font-weight: 700;
        font-size: 25px;
    }
</style>
<div class="content py-4">
    <div class="card card-outline shadow rounded-3">
        <div class="card-header rounded-3">
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
                                <label for="zipfiles" class="control-label text-muted">Source
                                    Code(Zip Files Only)</label>
                                <input type="file" id="zipfiles" name="zipfiles[]"
                                    class="form-control form-control-border" multiple accept=".zip" <?= !isset($id) ? "required" : "" ?>>

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="sql" class="control-label text-muted">Database(SQL File Only)</label>
                                <input type="file" id="sql" name="sql" class="form-control form-control-border"
                                    accept=".sql" v<?= !isset($id) ? "required" : "" ?>>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group text-center">
                                <button class="btn btn-default btn-flat"
                                    style="background-color: #0062cc; color:white;">
                                    Submit</button>
                                <a href="./?page=profile" class="btn btn-danger border btn-flat"> Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Modal for Upload Progress -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 8px; padding: 20px;">
            <h4 class="modal-title" id="uploadModalLabel">Uploading...</h4>
            <div class="progress"
                style="background-color: #f0f0f0; height: 8px; border-radius: 4px; overflow: hidden; margin-top: 15px;">
                <div class="progress-bar" style="background-color: #007bff; height: 100%; width: 0%;"></div>
            </div>
            <div class="upload-details mt-3">
                <p id="dataTransferred">Loaded/Total: 0 MB</p>
                <p id="Mbps">Speed: 0 Mbps</p>
                <p id="timeLeft">Time Left: --:--</p>
            </div>
            <div class="mt-3">
                <button id="hideBtn" class="btn btn-secondary">Hide</button>
                <button id="cancelBtn" class="btn btn-danger" disabled>Cancel</button>
                <p id="percent" class="mt-2">0%</p>
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
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ol', 'ul']],
                ['view', ['undo', 'redo', 'help']]
            ]
        })
    })
</script>
<script>
    $(document).ready(function () {
        $('#archive-form').submit(function (e) {
            e.preventDefault();
            $('#uploadModal').modal('show'); // Show Bootstrap modal

            const formData = new FormData(this); // Collect form data
            const startTime = new Date().getTime();
            let xhr;

            xhr = $.ajax({
                xhr: function () {
                    const customXHR = new XMLHttpRequest();
                    customXHR.upload.addEventListener("progress", function (e) {
                        if (e.lengthComputable) {
                            const percentComplete = ((e.loaded / e.total) * 100).toFixed(2);

                            // Convert bytes to MB
                            const mbTotal = (e.total / (1024 * 1024)).toFixed(2);
                            const mbLoaded = (e.loaded / (1024 * 1024)).toFixed(2);

                            // Calculate speed in Mbps
                            const elapsedTime = (new Date().getTime() - startTime) / 1000;
                            const bps = e.loaded / elapsedTime;
                            const Mbps = (bps / (1024 * 1024)).toFixed(2);

                            // Estimate remaining time
                            const remainingTime = ((e.total - e.loaded) / bps).toFixed(0);
                            const minutes = Math.floor(remainingTime / 60);
                            const seconds = remainingTime % 60;

                            // Update progress details
                            $('.progress-bar').css('width', percentComplete + '%');
                            $('#percent').text(`${percentComplete}%`);
                            $('#dataTransferred').text(`Loaded/Total: ${mbLoaded}/${mbTotal} MB`);
                            $('#Mbps').text(`Speed: ${Mbps} Mbps`);
                            $('#timeLeft').text(`Time Left: ${minutes}:${seconds}`);
                            $('#cancelBtn').prop('disabled', percentComplete === '100.00');
                        }
                    }, false);

                    return customXHR;
                },
                type: 'POST',
                url: _base_url_ + 'classes/Master.php?f=save_archive', // Point to your backend endpoint
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    const resp = JSON.parse(response);

                    if (resp.status === 'success') {
                        Swal.fire('Success', resp.msg, 'success').then(() => {
                            window.location.href = './?page=studentprofile'; // Redirect after completion
                        });
                    } else {
                        Swal.fire('Error', resp.msg, 'error');
                    }

                    $('#uploadModal').modal('hide'); // Hide Bootstrap modal
                },
                error: function () {
                    Swal.fire('Error', 'An unexpected error occurred.', 'error');
                    $('#uploadModal').modal('hide'); // Hide Bootstrap modal
                }
            });

            // Cancel upload
            $('#cancelBtn').on('click', function () {
                xhr.abort();
                Swal.fire('Cancelled', 'File upload cancelled.', 'info');
                $('#uploadModal').modal('hide'); // Hide Bootstrap modal
            });

            // Hide modal manually
            $('#hideBtn').on('click', function () {
                $('#uploadModal').modal('hide'); // Hide Bootstrap modal
            });
        });
    });
</script>
<!-- <script>
    $(document).ready(function () {
        let isScanning = false; // Prevent duplicate scans

        $('#archive-form').submit(function (e) {
            e.preventDefault(); // Prevent form submission

            const formData = new FormData(this); // Collect form data

            if (!isScanning) {
                isScanning = true;
                // Step 1: Scan files for malware
                $.ajax({
                    url: _base_url_ + 'scan-files.php', // Malware scanning endpoint
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Scanning Files',
                            text: 'Please wait while your files are being scanned for malware.',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => Swal.showLoading()
                        });
                    },
                    success: function (response) {
                        const resp = JSON.parse(response);

                        if (resp.status === 'clean') {
                            Swal.fire({
                                title: 'Files are Clean',
                                text: 'Your files passed the security scan. Click Upload to proceed.',
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'Upload',
                                cancelButtonText: 'Cancel'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Proceed with file upload
                                    uploadFiles(formData);
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Malicious Files Detected',
                                text: resp.msg,
                                icon: 'error'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'File scanning failed. Please try again.', 'error');
                    },
                    complete: function () {
                        isScanning = false; // Reset scanning state
                    }
                });
            }
        });

        function uploadFiles(formData) {
            const startTime = new Date().getTime();

            $.ajax({
                xhr: function () {
                    const customXHR = new XMLHttpRequest();
                    customXHR.upload.addEventListener("progress", function (e) {
                        if (e.lengthComputable) {
                            const percentComplete = ((e.loaded / e.total) * 100).toFixed(2);

                            // Update progress details
                            $('.progress-bar').css('width', percentComplete + '%');
                            $('#percent').text(`${percentComplete}%`);

                            // Additional metrics
                            const mbLoaded = (e.loaded / (1024 * 1024)).toFixed(2);
                            const mbTotal = (e.total / (1024 * 1024)).toFixed(2);
                            const elapsedTime = (new Date().getTime() - startTime) / 1000;
                            const bps = e.loaded / elapsedTime;
                            const Mbps = (bps / (1024 * 1024)).toFixed(2);
                            const remainingTime = ((e.total - e.loaded) / bps).toFixed(0);
                            const minutes = Math.floor(remainingTime / 60);
                            const seconds = remainingTime % 60;

                            // Update display
                            $('#dataTransferred').text(`Loaded/Total: ${mbLoaded}/${mbTotal} MB`);
                            $('#Mbps').text(`Speed: ${Mbps} Mbps`);
                            $('#timeLeft').text(`Time Left: ${minutes}:${seconds}`);
                        }
                    }, false);

                    return customXHR;
                },
                type: 'POST',
                url: _base_url_ + 'classes/Master.php?f=save_archive', // File upload endpoint
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#uploadModal').modal('show'); // Show progress bar
                },
                success: function (response) {
                    const resp = JSON.parse(response);
                    if (resp.status === 'success') {
                        Swal.fire('Success', resp.msg, 'success').then(() => {
                            window.location.href = './?page=studentprofile'; // Redirect
                        });
                    } else {
                        Swal.fire('Error', resp.msg, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'File upload failed.', 'error');
                },
                complete: function () {
                    $('#uploadModal').modal('hide'); // Hide progress bar
                }
            });
        }
    });
</script> -->