<?php
	class QuestionsConnector {
		private $mysqli = NULL;
		
		public static $TABLE_NAME = "questions";
		public static $COLUMN_ID = "id";
        public static $COLUMN_INSTITUTION = "institution";
        public static $COLUMN_TITLE = "title";
		public static $COLUMN_DESCRIPTION = "description";
		public static $COLUMN_SUBJECT = "subject";
		public static $COLUMN_IMAGES = "images";
		public static $COLUMN_SCORE = "score";
		public static $COLUMN_ASKER = "asker";
		public static $COLUMN_ASK_DATE = "askDate";
		public static $COLUMN_REPLIES = "replies";
		
        // The prepare statements exist to prevent SQL injection
		private $createStatement = NULL;
        
		private $selectByIdStatement = NULL;
        private $selectByTitleStatement = NULL;
        private $selectByInstitutionStatement = NULL;
        private $selectBySubjectStatement = NULL;
        private $selectAllStatement = NULL;
        
		private $deleteStatement = NULL;
        
        private $updateDescriptionStatement = NULL;
        private $updateSubjectStatement = NULL;
        private $updateImagesStatement = NULL;
        private $updateScoreStatement = NULL;
        private $updateRepliesStatement = NULL;
        
		
		function __construct($mysqli) {
            // This class requires utils/database.php
            // The input to the constructor is a handle to the sql session. Should be $conn
			if($mysqli->connect_errno > 0){
				die('Unable to connect to database [' . $mysqli->connect_error . ']');
			}
			
			$this->mysqli = $mysqli;
			
            // createStatement creates a new question
			$this->createStatement = $mysqli->prepare("INSERT INTO " . QuestionsConnector::$TABLE_NAME . "(`" . QuestionsConnector::$COLUMN_INSTITUTION . "`, `" . QuestionsConnector::$COLUMN_TITLE . "`, `" . QuestionsConnector::$COLUMN_DESCRIPTION . "`, `" . QuestionsConnector::$COLUMN_SUBJECT . "`, `" . QuestionsConnector::$COLUMN_IMAGES . "`, `" . QuestionsConnector::$COLUMN_ASKER . "`) VALUES(?, ?, ?, ?, ?, ?)");
			
            // selectByIdStatement searches for an question of the input Id. This is faster than selectStatement
            $this->selectByIdStatement = $mysqli->prepare("SELECT * FROM `" . QuestionsConnector::$TABLE_NAME . "` WHERE `" . QuestionsConnector::$COLUMN_ID . "` = ?");
			
            // selectByTitleStatement searches for a question with the input title
            $this->selectByTitleStatement = $mysqli->prepare("SELECT * FROM `" . QuestionsConnector::$TABLE_NAME . "` WHERE `" . QuestionsConnector::$COLUMN_TITLE . "` = ? AND `" . QuestionsConnector::$COLUMN_INSTITUTION . "` = ?");
            
            // selectByInstitutionStatement searches for all the questions from students from institution id
            $this->selectByInstitutionStatement = $mysqli->prepare("SELECT * FROM `" . QuestionsConnector::$TABLE_NAME . "` WHERE `" . QuestionsConnector::$COLUMN_INSTITUTION . "` = ?");
            
            // selectBySubjectStatement searches for all the questions in a subject
            $this->selectBySubjectStatement = $mysqli->prepare("SELECT * FROM `" . QuestionsConnector::$TABLE_NAME . "` WHERE `" . QuestionsConnector::$COLUMN_SUBJECT . "` = ? AND `" . QuestionsConnector::$COLUMN_INSTITUTION . "` = ?");    
            
            // selectAllStatement just grabs every question from the server
            $this->selectAllStatement = $mysqli->prepare("SELECT * FROM `" . QuestionsConnector::$TABLE_NAME . "`");
			
            // deleteStatement, you guess it! Deletes a question of input id
            $this->deleteStatement = $mysqli->prepare("DELETE FROM " . QuestionsConnector::$TABLE_NAME . " WHERE `" . QuestionsConnector::$COLUMN_ID . "` = ?");
		
            // updateDescriptionStatement updates the descripton of the input id
            $this->updateDescriptionStatement = $mysqli->prepare("UPDATE " . QuestionsConnector::$TABLE_NAME . " SET `" . QuestionsConnector::$COLUMN_DESCRIPTION . "` = ? WHERE `" . QuestionsConnector::$COLUMN_ID . "` = ?");
            
            // updateSubjectStatement updates the subject of the input id
            $this->updateSubjectStatement = $mysqli->prepare("UPDATE " . QuestionsConnector::$TABLE_NAME . " SET `" . QuestionsConnector::$COLUMN_SUBJECT . "` = ? WHERE `" . QuestionsConnector::$COLUMN_ID . "` = ?");
            
            // updateImagesStatement updates the images in a question
            $this->updateImagesStatement = $mysqli->prepare("UPDATE " . QuestionsConnector::$TABLE_NAME . " SET `" . QuestionsConnector::$COLUMN_IMAGES . "` = ? WHERE `" . QuestionsConnector::$COLUMN_ID . "` = ?");
            
            // updateScoreStatement updates the score of a question
            $this->updateScoreStatement = $mysqli->prepare("UPDATE " . QuestionsConnector::$TABLE_NAME . " SET `" . QuestionsConnector::$COLUMN_SCORE . "` = ? WHERE `" . QuestionsConnector::$COLUMN_ID . "` = ?");
            
            // updateRepliesStatement updates the replies to a question
            $this->updateRepliesStatement = $mysqli->prepare("UPDATE " . QuestionsConnector::$TABLE_NAME . " SET `" . QuestionsConnector::$COLUMN_REPLIES . "` = ? WHERE `" . QuestionsConnector::$COLUMN_ID . "` = ?");
                
        }
		
		public function create($institution, $title, $description, $subject, $images, $asker) {
            // Create new question using title, description, subject, file name of images and name of the person asking
			if($institution == null || $title == NULL || $subject == NULL || $asker == NULL) return false; // if you didn't enter any title, subject or asker, the method stops
			
			$this->createStatement->bind_param("isssss", $institution, $title, $description, $subject, $images, $asker);
			return $this->createStatement->execute();
		}
        
		public function selectById($id) {
            // Select the question of id
			$this->selectByIdStatement->bind_param("i", $id);
			if(!$this->selectByIdStatement->execute()) return false; // if the query didn't execute, return false

			$result = $this->selectByIdStatement->get_result();
			if(!$result) return false; // if the query didn't give a result, return false
			$question = $result->fetch_assoc();
			
			$this->selectByIdStatement->free_result(); // releases memory
			
			return $question;
		}
        
        public function selectByTitle($title, $institution) {
            // Select a question of title
            $this->selectByTitleStatement->bind_param("si", $title, $institution);
            if(!$this->selectByTitleStatement->execute()) return false; 
            
            $result = $this->selectByTitleStatement->get_result();
            if(!$result) return false;
            $question = $result->fetch_assoc();
            
            $this->selectByTitleStatement->free_result();
            
            return $question;            
        }
        
        public function selectByInstitution($institution) {
            // Select a question of institution
            $this->selectByInstitutionStatement->bind_param("i", $institution);
            if(!$this->selectByInstitutionStatement->execute()) return false; 
            
            $result = $this->selectByInstitutionStatement->get_result();
            if(!$result) return false;
            $resultArray = $result->fetch_all(MYSQLI_ASSOC);
            
            $this->selectByInstitutionStatement->free_result();
    
			return $resultArray;        
        }
        
        public function selectBySubject($subject, $institution) {
            // Select a question of subject and institution
            $this->selectBySubjectStatement->bind_param("si", $subject, $institution);
            if(!$this->selectBySubjectStatement->execute()) return false; 
            
            $result = $this->selectBySubjectStatement->get_result();
            if(!$result) return false;
            $resultArray = $result->fetch_all(MYSQLI_ASSOC);
            
            $this->selectBySubjectStatement->free_result();
    
			return $resultArray;        
        }
        
        
		public function selectAll() {
            // Grab everything from the table
			if(!$this->selectAllStatement->execute()) return false; // if the query didn't execute, return false
			$result = $this->selectAllStatement->get_result(); // frees memory
			$resultArray = $result->fetch_all(MYSQLI_ASSOC);
			return $resultArray;
		}
		
		public function updateDescription($description, $id) {
            // Updates account description of question with id
			$this->updateDescriptionStatement->bind_param("si", $description, $id);
			
			if(!$this->updateDescriptionStatement->execute()) return false; // if the query didn't execute, return false
			return true;
		}
        
        public function updateSubject($subject, $id) {
            // Updates the subject of question with question id
            $this->updateSubjectStatement->bind_param("si", $subject, $id);
            
            if(!$this->updateSubjectStatement->execute()) return false; // if the query didn't execute, return false
			return true;
        }
        
        public function updateImages($images, $id) {
            // Updates the file names of the images for question with id
            $this->updateImagesStatement->bind_param("si", $images, $id);
            
            if(!$this->updateImagesStatement->execute()) return false; // if the query didn't execute, return false
            return true;
        }
        
        public function updateScore($score, $id) {
            // Updates the score of the question with id
            $this->updateScoreStatement->bind_param("ii", $score, $id);
            
            if(!$this->updateScoreStatement->execute()) return false; // if the query didn't execute, return false
            return true;
        }
		
        public function updateReplies($replies, $id) {
            $this->updateRepliesStatement->bind_param("si", $replies, $id);
            
            if(!$this->updateRepliesStatement->execute()) return false; // if the query didn't execute, return false
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
