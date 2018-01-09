<?php
	class InstitutionUsersConnector {
		private $mysqli = NULL;
		
		public static $TABLE_NAME = "institutionusers";
        public static $COLUMN_INSTITUTION = "institution";
        public static $COLUMN_USER = "user";
        public static $COLUMN_PROF = "prof";
		
        // The prepare statements exist to prevent SQL injection
		private $createStatement = NULL;
		private $selectUserStatement = NULL;
        private $selectInstitutionStatement = NULL;
		private $selectAllStatement = NULL;
		private $deleteUserStatement = NULL;
        private $deleteInstitutionStatement = NULL;
        
		
		function __construct($mysqli) {
            // This class requires utils/database.php
            // The input to the constructor is a handle to the sql session. Should be $conn
			if($mysqli->connect_errno > 0){
				die('Unable to connect to database [' . $mysqli->connect_error . ']');
			}
			
			$this->mysqli = $mysqli;
			
            // createStatement creates a new entry
			$this->createStatement = $mysqli->prepare("INSERT INTO " . InstitutionUsersConnector::$TABLE_NAME . "(`" . InstitutionUsersConnector::$COLUMN_INSTITUTION . "`, `" . InstitutionUsersConnector::$COLUMN_USER . "`, `" . InstitutionUsersConnector::$COLUMN_PROF . "`) VALUES(?, ?, ?)");
			
            // selectUserStatement searches for an account of the input id
            $this->selectUserStatement = $mysqli->prepare("SELECT * FROM `" . InstitutionUsersConnector::$TABLE_NAME . "` WHERE `" . InstitutionUsersConnector::$COLUMN_USER . "` = ?");
			
            // selectInstitutionStatement searches for an institution of the input Id. 
            $this->selectInstitutionStatement = $mysqli->prepare("SELECT * FROM `" . InstitutionUsersConnector::$TABLE_NAME . "` WHERE `" . InstitutionUsersConnector::$COLUMN_INSTITUTION . "` = ?");
			
            // selectAllStatement just grabs every entry from the server
            $this->selectAllStatement = $mysqli->prepare("SELECT * FROM `" . InstitutionUsersConnector::$TABLE_NAME . "`");
			
            // deleteUserStatement, you guess it! Deletes an user account of input id
            $this->deleteUserStatement = $mysqli->prepare("DELETE FROM " . InstitutionUsersConnector::$TABLE_NAME . " WHERE `" . InstitutionUsersConnector::$COLUMN_USER . "` = ?");
		
            // deleteInstitutionStatement, you guess it! Deletes an institution account of input id
            $this->deleteInstitutionStatement = $mysqli->prepare("DELETE FROM " . InstitutionUsersConnector::$TABLE_NAME . " WHERE `" . InstitutionUsersConnector::$COLUMN_INSTITUTION . "` = ?");      
        }
		
		public function create($institutionId, $userId, $prof) {
            // Create new entry using institutionid, userid, and prof status of the person
			if($institutionId == NULL || $userId == NULL || $prof == NULL) return false; // if you didn't enter some entries, the method stops
			
			$this->createStatement->bind_param("iii", $institutionId, $userId, $prof);
			return $this->createStatement->execute();
		}
		
		public function selectByUser($userId) {
            // Select all the entries with $userId
			if($userId == NULL) return false; // if you didn't enter a userId, the method stops
			
			$this->selectUserStatement->bind_param("i", $userId);
			if(!$this->selectUserStatement->execute()) return false; // if the query didn't execute, return false

			$result = $this->selectUserStatement->get_result();
			if(!$result) return false; // if the query didn't give a result, return false
			$user = $result->fetch_all(MYSQLI_ASSOC);
			
			$this->selectUserStatement->free_result(); // releases memory
			
			return $user;
		}
        
		public function selectByInstitution($institutionId) {
            // Select the admin account of admin id
			$this->selectInstitutionStatement->bind_param("i", $institutionId);
			if(!$this->selectInstitutionStatement->execute()) return false; // if the query didn't execute, return false

			$result = $this->selectInstitutionStatement->get_result();
			if(!$result) return false; // if the query didn't give a result, return false
			$institution = $result->fetch_all(MYSQLI_ASSOC);
			
			$this->selectInstitutionStatement->free_result(); // releases memory
			
			return $institution;
		}
		public function selectAll() {
            // Grab everything from the table
			if(!$this->selectAllStatement->execute()) return false; // if the query didn't execute, return false
			$result = $this->selectAllStatement->get_result(); // frees memory
			$resultArray = $result->fetch_all(MYSQLI_ASSOC);
			return $resultArray;
		}
		
        public function deleteUser($userId) {
            // Deletes user of userid
            $this->deleteUserStatement->bind_param("i", $userId);
			if(!$this->deleteUserStatement->execute()) return false; // if the query didn't execute, return false
			
			return true;
        }
        
		public function deleteInstitution($institutionId) {
            // Deletes institution of institutionId
			$this->deleteInstitutionStatement->bind_param("i", $institutionId);
			if(!$this->deleteInstitutionStatement->execute()) return false; // if the query didn't execute, return false
			
			return true;
		}
	}
?>
