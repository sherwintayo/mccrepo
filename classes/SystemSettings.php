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


	function update_settings_info()
	{
		$resp = ['success' => false, 'message' => ''];
		try {
			foreach ($_POST as $key => $value) {
				if ($key !== "content") {
					$value = str_replace("'", "&apos;", $value);
					$sql = "INSERT INTO system_info (meta_field, meta_value) VALUES ('$key', '$value') 
                        ON DUPLICATE KEY UPDATE meta_value='$value'";
					$this->conn->query($sql);
				}
			}

			if (isset($_POST['content'])) {
				foreach ($_POST['content'] as $k => $v) {
					file_put_contents("../$k.html", $v);
				}
			}

			$this->processFileUpload('img', 'logo');
			$this->processFileUpload('cover', 'cover');

			$this->update_system_info();
			$resp['success'] = true;
			$resp['message'] = 'System Info Successfully Updated.';
		} catch (Exception $e) {
			$resp['message'] = 'An error occurred: ' . $e->getMessage();
		}
		header('Content-Type: application/json');
		echo json_encode($resp);
		exit;
	}

	private function processFileUpload($fieldName, $metaField)
	{
		if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['tmp_name'] != '') {
			$fname = "uploads/{$metaField}-" . time() . '.png';
			$dir_path = base_app . $fname;
			$upload = $_FILES[$fieldName]['tmp_name'];
			$type = mime_content_type($upload);
			$allowed = ['image/png', 'image/jpeg'];
			if (!in_array($type, $allowed)) {
				throw new Exception("Invalid file type for {$fieldName}");
			}
			list($width, $height) = getimagesize($upload);
			$new_width = $metaField == 'cover' ? 1280 : 200;
			$new_height = $metaField == 'cover' ? 720 : 200;

			$t_image = imagecreatetruecolor($new_width, $new_height);
			$gdImg = ($type == 'image/png') ? imagecreatefrompng($upload) : imagecreatefromjpeg($upload);
			imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagepng($t_image, $dir_path);

			if (isset($_SESSION['system_info'][$metaField])) {
				$this->conn->query("UPDATE system_info set meta_value = '{$fname}' where meta_field = '{$metaField}' ");
				if (is_file(base_app . $_SESSION['system_info'][$metaField])) {
					unlink(base_app . $_SESSION['system_info'][$metaField]);
				}
			} else {
				$this->conn->query("INSERT into system_info set meta_value = '{$fname}',meta_field = '{$metaField}' ");
			}
		}
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
		if (isset($_SESSION['userdata'])) {
			unset($_SESSION['userdata']);
			return true;
		}
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
		echo $sysset->update_settings_info();
		break;
	default:
		// echo $sysset->index();
		break;
}
?>