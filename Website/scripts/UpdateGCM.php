<?php
    $userId = $_POST['userId'];
    $GCMToken = $_POST['GCMToken'];

    $response = array();

    require_once 'utils/database.php';
    require_once 'connectors/AccountConnector.php';

    $AccountConnector = new AccountConnector($conn);

    if($userId == null || $GCMToken == null){
        $response["message"] = "Entries incomplete";
		$response["success"] = false;
		
		echo(json_encode($response)); // Return error message
		return;
    }

    // If nothing wrong with the input, continue with creating new question in database
    
    $result = $AccountConnector->updateGCMToken($GCMToken, $userId);

    if(!$result) {
        $response["message"] = "GCM Token not updated";
		$response["success"] = false;
        
    } else {
        $response["success"] = true;
        $response['userId'] = $result[AccountConnector::$COLUMN_ID]; //record user id
    }

    echo(json_encode($response)); // Return response message
?>