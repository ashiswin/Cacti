<?php
	// Use this script when you want to update user account's password
    // $_POST requires user id, current password and new password

	$userId = trim($_POST['userId']);
	$current = trim($_POST['current']);
	$newPass = trim($_POST['newpass']);
	
	$response = array();
	
    require_once 'utils/random_gen.php';
	require_once 'utils/database.php';
	require_once 'connectors/AccountConnector.php';
	
	$AccountConnector = new AccountConnector($conn);
	$result = $AccountConnector->selectById($userId); // Find the row in database for this userId

	if(!$result) { // If the row can't be found
		$response["message"] = "Uable to select account";
		$response["success"] = false;
		
		echo(json_encode($response));
		return;
	}
	
	$passwordHash = hash('sha512', ($current . $result[AccountConnector::$COLUMN_SALT]));
	if(!strcmp($passwordHash, $result[AdminConnector::$COLUMN_PASSWORD]) == 0) { // Check if current password is correct
		$response["success"] = false;
		$response["message"] = "Invalid username or password!";
		
		echo(json_encode($response));
		return;
	}
	
	$salt = random_str(10); // Generate new salt
	$password = hash('sha512', ($newPass . $salt)); // Hash the new password
	
	if(!$AccountConnector->updatePassword($password, $salt, $userId)) { // Update the password
		$response["message"] = "An error occurred while updating password";
		$response["success"] = false;
	}
	else {
		$response["success"] = true;
	}
	
	echo(json_encode($response));
?>
