<?php
require_once('../config.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $token_hash = hash('sha256', $token);
    
    // Validate token
    $stmt = $conn->prepare("SELECT id, reset_token_expires_at FROM users WHERE reset_token_hash = ?");
    $stmt->bind_param('s', $token_hash);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $expires_at);
        $stmt->fetch();
        
        // Check if token has expired
        if (strtotime($expires_at) > time()) {
            // Token is valid
            ?>
            <!DOCTYPE html>
            <html lang="en" class="" style="height: auto;">
            <!-- <?php require_once('inc/header.php') ?> -->
            <body class="hold-transition">
                <style>
                    html, body {
                        height: calc(100%) !important;
                        width: calc(100%) !important;
                    }
                    body {
                        background: linear-gradient(135deg, #141e30 20%, #243b55 100%);
                        background-size: cover;
                        background-repeat: no-repeat;
                        height: 100%;
                        width: 100%;
                    }
                    .login-title {
                        text-shadow: 3px 3px black;
                        padding: 20px 0 0 0;
                    }
                    .myColor {
                        background-image: linear-gradient(to right, #f83600 50%, #f9d423 150%);
                    }
                    .myLoginForm {
                        background: transparent;
                        border: 2px solid #f83600;
                        backdrop-filter: blur(2px);
                        border-radius: 20px 0 0 20px;
                    }
                    .btnLogin {
                        border-radius: 0 20px 20px 0;
                        border: 0;
                        background-image: linear-gradient(to right, #f83600 50%, #f9d423 150%);
                    }

                    @media (max-width: 575.98px) {
                        .myContainer {
                            margin: 20px;
                        }
                        .login-form {
                            width: 100%;
                        }
                        .myLoginForm {
                            border-radius: 20px 20px 0 0;
                        }
                    }
                </style>

                <div class="d-flex flex-column align-items-center w-100" id="reset-password">
                    <div class="body d-flex flex-column justify-content-center align-items-center">
                        <div class="w-100">
                            <h1 class="text-center py-5 my-5 login-title"><b>Reset Your Password</b></h1>
                        </div>
                        <div class="row myContainer">
                            <div class="myLoginForm col-lg-7 p-3 w-100 d-flex justify-content-center align-items-center text-navy">
                                <div class="d-flex flex-column w-100 px-3">
                                    <h1 class="text-center font-weight-bold text-white">Reset Password</h1>
                                    <hr class="my-3" />
                                    <form action="reset_password_process.php" method="post">
                                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="input-group form-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text rounded-0"><i class="fas fa-lock fa-lg fa-fw"></i></span>
                                                    </div>
                                                    <input type="password" name="password" id="password" placeholder="Enter new password" class="form-control form-control-border" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <a class="text-light font-weight-bolder" href="<?php echo base_url ?>admin/login.php">Go Back</a>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group text-right">
                                                    <button class="btnLogin btn btn-primary btn-flat text-white" type="submit">Reset Password</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-5 d-flex flex-column justify-content-center myColor p-4">
                                <h1 class="text-center font-weight-bold text-white">Password Assistance</h1>
                                <hr class="my-3 bg-light myHr" />
                                <p class="text-center font-weight-bolder text-light lead">Enter your new password and confirm to reset.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- jQuery -->
                <script src="plugins/jquery/jquery.min.js"></script>
                <!-- Bootstrap 4 -->
                <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
                <!-- AdminLTE App -->
                <script src="dist/js/adminlte.min.js"></script>
                <script>
                    $(document).ready(function(){
                        end_loader();
                    });
                </script>
            </body>
            </html>
            <?php
        } else {
            echo "Token has expired.";
        }
    } else {
        echo "Invalid token.";
    }
} else {
    echo "No token provided.";
}
?>
