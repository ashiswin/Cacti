<?php
    require_once 'utils/database.php';
    require_once 'connectors/InstitutionConnector.php';
    require_once 'utils/random_gen.php';

    $response = array();

    $institutionName = $_POST['institutionName'];
    $InstitutionConnector = new InstitutionConnector($conn);
        
    if($institutionName == NULL) {
        $response["message"] = "Institution name cannot be blank";
		$response["success"] = false;
		
		echo(json_encode($response)); // Return error message
		return;
    }

    if(count($InstitutionConnector->select($institutionName)) > 0) { // If there is another of this username in Database
		$response["message"] = "This institution name has been taken";
		$response["success"] = false;
		
		echo(json_encode($response)); // Return error message
		return;
	}

    // If nothing wrong with the input, continue with creating new institution in database
    $profLink = random_str(6);
    $studentLink = random_str(6);

    while($InstitutionConnector->checkUniqueProfCode($profLink) == false){ // ensure profLink is not repeated within database
        $profLink = random_str(); // if it is repeated, then generate a new one
    }

    while($InstitutionConnector->checkUniqueStudentCode($studentLink) == false){ // ensure studentLink is not repeated within database
        $studentLink = random_str(); // if it is repeated, then generate a new one
    }

    $result = $InstitutionConnector->create($institutionName, $profLink, $studentLink);

    if(!$result) {
        $response["message"] = "Institution not created";
		$response["success"] = false;
        
    } else {
        $response["success"] = true;
        $response['institutionId'] = $result[InstitutionConnector::$COLUMN_ID]; //record Institution id
    }

    echo(json_encode($response)); // Return error message
    
?>