<?php
	// Open a new MySQLi connection to the database
	$conn = new mysqli("localhost", "cacti", "cacti", "cacti");
	
	// Check for and report any errors
	if($conn->connect_error) {
		$response["success"] = false;
		$response["message"] = "Connection failed: " . $conn->connect_error;
		
		die(json_encode($response));
	} 
?>
