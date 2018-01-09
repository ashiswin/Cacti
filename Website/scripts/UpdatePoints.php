<?php
    $increment = intval($_POST['increment']);
    $userId = $_POST['userId'];
    $response = array();

    if($increment == 0){
        $response["message"] = "Increment must be an int";
		$response["success"] = false;
    }

    require_once 'utils/database.php'; // Provides handle to sql session
	require_once 'connectors/AccountConnector.php'; // Gives all the functions related to account

    $result = $AccountConnector->selectById($userId); // Find the row in database for this userId

	if(!$result) { // If the row can't be found
		$response["message"] = "Uable to select account";
		$response["success"] = false;
		
		echo(json_encode($response));
		return;
	}

    if(!$AccountConnector->updatePoints($result[AccountConnect::$COLUMN_POINTS] + $increment, $userId)) { // Update the display name
		$response["message"] = "An error occurred while updating points";
		$response["success"] = false;
	}
	else {
		$response["success"] = true;
	}
	
	echo(json_encode($response));

?>