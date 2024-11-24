<?php
if (!class_exists('DBConnection')) {
	require_once('../config.php');
	require_once('DBConnection.php');
}
class SystemSettings extends DBConnection
{
	public function __construct()
	{
		parent::__construct();
	}
	function check_connection()
	{
		return ($this->conn);
	}
	function load_system_info()
	{
		// if(!isset($_SESSION['system_info'])){
		$sql = "SELECT * FROM system_info";
		$qry = $this->conn->query($sql);
		while ($row = $qry->fetch_assoc()) {
			$_SESSION['system_info'][$row['meta_field']] = $row['meta_value'];
		}
		// }
	}
	function update_system_info()
	{
		$sql = "SELECT * FROM system_info";
		$qry = $this->conn->query($sql);
		while ($row = $qry->fetch_assoc()) {
			if (isset($_SESSION['system_info'][$row['meta_field']]))
				unset($_SESSION['system_info'][$row['meta_field']]);
			$_SESSION['system_info'][$row['meta_field']] = $row['meta_value'];
		}
		return true;
	}
	function update_settings()
	{
		$resp = ['status' => 'success', 'msg' => '']; // Default response

		// Update database for text fields
		foreach ($_POST as $key => $value) {
			if (!in_array($key, array("content"))) {
				$value = $this->conn->real_escape_string(str_replace("'", "&apos;", $value));
				$sql = isset($_SESSION['system_info'][$key])
					? "UPDATE system_info SET meta_value = '{$value}' WHERE meta_field = '{$key}'"
					: "INSERT INTO system_info (meta_value, meta_field) VALUES ('{$value}', '{$key}')";
				$qry = $this->conn->query($sql);
				if (!$qry) {
					$resp['status'] = 'error';
					$resp['msg'] .= "Failed to update {$key}. ";
					error_log("DB Error [{$key}]: " . $this->conn->error);
				}
			}
		}

		// Save content fields to files
		if (isset($_POST['content'])) {
			foreach ($_POST['content'] as $k => $v) {
				$file_path = "../{$k}.html";
				if (!file_put_contents($file_path, $v)) {
					$resp['status'] = 'error';
					$resp['msg'] .= "Failed to save {$k}.html. ";
					error_log("File write error: {$file_path}");
				}
			}
		}

		// Handle logo upload
		if (isset($_FILES['img']) && $_FILES['img']['tmp_name'] != '') {
			$upload_result = $this->handle_file_upload($_FILES['img'], 'logo', 200, 200, 'uploads/logo-');
			if ($upload_result['status'] !== 'success') {
				$resp['status'] = 'error';
				$resp['msg'] .= $upload_result['msg'];
			}
		}

		// Handle cover upload
		if (isset($_FILES['cover']) && $_FILES['cover']['tmp_name'] != '') {
			$upload_result = $this->handle_file_upload($_FILES['cover'], 'cover', 1280, 720, 'uploads/cover-');
			if ($upload_result['status'] !== 'success') {
				$resp['status'] = 'error';
				$resp['msg'] .= $upload_result['msg'];
			}
		}

		// Update system info in session
		if ($this->update_system_info() && $resp['status'] === 'success') {
			$this->set_flashdata('success', 'System Info Successfully Updated.');
		} else {
			$resp['status'] = 'error';
			$resp['msg'] .= " Failed to refresh system info.";
		}

		return json_encode($resp);
	}

	/**
	 * Handle file upload and resizing.
	 */
	private function handle_file_upload($file, $field, $new_width, $new_height, $prefix)
	{
		$resp = ['status' => 'success', 'msg' => ''];
		$fname = $prefix . time() . '.png';
		$dir_path = base_app . $fname;
		$upload = $file['tmp_name'];
		$type = mime_content_type($upload);
		$allowed = ['image/png', 'image/jpeg'];

		if (!in_array($type, $allowed)) {
			$resp['status'] = 'error';
			$resp['msg'] = " Invalid file type for {$field}.";
			return $resp;
		}

		list($width, $height) = getimagesize($upload);
		$t_image = imagecreatetruecolor($new_width, $new_height);
		imagealphablending($t_image, false);
		imagesavealpha($t_image, true);
		$gdImg = ($type === 'image/png') ? imagecreatefrompng($upload) : imagecreatefromjpeg($upload);
		imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

		if ($gdImg) {
			if (is_file($dir_path))
				unlink($dir_path);
			if (!imagepng($t_image, $dir_path)) {
				$resp['status'] = 'error';
				$resp['msg'] = " Failed to save resized image for {$field}.";
			} else {
				$sql = isset($_SESSION['system_info'][$field])
					? "UPDATE system_info SET meta_value = '{$fname}' WHERE meta_field = '{$field}'"
					: "INSERT INTO system_info (meta_value, meta_field) VALUES ('{$fname}', '{$field}')";
				$qry = $this->conn->query($sql);
				if (!$qry) {
					$resp['status'] = 'error';
					$resp['msg'] = " Failed to save file path for {$field} to the database.";
					error_log("DB Error [{$field}]: " . $this->conn->error);
				}
			}
			imagedestroy($gdImg);
			imagedestroy($t_image);
		} else {
			$resp['status'] = 'error';
			$resp['msg'] = " Failed to process image for {$field}.";
		}

		return $resp;
	}

	function set_userdata($field = '', $value = '')
	{
		if (!empty($field) && !empty($value)) {
			$_SESSION['userdata'][$field] = $value;
		}
	}
	function userdata($field = '')
	{
		if (!empty($field)) {
			if (isset($_SESSION['userdata'][$field]))
				return $_SESSION['userdata'][$field];
			else
				return null;
		} else {
			return false;
		}
	}
	function set_flashdata($flash = '', $value = '')
	{
		if (!empty($flash) && !empty($value)) {
			$_SESSION['flashdata'][$flash] = $value;
			return true;
		}
	}
	function chk_flashdata($flash = '')
	{
		if (isset($_SESSION['flashdata'][$flash])) {
			return true;
		} else {
			return false;
		}
	}
	function flashdata($flash = '')
	{
		if (!empty($flash)) {
			$_tmp = $_SESSION['flashdata'][$flash];
			unset($_SESSION['flashdata']);
			return $_tmp;
		} else {
			return false;
		}
	}
	function sess_des()
	{
		// Ensure session is started
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}

		// Clear user-specific session data
		if (isset($_SESSION['userdata'])) {
			unset($_SESSION['userdata']);
		}

		// Clear login status
		if (isset($_SESSION['user_logged_in'])) {
			unset($_SESSION['user_logged_in']);
		}

		// Clear all session data if necessary
		session_unset();
		session_destroy();

		return true;
	}

	function info($field = '')
	{
		if (!empty($field)) {
			if (isset($_SESSION['system_info'][$field]))
				return $_SESSION['system_info'][$field];
			else
				return false;
		} else {
			return false;
		}
	}
	function set_info($field = '', $value = '')
	{
		if (!empty($field) && !empty($value)) {
			$_SESSION['system_info'][$field] = $value;
		}
	}
}
$_settings = new SystemSettings();
$_settings->load_system_info();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
	case 'update_settings':
		header('Content-Type: application/json'); // Return JSON response
		$result = $sysset->update_settings();
		if ($result) {
			echo json_encode(['status' => 'success']);
		} else {
			echo json_encode(['status' => 'error', 'msg' => 'Failed to update settings.']);
		}
		break;

	default:
		// echo $sysset->index();
		break;
}
?>