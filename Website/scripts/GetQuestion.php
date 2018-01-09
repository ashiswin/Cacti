<?php
	$questionId = $_GET['questionId'];

	$response = array();

	require_once 'utils/database.php'; // Provides handle to sql session
	require_once 'connectors/QuestionsConnector.php'; // Gives all the functions related to Questions
	require_once 'connectors/SubjectsConnector.php';
	require_once 'connectors/AccountConnector.php';
	require_once 'connectors/RepliesConnector.php';
	
	if($questionId == null) {
		$response["message"] = "Question ID is empty";
		$response["success"] = false;

		echo(json_encode($response)); // Return error message
		return;
	}

	$QuestionsConnector = new QuestionsConnector($conn);
	$SubjectsConnector = new SubjectsConnector($conn);
	$AccountConnector = new AccountConnector($conn);
	$RepliesConnector = new RepliesConnector($conn);
	$question = $QuestionsConnector->selectById($questionId);
	$question["subjectName"] = $SubjectsConnector->selectById($question["subject"])[0]["name"];
	$question["askerName"] = $AccountConnector->selectById($question["asker"])["name"];
	
	$replyArray = explode("|", $question["replies"]);
	$replies = array();
	$count = 0;
	
	for($i = 0; $i < count($replyArray); $i++) {
		if($replyArray[$i] == NULL || strcmp($replyArray[$i], "") == 0) continue;
		
		$replies[$count] = $RepliesConnector->selectById(intval($replyArray[$i]));
		$replies[$count]["replierName"] = $AccountConnector->selectById($replies[$count]["replier"])["name"];
		$replies[$count]["replierEmail"] = $AccountConnector->selectById($replies[$count]["replier"])["email"];
		$count++;
	}
	
	$question["replies"] = $replies;
	
	if(!$question) {
		$response["message"] = "Question does not exist";
		$response["success"] = false;

		echo(json_encode($response)); // Return error message
		return;
	}


	$response["success"] = true;
	$response["question"] = $question;

	echo(json_encode($response)); // Return error message
	return;



?>
