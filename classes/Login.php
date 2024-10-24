<?php
require_once '../config.php';

class Login extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;

		parent::__construct();
		ini_set('display_error', 1);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public function index(){
		echo "<h1>Access Denied</h1> <a href='".base_url."'>Go Back.</a>";
	}
	public function login(){
        extract($_POST);
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $qry = $stmt->get_result();

        if($qry->num_rows > 0){
            $res = $qry->fetch_assoc();
            
            // Check if the password is hashed with bcrypt (password_hash)
            if (password_verify($password, $res['password'])) {
                // Check account status
                if ($res['status'] != 1) {
                    return json_encode(['status' => 'notverified']);
                }

                // Store session data
                foreach($res as $k => $v){
                    if(!is_numeric($k) && $k != 'password'){
                        $this->settings->set_userdata($k, $v);
                    }
                }
                $this->settings->set_userdata('login_type', 1);

                // Optional: Rehash the password if the current hash is not up to date
                if (password_needs_rehash($res['password'], PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt->bind_param('si', $newHash, $res['id']);
                    $updateStmt->execute();
                }

                return json_encode(['status' => 'success']);
            } 
            // If the password is still hashed with MD5, verify it
            elseif ($res['password'] === md5($password)) {
                // Migrate MD5 hash to bcrypt
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->bind_param('si', $newHash, $res['id']);
                $updateStmt->execute();

                // Store session data
                foreach($res as $k => $v){
                    if(!is_numeric($k) && $k != 'password'){
                        $this->settings->set_userdata($k, $v);
                    }
                }
                $this->settings->set_userdata('login_type', 1);

                return json_encode(['status' => 'success']);
            } else {
                // Incorrect password
                return json_encode(['status' => 'incorrect']);
            }
        } else {
            // No user found with the given username
            return json_encode(['status' => 'incorrect']);
        }
    }


	public function logout(){
		if($this->settings->sess_des()){
			redirect('admin/login.php');
		}
	}
    public function student_login(){
        extract($_POST);
        $qry = $this->conn->query("SELECT *,concat(lastname,', ',firstname,' ',middlename) as fullname from student_list where email = '$email' and password = md5('$password') ");
        if($this->conn->error){
            $resp['status'] = 'failed';
            $resp['msg'] = "An error occurred while fetching data. Error:". $this->conn->error;
        }else{
            if($qry->num_rows > 0){
                $res = $qry->fetch_array();
                if($res['status'] == 1){
                    foreach($res as $k => $v){
                        $this->settings->set_userdata($k,$v);
                    }
                    $this->settings->set_userdata('login_type',2);
                    $resp['status'] = 'success';
                }else{
                    $resp['status'] = 'failed';
                    $resp['msg'] = "Your Account is not verified yet.";
                }
                
            }else{
                $resp['status'] = 'failed';
                $resp['msg'] = "Invalid email or password.";
            }
        }
        return json_encode($resp);
        
    }
    
    
	public function student_logout(){
		if($this->settings->sess_des()){
			redirect('./');
		}
	}


	public function ms_Login(){
        extract($_POST);
        $domain = "@mcclawis.edu.ph";
        
        if (!str_ends_with($email, $domain)) {
            return json_encode(['status' => 'error', 'msg' => "Invalid email address. Only emails ending with $domain are allowed."]);
        }

        // Check if email exists in the database
        $stmt = $this->conn->prepare("SELECT id, username FROM msaccount WHERE username = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            // Generate reset token and expiration
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $token_hash = hash('sha256', $token);
            
            // Store token and expiration in the database
            $stmt->bind_result($user_id, $username);
            $stmt->fetch();
            $update = $this->conn->prepare("UPDATE msaccount SET reset_token_hash = ?, reset_token_hash_expires_at = ? WHERE id = ?");
            $update->bind_param('ssi', $token_hash, $expires_at, $user_id);
            $update->execute();
            
            // Send the reset link via PHPMailer
            $reset_link = base_url . "register.php?token=$token";
            require '../vendor/autoload.php';
            
            $mail = new PHPMailer(true);
            
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = getenv('EMAIL_USERNAME');
                $mail->Password = getenv('EMAIL_PASSWORD');
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('sherwintayo08@gmail.com', 'MCC Repositories');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body    = "Hi $username,<br><br>Click the link below to register:<br><a href='$reset_link'>$reset_link</a><br><br>The link is valid for 1 hour.";

                $mail->send();
                return json_encode(['status' => 'success']);
            } catch (Exception $e) {
                return json_encode(['status' => 'error', 'msg' => "Mailer Error: {$mail->ErrorInfo}"]);
            }
        } else {
            return json_encode(['status' => 'error', 'msg' => 'Email not found.']);
        }
    }



	
}
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$auth = new Login();
switch ($action) {
	case 'login':
		echo $auth->login();
		break;
	case 'logout':
		echo $auth->logout();
		break;
	case 'student_login':
		echo $auth->student_login();
		break;
	case 'student_logout':
		echo $auth->student_logout();
		break;
	default:
		echo $auth->index();
		break;
}