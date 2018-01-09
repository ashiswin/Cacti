<?php
    require_once 'utils/database.php';
    require_once 'connectors/InstitutionUsersConnector.php';
    require_once 'connectors/InstitutionConnector.php';

    $userId = trim($_POST['userId']);
    $inviteCode = trim($_POST['inviteCode']);
    
    $InstitutionUsersConnector = new InstitutionUsersConnector($conn);
    $InstitutionConnector = new InstitutionConnector($conn);
    
    $result = $InstitutionConnector->selectByProfLink($inviteCode);
    $prof = 1;

    $response = array();

    if(!$result) {
        $result = $InstitutionConnector->selectByStudentLink($inviteCode);
        
        if(!$result) {
            $response["message"] = "Invalid invite code";
            $response["success"] = false;

            echo(json_encode($response)); // Return error message
            return;
        }
        
        else {
            $prof = 0;
        }
    }

    $response["oihs"] = $result[InstitutionConnector::$COLUMN_ID] . "|" . $userId . "|" . $prof;
    $result = $InstitutionUsersConnector->create($result[InstitutionConnector::$COLUMN_ID], $userId, $prof);

    if(!$result) {
        $response["message"] = "Entry not created: " . $result;
	$response["success"] = false;
    } else {
        $response["success"] = true;
        $response['institutionId'] = $result[InstitutionConnector::$COLUMN_ID]; //record Institution id
    }

    echo(json_encode($response)); // Return error message
    return;
     

?>
