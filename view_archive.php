<?php
session_start();

// After successful login, set user_id in session
$_SESSION['user_id'] = $user_id; // Assume $user_id is obtained from the database
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

    // Record count execution
    $stmt = $conn->prepare("INSERT INTO archive_counter (archive_id) VALUES (?)");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
}
?>
<style>
    /* Styling for centered modal and main download button */
    #document_field {
        min-height: 80vh;
    }

    .download-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .download-button {
        display: flex;
        gap: 10px;
        margin-top: 10px;
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

                    <!-- Unified Download Section -->
                    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == true): ?>
                        <hr>
                        <div class="download-section">
                            <h4 class="text-navy">Project Files</h4>
                            <div class="download-button">
                                <?php if ($folder_path): ?>
                                    <a href="download.php?file=<?= htmlspecialchars($folder_path) ?>"
                                        class="btn btn-flat btn-success">Download Project Files</a>
                                <?php endif; ?>
                                <?php if ($sql_path): ?>
                                    <a href="download.php?file=<?= htmlspecialchars($sql_path) ?>"
                                        class="btn btn-flat btn-success">Download SQL File</a>
                                <?php endif; ?>
                                <?php if ($document_path): ?>
                                    <a href="download.php?file=<?= htmlspecialchars($document_path) ?>"
                                        class="btn btn-flat btn-success">Download Document</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Prompt login if not logged in -->
                        <hr>
                        <div class="download-section">
                            <h4 class="text-navy">Project Files</h4>
                            <a href="login.php?redirect=archive&id=<?= htmlspecialchars($id) ?>"
                                class="btn btn-success">Login to Download All Files</a>
                        </div>
                    <?php endif; ?>

                    <!-- Archive information fields -->
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

                    <!-- Banner Image -->
                    <center>
                        <img src="<?= validate_image(isset($banner_path) ? htmlspecialchars($banner_path) : "") ?>"
                            alt="Banner Image" id="banner-img" class="img-fluid border bg-gradient-dark">
                    </center>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Any JS for the new download format could go here if necessary
</script>