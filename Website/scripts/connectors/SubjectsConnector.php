<?php
	class SubjectsConnector {
		private $mysqli = NULL;
		
		public static $TABLE_NAME = "subjects";
        public static $COLUMN_INSTITUTION = "institution";
        public static $COLUMN_ID = "id";
        public static $COLUMN_NAME = "name";
		
        // The prepare statements exist to prevent SQL injection
		private $createStatement = NULL;
		private $selectByInstitutionStatement = NULL;
        private $selectByNameStatement = NULL;
        private $selectByIdStatement = NULL;
		private $selectAllStatement = NULL;
        private $deleteStatement = NULL;
        
		
		function __construct($mysqli) {
            // This class requires utils/database.php
            // The input to the constructor is a handle to the sql session. Should be $conn
			if($mysqli->connect_errno > 0){
				die('Unable to connect to database [' . $mysqli->connect_error . ']');
			}
			
			$this->mysqli = $mysqli;
			
            // createStatement creates a new entry
			$this->createStatement = $mysqli->prepare("INSERT INTO " . SubjectsConnector::$TABLE_NAME . "(`" . SubjectsConnector::$COLUMN_INSTITUTION . "`, `" . SubjectsConnector::$COLUMN_NAME . "`) VALUES(?, ?)");
			
            // selectByInstitutionStatement searches for all the subjects from institution
            $this->selectByInstitutionStatement = $mysqli->prepare("SELECT * FROM `" . SubjectsConnector::$TABLE_NAME . "` WHERE `" . SubjectsConnector::$COLUMN_INSTITUTION . "` = ?");
            
            // selectByNameStatement searches for a subject of the input name and from a specified institution. 
            $this->selectByNameStatement = $mysqli->prepare("SELECT * FROM `" . SubjectsConnector::$TABLE_NAME . "` WHERE `" . SubjectsConnector::$COLUMN_NAME . "` = ? AND " . SubjectsConnector::$COLUMN_INSTITUTION . "` = ?");
            
            // selectByIdStatement searches for a subject of the input Id. 
            $this->selectByInstitutionStatement = $mysqli->prepare("SELECT * FROM `" . SubjectsConnector::$TABLE_NAME . "` WHERE `" . SubjectsConnector::$COLUMN_INSTITUTION . "` = ?");
	    $this->selectByIdStatement = $mysqli->prepare("SELECT * FROM `" . SubjectsConnector::$TABLE_NAME . "` WHERE `" . SubjectsConnector::$COLUMN_ID . "` = ?");
			
            // selectAllStatement just grabs every entry from the server
            $this->selectAllStatement = $mysqli->prepare("SELECT * FROM `" . SubjectsConnector::$TABLE_NAME . "`");
			
            // deleteUserStatement, you guess it! Deletes a subject of input id
            $this->deleteSubjectStatement = $mysqli->prepare("DELETE FROM " . SubjectsConnector::$TABLE_NAME . " WHERE `" . SubjectsConnector::$COLUMN_ID . "` = ?");
		
            // deleteInstitutionStatement, you guess it! Deletes all subjects from specified institution
            $this->deleteInstitutionStatement = $mysqli->prepare("DELETE FROM " . SubjectsConnector::$TABLE_NAME . " WHERE `" . SubjectsConnector::$COLUMN_INSTITUTION . "` = ?");      
        }
		
		public function create($institution, $name) {
            // Create new entry using institution id, and name of the subject
			if($institution == NULL || $name == NULL) return false; // if you didn't enter some entries, the method stops
			
			$this->createStatement->bind_param("is", $institution, $name);
			return $this->createStatement->execute();
		}
        
		public function selectByInstitution($institutionId) {
            // Select the subjects belonging to institution
			$this->selectByInstitutionStatement->bind_param("i", $institutionId);
			if(!$this->selectByInstitutionStatement->execute()) return false; // if the query didn't execute, return false

			$result = $this->selectByInstitutionStatement->get_result();
			if(!$result) return false; // if the query didn't give a result, return false
			$subjects = $result->fetch_all(MYSQLI_ASSOC);
			
			$this->selectByInstitutionStatement->free_result(); // releases memory
			
			return $subjects;
		}
		
		public function selectById($id) {
			$this->selectByIdStatement->bind_param("i", $id);
			if(!$this->selectByIdStatement->execute()) return false; // if the query didn't execute, return false

			$result = $this->selectByIdStatement->get_result();
			if(!$result) return false; // if the query didn't give a result, return false
			$subjects = $result->fetch_all(MYSQLI_ASSOC);
			
			$this->selectByIdStatement->free_result(); // releases memory
			
			return $subjects;
		}
        
        public function selectByName($name, $institutionId) {
            // Select the subject by its name and the institution it belongs to
			$this->selectByNameStatement->bind_param("si" ,$name, $institutionId);
			if(!$this->selectByNameStatement->execute()) return false; // if the query didn't execute, return false

			$result = $this->selectByNameStatement->get_result();
			if(!$result) return false; // if the query didn't give a result, return false
			$subject = $result->fetch_assoc();
			
			$this->selectByNameStatement->free_result(); // releases memory
			
			return $institution;
		}
        
		public function selectAll() {
            // Grab everything from the table
			if(!$this->selectAllStatement->execute()) return false; // if the query didn't execute, return false
			$result = $this->selectAllStatement->get_result(); // frees memory
			$resultArray = $result->fetch_all(MYSQLI_ASSOC);
			return $resultArray;
		}
		
        public function deleteSubject($subjectId) {
            // Deletes subject of subjectid
            $this->deleteSubjectStatement->bind_param("i", $subjectId);
			if(!$this->deleteSubjectStatement->execute()) return false; // if the query didn't execute, return false
			
			return true;
        }
        
		public function deleteInstitution($institutionId) {
            // Deletes all the subjects belonging to institution
			$this->deleteInstitutionStatement->bind_param("i", $institutionId);
			if(!$this->deleteInstitutionStatement->execute()) return false; // if the query didn't execute, return false
			
			return true;
		}
	}
?>
