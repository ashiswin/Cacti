<?php
	class AccountConnector {
		private $mysqli = NULL;
		
		public static $TABLE_NAME = "users";
		public static $COLUMN_ID = "id";
        public static $COLUMN_NAME = "name";
		public static $COLUMN_USERNAME = "username";
		public static $COLUMN_PASSWORD_HASH = "passwordHash";
		public static $COLUMN_SALT = "salt";
		public static $COLUMN_EMAIL = "email";
		public static $COLUMN_POINTS = "points";
        public static $COLUMN_GCM_TOKEN = "gcmToken";
		
        // The prepare statements exist to prevent SQL injection
		private $createStatement = NULL;
		private $selectStatement = NULL;
		private $selectByIdStatement = NULL;
		private $selectAllStatement = NULL;
		private $deleteStatement = NULL;
        private $updatePWStatement = NULL;
        private $updateNameStatement = NULL;
        private $updatePointsStatement = NULL;
        private $updateGCMStatement = NULL;
		
		function __construct($mysqli) {
            // This class requires utils/database.php
            // The input to the constructor is a handle to the sql session. Should be $conn
			if($mysqli->connect_errno > 0){
				die('Unable to connect to database [' . $mysqli->connect_error . ']');
			}
			
			$this->mysqli = $mysqli;
			
            // createStatement creates a new account
			$this->createStatement = $mysqli->prepare("INSERT INTO " . AccountConnector::$TABLE_NAME . "(`" . AccountConnector::$COLUMN_USERNAME . "`, `" . AccountConnector::$COLUMN_PASSWORD_HASH . "`, `" . AccountConnector::$COLUMN_SALT . "`, `" . AccountConnector::$COLUMN_EMAIL . "`, `" . AccountConnector::$COLUMN_NAME . "`) VALUES(?, ?, ?, ?, ?)");
			
            // selectStatement searches for an account of the input username
            $this->selectStatement = $mysqli->prepare("SELECT * FROM `" . AccountConnector::$TABLE_NAME . "` WHERE `" . AccountConnector::$COLUMN_USERNAME . "` = ?");
			
            // selectByIdStatement searches for an account of the input Id. This is faster than selectStatement
            $this->selectByIdStatement = $mysqli->prepare("SELECT * FROM `" . AccountConnector::$TABLE_NAME . "` WHERE `" . AccountConnector::$COLUMN_ID . "` = ?");
			
            // selectAllStatement just grabs every account from the server
            $this->selectAllStatement = $mysqli->prepare("SELECT * FROM `" . AccountConnector::$TABLE_NAME . "`");
			
            // deleteStatement, you guess it! Deletes an account of input id
            $this->deleteStatement = $mysqli->prepare("DELETE FROM " . AccountConnector::$TABLE_NAME . " WHERE `" . AccountConnector::$COLUMN_ID . "` = ?");
		
            // updatePWStatement updates the password of the input id
            $this->updatePWStatement = $mysqli->prepare("UPDATE " . AccountConnector::$TABLE_NAME . " SET `" . AccountConnector::$COLUMN_PASSWORD_HASH . "` = ?, `" . AccountConnector::$COLUMN_SALT . "` = ? WHERE `" . AccountConnector::$COLUMN_ID . "` = ?");
            
            // updateStatement updates the password of the input id
            $this->updateNameStatement = $mysqli->prepare("UPDATE " . AccountConnector::$TABLE_NAME . " SET `" . AccountConnector::$COLUMN_NAME . "` = ? WHERE `" . AccountConnector::$COLUMN_ID . "` = ?");
            
            // updatePointsStatement updates the amount of points a user has
            $this->updatePointsStatement = $mysqli->prepare("UPDATE " . AccountConnector::$TABLE_NAME . " SET `" . AccountConnector::$COLUMN_POINTS . "` = ? WHERE `" . AccountConnector::$COLUMN_ID . "` = ?");
            
            // updateGCMStatement updates the GCM token of a user
            $this->updateGCMStatement = $mysqli->prepare("UPDATE " . AccountConnector::$TABLE_NAME . " SET `" . AccountConnector::$COLUMN_GCM_TOKEN . "` = ? WHERE `" . AccountConnector::$COLUMN_ID . "` = ?");
                
        }
		
		public function create($username, $passwordHash, $salt, $email, $name) {
            // Create new account using username, hashed password, salt and name of the person
			if($username == NULL) return false; // if you didn't enter a username, the method stops
			
			$this->createStatement->bind_param("sssss", $username, $passwordHash, $salt, $email, $name);
			if(!$this->createStatement->execute()) return false;
			
			return $this->mysqli->insert_id;
		}
		
		public function select($username) {
            // Select the account account of input username
			if($username == NULL) return false; // if you didn't enter a username, the method stops
			
			$this->selectStatement->bind_param("s", $username);
			if(!$this->selectStatement->execute()) return false; // if the query didn't execute, return false

			$result = $this->selectStatement->get_result();
			if(!$result) return false; // if the query didn't give a result, return false
			$account = $result->fetch_assoc();
			
			$this->selectStatement->free_result(); // releases memory
			
			return $account;
		}
        
		public function selectById($id) {
            // Select the account of account id
			$this->selectByIdStatement->bind_param("i", $id);
			if(!$this->selectByIdStatement->execute()) return false; // if the query didn't execute, return false

			$result = $this->selectByIdStatement->get_result();
			if(!$result) return false; // if the query didn't give a result, return false
			$account = $result->fetch_assoc();
			
			$this->selectByIdStatement->free_result(); // releases memory
			
			return $account;
		}
        
		public function selectAll() {
            // Grab everything from the table
			if(!$this->selectAllStatement->execute()) return false; // if the query didn't execute, return false
			$result = $this->selectAllStatement->get_result(); // frees memory
			$resultArray = $result->fetch_all(MYSQLI_ASSOC);
			return $resultArray;
		}
		
		public function updatePassword($password, $salt, $id) {
            // Updates account password of account id
            // $password should have been hashed before calling this function
			$this->updatePWStatement->bind_param("ssi", $password, $salt, $id);
			
			if(!$this->updatePWStatement->execute()) return false; // if the query didn't execute, return false
			return true;
		}
        
        public function updateName($new_name, $id) {
            // Updates the display name of account id
            $this->updateNameStatement->bind_param("si", $new_name, $id);
            
            if(!$this->updateNameStatement->execute()) return false; // if the query didn't execute, return false
			return true;
        }
        
        public function updatePoints($new_points, $id) {
            // Updates the amount of points of account id
            $this->updatePointsStatement->bind_param("ii", $new_points, $id);
            
            if(!$this->updatePointsStatement->execute()) return false; // if the query didn't execute, return false
            return true;
        }
        
        public function updateGCMToken($gcmToken, $id) {
            // Updates GCM token of account id
            $this->updateGCMStatement->bind_param("si", $gcmToken, $id);
            
            if(!$this->updateGCMStatement->execute()) return false; // if the query didn't execute, return false
            return true;
        }
		
		public function delete($id) {
            // Deletes account of id
			$this->deleteStatement->bind_param("i", $id);
			if(!$this->deleteStatement->execute()) return false; // if the query didn't execute, return false
			
			return true;
		}
	}
?>
