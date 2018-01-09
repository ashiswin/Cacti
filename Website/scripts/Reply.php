<?php
	$userId = $_POST['userId'];
	$description = $_POST['description'];
	$questionId = $_POST['questionId'];

	$response = array();

	require_once 'utils/database.php'; // Provides handle to sql session
	require_once 'connectors/QuestionsConnector.php'; // Gives all the functions related to Admin
	require_once 'connectors/RepliesConnector.php';

	$RepliesConnector = new RepliesConnector($conn);
	$QuestionsConnector = new QuestionsConnector($conn);

	$result = $RepliesConnector->create($description, null, $userId);
	$replies = $QuestionsConnector->selectById($questionId)["replies"];
	
	$QuestionsConnector->updateReplies($replies . "|" . $result, $questionId);

	if(!$result) {
		$response["message"] = "Reply not created";
		$response["success"] = false;

	} else {
		$response["success"] = true;
		$response['replyId'] = $result; //record question id
	}

	echo(json_encode($response)); // Return response message

?>
