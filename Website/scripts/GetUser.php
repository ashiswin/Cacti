<?php
	// Call this script when someone is loging in
	$userId = intval($_GET['userId']);
	
	$response = array();
	
	require_once 'utils/database.php'; // Provides handle to sql session
	require_once 'connectors/AccountConnector.php'; // Gives all the functions related to Admin
	require_once 'connectors/InstitutionUsersConnector.php';
	require_once 'connectors/InstitutionConnector.php';
	
	$AccountConnector = new AccountConnector($conn);
	$result = $AccountConnector->selectById($userId); // Check if username exists in Database
	
	$InstitutionUsersConnector = new InstitutionUsersConnector($conn);
	$InstitutionConnector = new InstitutionConnector($conn);

	$institutionIds = $InstitutionUsersConnector->selectByUser($userId);
	$institutions = array();
	
	for($i = 0; $i < count($institutionIds); $i++) {
		$institutions[$i] = $InstitutionConnector->selectById($institutionIds[$i]["institution"]);
	}
	
	$result["institutions"] = $institutions;
	
	if(!$result) {
		$response["success"] = false;
		$response["message"] = "Failed to select user";
	}
	else {
		$response["success"] = true;
		$response["user"] = $result;
	}
	
	echo(json_encode($response)); // return a response
?>
