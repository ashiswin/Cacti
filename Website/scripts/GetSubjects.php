<?php
	// Call this script when someone is loging in
	$institution = trim($_GET['institution']);
	
	$response = array();
	
	require_once 'utils/database.php'; // Provides handle to sql session
	require_once 'connectors/SubjectsConnector.php'; 
	require_once 'connectors/InstitutionConnector.php';
	
	$InstitutionConnector = new InstitutionConnector($conn);
	$institutionId = $InstitutionConnector->select($institution)["id"];
	
	$SubjectsConnector = new SubjectsConnector($conn);
	$subjects = $SubjectsConnector->selectAll(); // Check if username exists in Database
	
	if(!$subjects) {
		$response["success"] = false;
		$response["message"] = "Failed to select user";
	}
	else {
		$response["success"] = true;
		$response["subjects"] = $subjects;
	}
	
	echo(json_encode($response)); // return a response
?>
