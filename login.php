<?php 
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
                            <large><?= isset($abstract) ? html_entity_decode($abstract) : "" ?></large>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="text-navy">Members:</legend>
                        <div class="pl-4"><large><?= isset($members) ? html_entity_decode($members) : "" ?></large></div>
                    </fieldset>
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
                </div>
            </div>
        </div>
    </div>
</div>
<script>
  $(document).ready(function(){
    end_loader();
    // Registration Form Submit
    $('#slogin-form').submit(function(e){
        e.preventDefault()
        var _this = $(this)
            $(".pop-msg").remove()
            $('#password, #cpassword').removeClass("is-invalid")
        var el = $("<div>")
            el.addClass("alert pop-msg my-2")
            el.hide()
        start_loader();
        $.ajax({
            url:_base_url_+"classes/Login.php?f=student_login",
            method:'POST',
            data:_this.serialize(),
            dataType:'json',
            error:err=>{
                console.log(err)
                el.text("An error occurred while saving the data")
                el.addClass("alert-danger")
                _this.prepend(el)
                el.show('slow')
                end_loader();
            },
            success:function(resp){
                end_loader();
                if(resp.status == 'success'){
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Successful',
                        text: 'You will be redirected shortly.',
                        showConfirmButton: false,
                        timer: 800
                    }).then(() => {
                        let redirect = new URLSearchParams(window.location.search).get('redirect');
                        if(redirect == 'download') {
                            let file_type = new URLSearchParams(window.location.search).get('file_type');
                            let archive_id = new URLSearchParams(window.location.search).get('id');
                            let download_url = '';
                            if(file_type == 'zip') {
                                download_url = '<?= base_url ?>uploads/files/Files-' + archive_id + '.zip';
                            } else if(file_type == 'sql') {
                                download_url = '<?= base_url ?>uploads/sql/SQL-' + archive_id + '.zip';
                            } else if(file_type == 'pdf') {
                                download_url = '<?= base_url ?>uploads/pdf/Document-' + archive_id + '.zip';
                            }
                            if(download_url) {
                                window.location.href = download_url;
                            }
                        } else {
                            location.href= "./";
                        }
                    })
                }else if(!!resp.msg){
                    el.text(resp.msg)
                    el.addClass("alert-danger")
                    _this.prepend(el)
                    el.show('show')
                }else{
                    el.text("An error occurred while saving the data")
                    el.addClass("alert-danger")
                    _this.prepend(el)
                    el.show('show')
                }
                end_loader();
                $('html, body').animate({scrollTop: 0},'fast')
            }
        })
    })
  })
</script>


