<?php
    $userId = $_POST['userId'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $subject = $_POST['subject'];
    $institution = $_POST['institution'];

    $response = array();

    require_once 'utils/database.php'; // Provides handle to sql session
	require_once 'connectors/QuestionsConnector.php'; // Gives all the functions related to Admin
	require_once 'connectors/InstitutionConnector.php';
	
	$InstitutionConnector = new InstitutionConnector($conn);
	$institutionId = $InstitutionConnector->select($institution)["id"];
    $QuestionsConnector = new QuestionsConnector($conn);

    if($userId == null || $title == null || $subject == null || $institution == null) {
        $response["message"] = "Entries incomplete";
		$response["success"] = false;
		
		echo(json_encode($response)); // Return error message
		return;
    }

    if(count($QuestionsConnector->selectByTitle($title, $institution)) > 0) { // If there is another of this username in Database
		$response["message"] = "Someone has already asked this question";
		$response["success"] = false;
		
		echo(json_encode($response)); // Return error message
		return;
	}

    // If nothing wrong with the input, continue with creating new question in database
    
    $result = $QuestionsConnector->create($institutionId, $title, $description, $subject, null, $userId);

    if(!$result) {
        $response["message"] = "Question not created";
		$response["success"] = false;
        
    } else {
        $response["success"] = true;
        $response['questionId'] = $result[QuestionsConnector::$COLUMN_ID]; //record question id
    }

    echo(json_encode($response)); // Return response message

?>
