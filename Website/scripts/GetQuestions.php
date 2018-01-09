<?php
    $institutionName = $_GET['institutionName'];
    
    $response = array();

    require_once 'utils/database.php'; // Provides handle to sql session
	require_once 'connectors/QuestionsConnector.php'; // Gives all the functions related to Questions
    require_once 'connectors/InstitutionConnector.php'; // Gives all the functions related to Institutions

    if($institutionName == null) {
        $response["message"] = "Institution name is empty";
		$response["success"] = false;
		
		echo(json_encode($response)); // Return error message
		return;
    }

    $QuestionsConnector = new QuestionsConnector($conn);
    $InstitutionConnector = new InstitutionConnector($conn);

    $institution = $InstitutionConnector->select($institutionName);

    if(!$institution) {
        $response["message"] = "Institution does not exist";
		$response["success"] = false;
		
		echo(json_encode($response)); // Return error message
		return;
    }

    $resultArray = $QuestionsConnector->selectByInstitution($institution[InstitutionConnector::$COLUMN_ID]);
    
    if(!resultArray) {
        $response["message"] = "Failed to retrieve questions";
		$response["success"] = false;

    } else {
		$response["success"] = true;
		$response["questions"] = $resultArray;
	}

    echo(json_encode($response)); // Return error message
    return;



?>
