<?php
	class InstitutionConnector {
		private $mysqli = NULL;
		
		public static $TABLE_NAME = "institution";
		public static $COLUMN_ID = "id";
        public static $COLUMN_NAME = "name";
        public static $COLUMN_PROF_LINK = "profLink";
        public static $COLUMN_STUDENT_LINK = "studentLink";
		
        // The prepare statements exist to prevent SQL injection
		private $createStatement = NULL;
		private $selectStatement = NULL;
		private $selectByIdStatement = NULL;
        private $selectProfLinkStatement = NULL;
        private $selectStudentLinkStatement = NULL;
		private $selectAllStatement = NULL;
		private $deleteStatement = NULL;
        private $updateStatement = NULL;
        private $checkUniqueProfCodeStatement = NULL;
        private $checkUniqueStudentCodeStatement = NULL;
		
		function __construct($mysqli) {
            // This class requires utils/database.php
            // The input to the constructor is a handle to the sql session. Should be $conn
			if($mysqli->connect_errno > 0){
				die('Unable to connect to database [' . $mysqli->connect_error . ']');
			}
			
			$this->mysqli = $mysqli;
			
            // createStatement creates a new institution
			$this->createStatement = $mysqli->prepare("INSERT INTO " . InstitutionConnector::$TABLE_NAME . "(`" . InstitutionConnector::$COLUMN_NAME . "`, `" . InstitutionConnector::$COLUMN_PROF_LINK . "`, `" . InstitutionConnector::$COLUMN_STUDENT_LINK . "`) VALUES(?, ?, ?)");
			
            // selectStatement searches for an institution of the input name
            $this->selectStatement = $mysqli->prepare("SELECT * FROM `" . InstitutionConnector::$TABLE_NAME . "` WHERE `" . InstitutionConnector::$COLUMN_NAME . "` = ?");
			
            // selectByIdStatement searches for an institution of the input Id. This is faster than selectStatement
            $this->selectByIdStatement = $mysqli->prepare("SELECT * FROM `" . InstitutionConnector::$TABLE_NAME . "` WHERE `" . InstitutionConnector::$COLUMN_ID . "` = ?");
			
            // selectProfLinkStatement searches for an institution with the input profLink
            $this->selectProfLinkStatement = $mysqli->prepare("SELECT * FROM `" . InstitutionConnector::$TABLE_NAME . "` WHERE `" . InstitutionConnector::$COLUMN_PROF_LINK . "` = ?");
                
            // selectStudentLinkStatement searches for an institution with the input studentLink
            $this->selectStudentLinkStatement = $mysqli->prepare("SELECT * FROM `" . InstitutionConnector::$TABLE_NAME . "` WHERE `" . InstitutionConnector::$COLUMN_STUDENT_LINK . "` = ?");
            
            // selectAllStatement just grabs every institution from the server
            $this->selectAllStatement = $mysqli->prepare("SELECT * FROM `" . InstitutionConnector::$TABLE_NAME . "`");
			
            // deleteStatement, you guess it! Deletes an institution of input id
            $this->deleteStatement = $mysqli->prepare("DELETE FROM " . InstitutionConnector::$TABLE_NAME . " WHERE `" . InstitutionConnector::$COLUMN_ID . "` = ?");
		
            // updateStatement updates the name of the input institution id
            $this->updateStatement = $mysqli->prepare("UPDATE " . InstitutionConnector::$TABLE_NAME . " SET `" . InstitutionConnector::$COLUMN_NAME . "` = ? WHERE `" . InstitutionConnector::$COLUMN_ID . "` = ?");
            
            // checkUniqueProfCodeStatement searches the database for another institute with the same prof code
            $this->checkUniqueProfCodeStatement = $mysqli->prepare("SELECT 1 FROM " . InstitutionConnector::$TABLE_NAME . " WHERE " . InstitutionConnector::$COLUMN_PROF_LINK . "=? LIMIT 1");
            
            // checkUniqueStudentCodeStatement searches the database for another institute with the same student code
            $this->checkUniqueStudentCodeStatement = $mysqli->prepare("SELECT 1 FROM " . InstitutionConnector::$TABLE_NAME . " WHERE " . InstitutionConnector::$COLUMN_STUDENT_LINK . "=? LIMIT 1");
                
        }
		
		public function create($name, $profLink, $studentLink) {
            // Create new Institution using name, prof invite code and student invite code
			if($name == NULL || $profLink == NULL || $studentLink == NULL) return false; // if you didn't enter any entry, the method stops
			
			$this->createStatement->bind_param("sss", $name, $profLink, $studentLink);
			return $this->createStatement->execute();
		}
		
		public function select($name) {
            // Select the Institution of input name
			if($name == NULL) return false; // if you didn't enter a name, the method stops
			
			$this->selectStatement->bind_param("s", $name);
			if(!$this->selectStatement->execute()) return false; // if the query didn't execute, return false

			$result = $this->selectStatement->get_result();
			if(!$result) return false; // if the query didn't give a result, return false
			$institution = $result->fetch_assoc();
			
			$this->selectStatement->free_result(); // releases memory
			
			return $institution;
		}
        
		public function selectById($id) {
            // Select the Institution of input id
			$this->selectByIdStatement->bind_param("i", $id);
			if(!$this->selectByIdStatement->execute()) return false; // if the query didn't execute, return false

			$result = $this->selectByIdStatement->get_result();
			if(!$result) return false; // if the query didn't give a result, return false
			$institution = $result->fetch_assoc();
			
			$this->selectByIdStatement->free_result(); // releases memory
			
			return $institution;
		}
		public function selectAll() {
            // Grab everything from the table
			if(!$this->selectAllStatement->execute()) return false; // if the query didn't execute, return false
			$result = $this->selectAllStatement->get_result(); // frees memory
			$resultArray = $result->fetch_all(MYSQLI_ASSOC);
			return $resultArray;
		}
		
        public function selectByProfLink($profLink) {
            // Searches for an entry with input $proflink
            $this->selectProfLinkStatement->bind_param("s", $profLink);
            if(!$this->selectProfLinkStatement->execute()) return false; // if the query didn't execute, return false
            
            $result = $this->selectProfLinkStatement->get_result();
			if(!$result) return false; // if the query didn't give a result, return false
			$institution = $result->fetch_assoc();
			
			$this->selectProfLinkStatement->free_result(); // releases memory
			
			return $institution;
        }
        
        public function selectByStudentLink($studentLink) {
            // Searches for an entry with input $proflink
            $this->selectStudentLinkStatement->bind_param("s", $studentLink);
            if(!$this->selectStudentLinkStatement->execute()) return false; // if the query didn't execute, return false
            
            $result = $this->selectStudentLinkStatement->get_result();
			if(!$result) return false; // if the query didn't give a result, return false
			$institution = $result->fetch_assoc();
			
			$this->selectStudentLinkStatement->free_result(); // releases memory
			
			return $institution;
        }
        
        
		public function updateName($name, $id) {
            // Updates Institution name of Institution id
			$this->updateStatement->bind_param("si", $name, $id);
			
			if(!$this->updateStatement->execute()) return false; // if the query didn't execute, return false
			return true;
		}
		
		public function delete($id) {
            // Deletes Institution of id
			$this->deleteStatement->bind_param("i", $id);
			if(!$this->deleteStatement->execute()) return false; // if the query didn't execute, return false
			
			return true;
		}
        
        public function checkUniqueProfCode($code){
            // Checks if $code is unique
			if($code == NULL) return false;

			$this->checkUniqueProfCodeStatement->bind_param("s", $code);
			$this->checkUniqueProfCodeStatement->execute();
			$result = $this->checkUniqueProfCodeStatement->get_result();

			if(!isset($result)){
				die('Query failed ' . mysqli_error($this->mysqli));
			}
			else if(mysqli_num_rows($result) == 0) { // if no other entry has this code
				return true;
			}
			else {
				return false;
			}
		}
        
        public function checkUniqueStudentCode($code){
            // Checks if $code is unique
			if($code == NULL) return false;

			$this->checkUniqueStudentCodeStatement->bind_param("s", $code);
			$this->checkUniqueStudentCodeStatement->execute();
			$result = $this->checkUniqueStudentCodeStatement->get_result();

			if(!isset($result)){
				die('Query failed ' . mysqli_error($this->mysqli));
			}
			else if(mysqli_num_rows($result) == 0) { // if no other entry has this code
				return true;
			}
			else {
				return false;
			}
		}
	}
?>
