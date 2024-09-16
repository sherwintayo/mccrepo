<?php require_once('./config.php') ?>
<?php session_start(); // Start session handling ?>
<!DOCTYPE html>
<html lang="en" class="" style="height: auto;">
 <?php require_once('inc/header.php') ?>
<body class="hold-transition ">
  <script>
    start_loader()
  </script>

  <!-- Your login page styles here -->
  
  <div class=" d-flex flex-column align-items-center w-100" id="login">
    <div class="body d-flex flex-column justify-content-center align-items-center">
     <div class="w-100">
        <h1 class="text-center py-5 my-5 login-title"><b><?php echo $_settings->info('name') ?></b></h1>
      </div> 
        <div class="row myContainer">
            <div class="myLoginForm col-lg-7 p-3 w-100 d-flex justify-content-center align-items-center text-navy">
                <div class="d-flex flex-column w-100 px-3">
                    <h1 class="text-center font-weight-bold text-white">Sign in to Account</h1>
                    <hr class="my-3" />
                    <form action="" id="slogin-form">
                        <div class="input-group form-group">
                            <input type="email" name="email" id="email" placeholder="Email" class="form-control" required>
                        </div>
                        <div class="input-group form-group">
                            <input type="password" name="password" id="password" placeholder="Password" class="form-control" required>
                        </div>
                        <div class="form-group text-right">
                            <button class="btnLogin btn btn-primary btn-flat text-white">Login</button>
                        </div>
                    </form>
                 </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
  $(document).ready(function(){
    end_loader();
    
    $('#slogin-form').submit(function(e){
        e.preventDefault();
        var _this = $(this);
        var el = $("<div>");
        el.addClass("alert pop-msg my-2").hide();
        start_loader();

        $.ajax({
            url: _base_url_ + "classes/Login.php?f=student_login",
            method: 'POST',
            data: _this.serialize(),
            dataType: 'json',
            error: err => {
                console.log(err);
                el.text("An error occurred").addClass("alert-danger");
                _this.prepend(el);
                el.show('slow');
                end_loader();
            },
            success: function(resp){
                end_loader();
                if (resp.status == 'success') {
                    // Set session privileges for download after login
                    <?php $_SESSION['user_logged_in'] = true; ?>
                    <?php $_SESSION['can_download'] = true; ?>

                    // Redirect to the download URL if provided
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
                        location.href = "./";
                    }
                } else {
                    el.text(resp.msg || "Login failed").addClass("alert-danger");
                    _this.prepend(el);
                    el.show('show');
                }
            }
        });
    });
  });
</script>
</body>
</html>
.