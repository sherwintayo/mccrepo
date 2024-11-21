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

            <head>
                <title>Reset Password</title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
                <link rel="stylesheet" href="../dist/css/loginstyle.css">
            </head>
            <style>
                html,
                body {
                    height: calc(100%) !important;
                    width: calc(100%) !important;
                }

                body {
                    background-image: url("../dist/img/background.png");
                    background-size: cover;
                    background-repeat: no-repeat;
                }
            </style>

            <body class="hold-transition">
                <div class="container register" style="margin-top: 14vh;">
                    <div class="row">
                        <!-- Left Section -->
                        <div class="col-md-3 register-left">
                            <img src="<?= validate_image($_settings->info('logo')) ?>" alt="Logo" />
                            <h3>Password Reset</h3>
                            <p>Enter your new password to reset your account credentials.</p>
                            <button class="myButton" onclick="location.href = '<?php echo base_url ?>admin/login.php'">Go
                                Back</button>
                        </div>

                        <!-- Right Section -->
                        <div class="col-md-9 register-right">
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="reset-password-tab" role="tabpanel">
                                    <h3 class="register-heading">Reset Your Password</h3>
                                    <div class="row register-form">
                                        <div class="col-md-12">
                                            <form action="reset_password_process.php" method="POST">
                                                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                                                <!-- Password Field -->
                                                <div class="form-group">
                                                    <input type="password" name="password" id="password"
                                                        placeholder="Enter new password" class="form-control form-control-border"
                                                        required>
                                                </div>

                                                <!-- Buttons -->
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <a href="<?php echo base_url ?>admin/login.php" class="btn btn-light">Go
                                                            Back</a>
                                                    </div>
                                                    <div class="col-md-6 text-right">
                                                        <button type="submit" class="btn btn-primary btn-flat">Reset
                                                            Password</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scripts -->
                <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
                <script src="../plugins/jquery/jquery.min.js"></script>
                <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
                <script src="../dist/js/adminlte.min.js"></script>
                <script>
                    $(document).ready(function () {
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