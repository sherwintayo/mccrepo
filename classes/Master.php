<?php
require_once('../config.php');


class Master extends DBConnection
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
	function capture_err()
	{
		if (!$this->conn->error)
			return false;
		else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
			return json_encode($resp);
			exit;
		}
	}
	function save_program()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id'))) {
				if (!is_numeric($v))
					$v = $this->conn->real_escape_string($v);
				if (!empty($data))
					$data .= ",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if (empty($id)) {
			$sql = "INSERT INTO `program_list` SET {$data} ";
		} else {
			$sql = "UPDATE `program_list` SET {$data} WHERE id = '{$id}' ";
		}
		$check = $this->conn->query("SELECT * FROM `program_list` WHERE `name`='{$name}' " . ($id > 0 ? " AND id != '{$id}'" : ""))->num_rows;
		if ($check > 0) {
			$resp['status'] = 'failed';
			$resp['msg'] = "Program Name Already Exists.";
		} else {
			$save = $this->conn->query($sql);
			if ($save) {
				$rid = !empty($id) ? $id : $this->conn->insert_id;
				$resp['status'] = 'success';
				if (empty($id))
					$resp['msg'] = "Program details successfully added.";
				else
					$resp['msg'] = "Program details has been updated successfully.";

				// Log activity
				$this->ProgramlogActivity('Program saved or updated: ' . $name);
			} else {
				$resp['status'] = 'failed';
				$resp['msg'] = "An error occurred.";
				$resp['err'] = $this->conn->error . "[{$sql}]";
			}
		}
		if ($resp['status'] == 'success')
			$this->settings->set_flashdata('success', $resp['msg']);
		return json_encode($resp);
	}


	function delete_program()
	{
		extract($_POST);

		// Fetch program details for logging
		$get_program = $this->conn->query("SELECT * FROM `program_list` WHERE id = '{$id}'");
		if ($get_program->num_rows > 0) {
			$program = $get_program->fetch_assoc();
			$program_name = $program['name']; // Assuming 'name' is the field you want to log
		} else {
			// Handle case where program with $id doesn't exist
			$resp['status'] = 'failed';
			$resp['error'] = 'Program not found';
			return json_encode($resp);
		}

		// Perform deletion
		$del = $this->conn->query("DELETE FROM `program_list` WHERE id = '{$id}'");
		if ($del) {
			$resp['status'] = 'success';
			$resp['msg'] = "Program '{$program_name}' has been deleted successfully.";

			// Log activity
			$this->ProgramlogActivity("Deleted program '{$program_name}'");

			$this->settings->set_flashdata('success', "Program has been deleted successfully.");
		} else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}

	private function ProgramlogActivity($action)
	{
		$username = $this->settings->userdata('username');
		$date = date('Y-m-d H:i:s');
		$action = $this->conn->real_escape_string($action);
		$sql = "INSERT INTO `activity_log` (`username`, `date`, `action`) VALUES ('$username', '$date', '$action')";
		$this->conn->query($sql); // Assuming $this->conn is your database connection
	}


	function save_curriculum()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, array('id'))) {
				if (!is_numeric($v))
					$v = $this->conn->real_escape_string($v);
				if (!empty($data))
					$data .= ",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		if (empty($id)) {
			$sql = "INSERT INTO `curriculum_list` set {$data} ";
		} else {
			$sql = "UPDATE `curriculum_list` set {$data} where id = '{$id}' ";
		}
		$check = $this->conn->query("SELECT * FROM `curriculum_list` where `name`='{$name}' and `program_id` = '{program_id}' 
		" . ($id > 0 ? " and id != '{$id}'" : ""))->num_rows;
		if ($check > 0) {
			$resp['status'] = 'failed';
			$resp['msg'] = "Curriculum Name Already Exists.";
		} else {
			$save = $this->conn->query($sql);
			if ($save) {
				$rid = !empty($id) ? $id : $this->conn->insert_id;
				$resp['status'] = 'success';
				if (empty($id))
					$resp['msg'] = "Curriculum details successfully added.";
				else
					$resp['msg'] = "Curriculum details has been updated successfully.";
				// Log activity
				$this->CurriculumlogActivity('Curriculum added or updated: ' . $name);
			} else {
				$resp['status'] = 'failed';
				$resp['msg'] = "An error occured.";
				$resp['err'] = $this->conn->error . "[{$sql}]";
			}
		}
		if ($resp['status'] == 'success')
			$this->settings->set_flashdata('success', $resp['msg']);
		return json_encode($resp);
	}

	function delete_curriculum()
	{
		extract($_POST);

		// Fetch curriculum details for logging
		$get_curriculum = $this->conn->query("SELECT * FROM `curriculum_list` WHERE id = '{$id}'");
		if ($get_curriculum->num_rows > 0) {
			$curriculum = $get_curriculum->fetch_assoc();
			$curriculum_name = $curriculum['name']; // Assuming 'name' is the field you want to log
		} else {
			// Handle case where curriculum with $id doesn't exist
			$resp['status'] = 'failed';
			$resp['error'] = 'Curriculum not found';
			return json_encode($resp);
		}

		// Perform deletion
		$del = $this->conn->query("DELETE FROM `curriculum_list` WHERE id = '{$id}'");
		if ($del) {
			$resp['status'] = 'success';
			$resp['msg'] = "Curriculum '{$curriculum_name}' has been deleted successfully.";

			// Log activity
			$this->CurriculumlogActivity("Deleted curriculum '{$curriculum_name}'");

			$this->settings->set_flashdata('success', "Curriculum has been deleted successfully.");
		} else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}


	private function CurriculumlogActivity($action)
	{
		$username = $this->settings->userdata('username');
		$date = date('Y-m-d H:i:s');
		$action = $this->conn->real_escape_string($action);
		$sql = "INSERT INTO `activity_log` (`username`, `date`, `action`) VALUES ('$username', '$date', '$action')";
		$this->conn->query($sql); // Assuming $this->conn is your database connection
	}



	function save_archive()
	{
		// Generate Archive Code
		if (empty($_POST['id'])) {
			$pref = date("Ym");
			$code = sprintf("%'.04d", 1);
			while (true) {
				$check = $this->conn->query("SELECT * FROM archive_list WHERE archive_code = '{$pref}{$code}'")->num_rows;
				if ($check > 0) {
					$code = sprintf("%'.04d", abs($code) + 1);
				} else {
					break;
				}
			}
			$_POST['archive_code'] = $pref . $code;
			$_POST['student_id'] = $this->settings->userdata('id');
			$_POST['curriculum_id'] = $this->settings->userdata('curriculum_id');
		}

		// Sanitize Inputs
		if (isset($_POST['abstract'])) {
			$_POST['abstract'] = htmlentities($_POST['abstract'], ENT_QUOTES, 'UTF-8');
		}
		if (isset($_POST['members'])) {
			$_POST['members'] = htmlentities($_POST['members'], ENT_QUOTES, 'UTF-8');
		}

		extract($_POST);
		$data = "";

		foreach ($_POST as $k => $v) {
			if (!in_array($k, ['id']) && !is_array($_POST[$k])) {
				$v = $this->conn->real_escape_string($v);
				$data .= !empty($data) ? ", {$k}='{$v}'" : "{$k}='{$v}'";
			}
		}

		// Save to Database
		$sql = empty($id) ? "INSERT INTO archive_list SET {$data}" : "UPDATE archive_list SET {$data} WHERE id = '{$id}'";
		$save = $this->conn->query($sql);

		if ($save) {
			$aid = !empty($id) ? $id : $this->conn->insert_id;
			$resp = [
				'status' => 'success',
				'id' => $aid,
				'msg' => empty($id) ? "Archive was successfully submitted." : "Archive details were updated successfully."
			];

			// Create Upload Directories
			$uploadDirs = [
				'banners' => 'uploads/banners/',
				'pdf' => 'uploads/pdf/',
				'files' => 'uploads/files/',
				'sql' => 'uploads/sql/'
			];
			foreach ($uploadDirs as $dir) {
				if (!is_dir(base_app . $dir)) {
					mkdir(base_app . $dir, 0755, true);
				}
			}

			// Handle File Uploads
			handleFileUpload($_FILES['img'], $aid, 'image', $uploadDirs['banners'], ['png', 'jpeg', 'jpg']);
			handleFileUpload($_FILES['pdf'], $aid, 'pdf', $uploadDirs['pdf'], ['pdf']);
			handleZipUpload($_FILES['zipfiles'], $aid, $uploadDirs['files'], ['zip']);
			handleFileUpload($_FILES['sql'], $aid, 'sql', $uploadDirs['sql'], ['sql'], true);

		} else {
			$resp = [
				'status' => 'failed',
				'msg' => "An error occurred while saving the data."
			];
		}

		return json_encode($resp);
	}

	/**
	 * Function to handle file upload
	 */
	function handleFileUpload($file, $aid, $type, $dir, $allowedExtensions, $zip = false)
	{
		if (isset($file) && $file['tmp_name'] != '') {
			$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
			$allowed = $allowedExtensions;
			if (!in_array($ext, $allowed)) {
				echo json_encode(['status' => 'failed', 'msg' => ucfirst($type) . " file type not allowed."]);
				exit;
			}

			$fileName = "{$type}-{$aid}.{$ext}";
			$filePath = base_app . $dir . $fileName;

			if ($zip) {
				$zipFile = new ZipArchive();
				$zipFileName = "{$type}-{$aid}.zip";
				$zipFilePath = base_app . $dir . $zipFileName;

				if ($zipFile->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
					$zipFile->addFile($file['tmp_name'], $file['name']);
					$zipFile->close();
					updateArchiveList($aid, "{$dir}{$zipFileName}");
				} else {
					echo json_encode(['status' => 'failed', 'msg' => ucfirst($type) . " ZIP creation failed."]);
					exit;
				}
			} else {
				if (move_uploaded_file($file['tmp_name'], $filePath)) {
					updateArchiveList($aid, "{$dir}{$fileName}");
				} else {
					echo json_encode(['status' => 'failed', 'msg' => ucfirst($type) . " upload failed."]);
					exit;
				}
			}
		}
	}

	/**
	 * Function to handle zip uploads for multiple files
	 */
	function handleZipUpload($files, $aid, $dir, $allowedExtensions)
	{
		if (isset($files) && !empty($files['name'][0])) {
			$zipFile = new ZipArchive();
			$zipFileName = "Files-{$aid}.zip";
			$zipFilePath = base_app . $dir . $zipFileName;

			if ($zipFile->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
				foreach ($files['tmp_name'] as $key => $tmp_name) {
					$ext = strtolower(pathinfo($files['name'][$key], PATHINFO_EXTENSION));
					if (in_array($ext, $allowedExtensions) && is_uploaded_file($tmp_name)) {
						$zipFile->addFile($tmp_name, $files['name'][$key]);
					}
				}
				$zipFile->close();
				updateArchiveList($aid, "{$dir}{$zipFileName}");
			} else {
				echo json_encode(['status' => 'failed', 'msg' => "ZIP file creation failed."]);
				exit;
			}
		}
	}

	/**
	 * Update archive_list with file paths
	 */
	function updateArchiveList($aid, $path)
	{
		global $conn;
		$conn->query("UPDATE archive_list SET folder_path = CONCAT('{$path}', '?v=', unix_timestamp(CURRENT_TIMESTAMP)) WHERE id = '{$aid}'");
	}



	function get_upload_progress()
	{
		session_start();
		$progress = $_SESSION['upload_progress'] ?? 0;

		echo json_encode(['progress' => $progress]);
		exit;
	}



	//    DELETE ARCHIVE
	function delete_archive()
	{
		extract($_POST);
		$get = $this->conn->query("SELECT * FROM `archive_list` where id = '{$id}'");
		$del = $this->conn->query("DELETE FROM `archive_list` where id = '{$id}'");
		if ($del) {
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success', "Archive Records has deleted successfully.");
			if ($get->num_rows > 0) {
				$res = $get->fetch_array();
				$banner_path = explode("?", $res['banner_path'])[0];
				$document_path = explode("?", $res['document_path'])[0];
				if (is_file(base_app . $banner_path))
					unlink(base_app . $banner_path);
				if (is_file(base_app . $document_path))
					unlink(base_app . $document_path);
			}
		} else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);
	}



	// UPDATE ARCHIVE
	function update_status()
	{
		extract($_POST);

		// Update the project status
		$update = $this->conn->query("UPDATE archive_list SET status = '{$status}' WHERE id = '{$id}'");

		if ($update) {
			// Notify the student
			$this->notify_student($id, $status);

			$resp['status'] = 'success';
			$resp['msg'] = "Archive status has been successfully updated.";

			$this->UpdateArchivelogActivity('Program saved or updated: ' . $name);
		} else {
			$resp['status'] = 'failed';
			$resp['msg'] = "An error occurred. Error: " . $this->conn->error;
		}

		if ($resp['status'] == 'success')
			$this->settings->set_flashdata('success', $resp['msg']);

		return json_encode($resp);
	}


	private function UpdateArchivelogActivity($action)
	{
		$username = $this->settings->userdata('username');
		$date = date('Y-m-d H:i:s');
		$action = $this->conn->real_escape_string($action);
		$sql = "INSERT INTO `activity_log` (`username`, `date`, `action`) VALUES ('$username', '$date', '$action')";
		$this->conn->query($sql); // Assuming $this->conn is your database connection
	}

	function notify_student($archive_id, $status)
	{
		$status_message = $status == 1 ? "published" : "unpublished";

		// Get the student ID associated with the archive
		$result = $this->conn->query("SELECT student_id FROM archive_list WHERE id = '{$archive_id}'");
		$row = $result->fetch_assoc();
		$student_id = $row['student_id'];

		// Create the notification message
		$message = "Your project has been {$status_message} by the admin.";

		// Insert the notification into the database
		$stmt = $this->conn->prepare("INSERT INTO notifications (student_id, message) VALUES (?, ?)");
		$stmt->bind_param("is", $student_id, $message);
		$stmt->execute();
	}


}

$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
	case 'save_program':
		echo $Master->save_program();
		break;
	case 'delete_program':
		echo $Master->delete_program();
		break;
	case 'save_curriculum':
		echo $Master->save_curriculum();
		break;
	case 'delete_curriculum':
		echo $Master->delete_curriculum();
		break;
	case 'save_archive':
		echo $Master->save_archive();
		break;
	case 'delete_archive':
		echo $Master->delete_archive();
		break;
	case 'update_status':
		echo $Master->update_status();
		break;
	default:
		// echo $sysset->index();
		break;
}