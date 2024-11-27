<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class Users extends DBConnection
{
	private $settings;
	public function __construct()
	{
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct()
	{
		parent::__destruct();
	}
	public function save_users()
	{
		if (!isset($_POST['status']) && $this->settings->userdata('login_type') == 1) {
			$_POST['status'] = 1;
			$_POST['type'] = 1;
		}
		extract($_POST);
		$oid = $id;
		$data = '';

		// Get the current type from the database if an ID is provided
		if (isset($id) && $id > 0) {
			$current_user = $this->conn->query("SELECT `type` FROM `users` WHERE `id` = '{$id}'");
			if ($current_user->num_rows > 0) {
				$current_type = $current_user->fetch_assoc()['type'];
			}
		}

		if (isset($oldpassword)) {
			if (md5($oldpassword) != $this->settings->userdata('password')) {
				return 4;
			}
		}
		$chk = $this->conn->query("SELECT * FROM `users` WHERE username ='{$username}' " . ($id > 0 ? " AND id!= '{$id}' " : ""))->num_rows;
		if ($chk > 0) {
			return 3;
			exit;
		}

		foreach ($_POST as $k => $v) {
			if (in_array($k, array('firstname', 'middlename', 'lastname', 'username', 'type'))) {
				// Only update the type if it was changed in the form
				if ($k == 'type' && isset($current_type) && $current_type == $v) {
					continue; // Skip updating the type if it hasn't changed
				}
				if (!empty($data))
					$data .= " , ";
				$data .= " {$k} = '{$v}' ";
			}
		}

		if (!empty($password)) {
			$password = md5($password);
			if (!empty($data))
				$data .= " , ";
			$data .= " `password` = '{$password}' ";
		}

		if (empty($id)) {
			$qry = $this->conn->query("INSERT INTO users SET {$data}");
			if ($qry) {
				$id = $this->conn->insert_id;
				$this->settings->set_flashdata('success', 'User Details successfully saved.');
				$resp['status'] = 1;
			} else {
				$resp['status'] = 2;
			}
		} else {
			$qry = $this->conn->query("UPDATE users SET $data WHERE id = {$id}");
			if ($qry) {
				$this->settings->set_flashdata('success', 'User Details successfully updated.');
				if ($id == $this->settings->userdata('id')) {
					foreach ($_POST as $k => $v) {
						if ($k != 'id') {
							if (!empty($data))
								$data .= " , ";
							$this->settings->set_userdata($k, $v);
						}
					}
				}
				$resp['status'] = 1;
			} else {
				$resp['status'] = 2;
			}
		}

		if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
			$fname = 'uploads/avatar-' . $id . '.png';
			$dir_path = base_app . $fname;
			$upload = $_FILES['img']['tmp_name'];
			$type = mime_content_type($upload);
			$allowed = array('image/png', 'image/jpeg');
			if (!in_array($type, $allowed)) {
				$resp['msg'] .= " But Image failed to upload due to invalid file type.";
			} else {
				$new_height = 200;
				$new_width = 200;

				list($width, $height) = getimagesize($upload);
				$t_image = imagecreatetruecolor($new_width, $new_height);
				imagealphablending($t_image, false);
				imagesavealpha($t_image, true);
				$gdImg = ($type == 'image/png') ? imagecreatefrompng($upload) : imagecreatefromjpeg($upload);
				imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				if ($gdImg) {
					if (is_file($dir_path))
						unlink($dir_path);
					$uploaded_img = imagepng($t_image, $dir_path);
					imagedestroy($gdImg);
					imagedestroy($t_image);
				} else {
					$resp['msg'] .= " But Image failed to upload due to unknown reason.";
				}
			}
			if (isset($uploaded_img)) {
				$this->conn->query("UPDATE users SET `avatar` = CONCAT('{$fname}','?v=',unix_timestamp(CURRENT_TIMESTAMP)) WHERE id = '{$id}' ");
				if ($id == $this->settings->userdata('id')) {
					$this->settings->set_userdata('avatar', $fname);
				}
			}
		}

		if (isset($resp['msg']))
			$this->settings->set_flashdata('success', $resp['msg']);
		return $resp['status'];
	}


	public function delete_users()
	{
		extract($_POST);
		$avatar = $this->conn->query("SELECT avatar FROM users where id = '{$id}'")->fetch_array()['avatar'];
		$qry = $this->conn->query("DELETE FROM users where id = $id");
		if ($qry) {
			$avatar = explode("?", $avatar)[0];
			$this->settings->set_flashdata('success', 'User Details successfully deleted.');
			if (is_file(base_app . $avatar))
				unlink(base_app . $avatar);
			$resp['status'] = 'success';
		} else {
			$resp['status'] = 'failed';
		}
		return json_encode($resp);
	}


	public function save_student()
	{
		extract($_POST);


		try {


			// Validate reCAPTCHA response
			$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
			$secretKey = '6LfFJYcqAAAAANKGBiV1AlFMLMwj2wgAGifniAKO'; // Replace with your reCAPTCHA secret key
			$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

			// Send request to Google's reCAPTCHA API
			$response = file_get_contents("$verifyUrl?secret=$secretKey&response=$recaptchaResponse");
			$responseKeys = json_decode($response, true);


			// Check reCAPTCHA validation
			if (!$responseKeys['success']) {
				return json_encode([
					'status' => 'failed',
					'msg' => 'reCAPTCHA validation failed. Please try again.',
					'debug' => $responseKeys // For debugging reCAPTCHA issues
				]);
			}

			$data = '';

			// Check if old password verification is needed
			if (isset($oldpassword)) {
				$stmt = $this->conn->prepare("SELECT password FROM student_list WHERE id = ?");
				$stmt->bind_param("i", $this->settings->userdata('id'));
				$stmt->execute();
				$stmt->bind_result($hashed_password);
				$stmt->fetch();
				$stmt->close();

				if (!password_verify($oldpassword, $hashed_password)) {
					return json_encode([
						'status' => 'failed',
						'msg' => 'Old Password is Incorrect'
					]);
				}
			}

			// Check for duplicate email
			$chk = $this->conn->query("SELECT * FROM student_list WHERE email = '{$email}' " . ($id > 0 ? "AND id != '{$id}'" : ""))->num_rows;
			if ($chk > 0) {
				return json_encode([
					'status' => 'failed',
					'msg' => 'Email already exists.'
				]);
			}

			// Prepare data for SQL query
			foreach ($_POST as $k => $v) {
				if (!in_array($k, ['id', 'oldpassword', 'cpassword', 'password', 'g-recaptcha-response'])) {
					$v = $this->conn->real_escape_string($v);
					if (!empty($data)) {
						$data .= ", ";
					}
					$data .= " {$k} = '{$v}' ";
				}
			}

			// Hash the password if provided
			if (!empty($password)) {
				$password_hash = password_hash($password, PASSWORD_BCRYPT);
				if (!$password_hash) {
					return json_encode(['status' => 'failed', 'msg' => 'Password hashing failed.']);
				}
				if (!empty($data)) {
					$data .= ", ";
				}
				$data .= " password = '{$password_hash}' ";
			}

			// Insert or update the student record
			if (empty($id)) {
				$qry = $this->conn->query("INSERT INTO student_list SET {$data}");
				if ($qry) {
					$id = $this->conn->insert_id;
					$this->settings->set_flashdata('success', 'Student User Details successfully saved.');
					return json_encode(['status' => 'success']);
				} else {
					return json_encode([
						'status' => 'failed',
						'msg' => 'Database error: ' . $this->conn->error // Provide detailed error
					]);
				}
			} else {
				$qry = $this->conn->query("UPDATE student_list SET {$data} WHERE id = {$id}");
				if ($qry) {
					$this->settings->set_flashdata('success', 'Student User Details successfully updated.');
					return json_encode(['status' => 'success']);
				} else {
					return json_encode([
						'status' => 'failed',
						'msg' => 'Database error: ' . $this->conn->error // Provide detailed error
					]);
				}
			}
			// Image upload logic
			if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
				$fname = 'uploads/student-' . $id . '.png';
				$dir_path = base_app . $fname;
				$upload = $_FILES['img']['tmp_name'];
				$type = mime_content_type($upload);
				$allowed = ['image/png', 'image/jpeg'];

				if (!in_array($type, $allowed)) {
					$resp['msg'] .= " But Image failed to upload due to invalid file type.";
				} else {
					$new_height = 200;
					$new_width = 200;
					list($width, $height) = getimagesize($upload);

					$t_image = imagecreatetruecolor($new_width, $new_height);
					imagealphablending($t_image, false);
					imagesavealpha($t_image, true);
					$gdImg = ($type == 'image/png') ? imagecreatefrompng($upload) : imagecreatefromjpeg($upload);
					imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

					if ($gdImg) {
						if (is_file($dir_path)) {
							unlink($dir_path);
						}
						$uploaded_img = imagepng($t_image, $dir_path);
						imagedestroy($gdImg);
						imagedestroy($t_image);
					} else {
						$resp['msg'] .= " But Image failed to upload due to an unknown reason.";
					}
				}

				if (isset($uploaded_img)) {
					$this->conn->query("UPDATE student_list SET avatar = CONCAT('{$fname}','?v=',unix_timestamp(CURRENT_TIMESTAMP)) WHERE id = '{$id}'");
					if ($id == $this->settings->userdata('id')) {
						$this->settings->set_userdata('avatar', $fname);
					}
				}
			}
		} catch (Exception $e) {
			// Catch any unexpected errors and return JSON
			return json_encode([
				'status' => 'failed',
				'msg' => 'An unexpected error occurred.',
				'debug' => $e->getMessage() // Include exception message for debugging
			]);
		}
	}

	public function update_student()
	{
		extract($_POST);

		// Response array to build the result
		$response = ["status" => "failed", "msg" => "An unknown error occurred."];

		// Step 1: Verify old password if required
		if (isset($oldpassword)) {
			$stmt = $this->conn->prepare("SELECT password FROM student_list WHERE id = ?");
			$stmt->bind_param("i", $this->settings->userdata('id'));
			$stmt->execute();
			$stmt->bind_result($hashed_password);
			$stmt->fetch();
			$stmt->close();

			if (!$hashed_password) {
				$response["msg"] = "Unable to verify the old password. Please try again.";
				echo json_encode($response);
				exit;
			}

			if (!password_verify($oldpassword, $hashed_password)) {
				$response["msg"] = "Old Password is Incorrect.";
				echo json_encode($response);
				exit;
			}
		}

		// Step 2: Check for duplicate email
		$chk = $this->conn->query("SELECT * FROM student_list WHERE email = '{$email}' " . ($id > 0 ? "AND id != '{$id}'" : ""))->num_rows;
		if ($chk > 0) {
			$response["msg"] = "Email already exists. Please use a different email address.";
			echo json_encode($response);
			exit;
		}

		// Step 3: Prepare data for SQL update query
		$data = '';
		foreach ($_POST as $k => $v) {
			if (!in_array($k, ['id', 'oldpassword', 'cpassword', 'password'])) {
				$v = $this->conn->real_escape_string($v);
				if (!empty($data)) {
					$data .= ", ";
				}
				$data .= " {$k} = '{$v}' ";
			}
		}

		// Step 4: Hash the password if provided
		if (!empty($password)) {
			$password_hash = password_hash($password, PASSWORD_BCRYPT);
			if (!$password_hash) {
				$response["msg"] = "Password hashing failed. Please try again.";
				echo json_encode($response);
				exit;
			}
			if (!empty($data)) {
				$data .= ", ";
			}
			$data .= " password = '{$password_hash}' ";
		}

		// Step 5: Execute the update query
		$qry = $this->conn->query("UPDATE student_list SET {$data} WHERE id = {$id}");
		if (!$qry) {
			$response["msg"] = "Failed to update the student details. Database error: " . $this->conn->error;
			echo json_encode($response);
			exit;
		}

		// Step 6: Update session data if the user updates their own profile
		if ($id == $this->settings->userdata('id')) {
			foreach ($_POST as $k => $v) {
				if ($k != 'id' && $k != 'password') {
					$this->settings->set_userdata($k, $v);
				}
			}
		}

		// Step 7: Handle image upload
		if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
			$fname = 'uploads/student-' . $id . '.png';
			$dir_path = base_app . $fname;
			$upload = $_FILES['img']['tmp_name'];
			$type = mime_content_type($upload);
			$allowed = ['image/png', 'image/jpeg'];

			if (!in_array($type, $allowed)) {
				$response["msg"] = "Invalid image type. Please upload a PNG or JPEG file.";
				echo json_encode($response);
				exit;
			}

			$new_height = 200;
			$new_width = 200;
			list($width, $height) = getimagesize($upload);

			$t_image = imagecreatetruecolor($new_width, $new_height);
			imagealphablending($t_image, false);
			imagesavealpha($t_image, true);
			$gdImg = ($type == 'image/png') ? imagecreatefrompng($upload) : imagecreatefromjpeg($upload);
			imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

			if ($gdImg) {
				if (is_file($dir_path)) {
					unlink($dir_path);
				}
				imagepng($t_image, $dir_path);
				imagedestroy($gdImg);
				imagedestroy($t_image);
			} else {
				$response["msg"] = "Image upload failed due to an unknown reason.";
				echo json_encode($response);
				exit;
			}

			// Update avatar in the database
			$this->conn->query("UPDATE student_list SET avatar = CONCAT('{$fname}','?v=',unix_timestamp(CURRENT_TIMESTAMP)) WHERE id = '{$id}'");
			if ($id == $this->settings->userdata('id')) {
				$this->settings->set_userdata('avatar', $fname);
			}
		}

		// Final Success Response
		$response["status"] = "success";
		$response["msg"] = "Student details updated successfully.";
		echo json_encode($response);
	}



	public function delete_student()
	{
		extract($_POST);
		$avatar = $this->conn->query("SELECT avatar FROM student_list where id = '{$id}'")->fetch_array()['avatar'];
		$qry = $this->conn->query("DELETE FROM student_list where id = $id");
		if ($qry) {
			$avatar = explode("?", $avatar)[0];
			$this->settings->set_flashdata('success', 'Student User Details successfully deleted.');
			if (is_file(base_app . $avatar))
				unlink(base_app . $avatar);
			$resp['status'] = 'success';
		} else {
			$resp['status'] = 'failed';
		}
		return json_encode($resp);
	}
	public function verify_student()
	{
		extract($_POST);
		$update = $this->conn->query("UPDATE `student_list` set `status` = 1 where id = $id");
		if ($update) {
			$this->settings->set_flashdata('success', 'Student Account has verified successfully.');
			$resp['status'] = 'success';
		} else {
			$resp['status'] = 'failed';
		}
		return json_encode($resp);
	}

}

$users = new users();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
switch ($action) {
	case 'save':
		echo $users->save_users();
		break;
	case 'delete':
		echo $users->delete_users();
		break;
	case 'save_student':
		echo $users->save_student();
		break;
	case 'update_student':
		echo $users->update_student();
		break;
	case 'delete_student':
		echo $users->delete_student();
		break;
	case 'verify_student':
		echo $users->verify_student();
		break;
	default:
		// echo $sysset->index();
		break;
}