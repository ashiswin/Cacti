<?php
	// Use this script when you want to update user account's display name
    // $_POST requires user id and new name

	$userId = trim($_POST['userId']);
	$name = trim($_POST['name']);
	
	$response = array();
	
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
		
	if(!$AccountConnector->updateName($password, $userId)) { // Update the display name
		$response["message"] = "An error occurred while updating password";
		$response["success"] = false;
	}
	else {
		$response["success"] = true;
	}
	
	echo(json_encode($response));
?>
