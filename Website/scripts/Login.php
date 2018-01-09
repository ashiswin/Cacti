<?php
    // Call this script when someone is loging in
	$username = trim($_POST['username']);
	$password = trim($_POST['password']);
	
	$response = array();
	
	require_once 'utils/database.php'; // Provides handle to sql session
	require_once 'connectors/AccountConnector.php'; // Gives all the functions related to Admin
	
	$AccountConnector = new AccountConnector($conn);
	$result = $AccountConnector->select($username); // Check if username exists in Database

	if(!$result) { // If it doesn't exist
		$response["message"] = "Invalid username or password!";
		$response["success"] = false;
	}
	else { // Check if password is correct
		$passwordHash = hash('sha512', ($password . $result[AccountConnector::$COLUMN_SALT]));
		if(strcmp($passwordHash, $result[AccountConnector::$COLUMN_PASSWORD_HASH]) == 0) { // If password matches
			$response["success"] = true;
			$response["userId"] = $result[AccountConnector::$COLUMN_ID]; // Record the user id
		}
		else { // If password does not match
			$response["success"] = false;
			$response["message"] = "Invalid username or password!";
		}
	}
	
	echo(json_encode($response)); // return a response
?>
