<?php require_once('./config.php'); ?>
<!DOCTYPE html>
<html lang="en">
<!-- <?php require_once('inc/header.php') ?> -->
<body>
<div class="container">
    <h2>Forgot Password</h2>
    <form action="ms_login_process.php" method="post" onsubmit="return validateEmail()">
        <div class="form-group">
            <label for="email">Enter your MS account:</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>

<script>
function validateEmail() {
    const email = document.getElementById('email').value;
    const domain = "@mcclawis.edu.ph";
    if (!email.endsWith(domain)) {
        alert("Please enter a valid email address with the domain " + domain);
        return false;
    }
    return true;
}
</script>
</body>
</html>
