<?php
	class RepliesConnector {
		private $mysqli = NULL;
		
		public static $TABLE_NAME = "replies";
		public static $COLUMN_ID = "id";
		public static $COLUMN_DESCRIPTION = "description";
		public static $COLUMN_IMAGES = "images";
		public static $COLUMN_SCORE = "score";
		public static $COLUMN_REPLIER = "replier";
		public static $COLUMN_REPLY_DATE = "replyDate";
		public static $COLUMN_ACCEPTED = "accepted";
        public static $COLUMN_COMMENTS = "comments";
		
        // The prepare statements exist to prevent SQL injection
		private $createStatement = NULL;
        
		private $selectByIdStatement = NULL;
        private $selectAllStatement = NULL;
        
		private $deleteStatement = NULL;
        
        private $updateDescriptionStatement = NULL;
        private $updateImagesStatement = NULL;
        private $updateScoreStatement = NULL;
        private $updateAcceptedStatement = NULL;
        private $updateCommentsStatement = NULL;
		
		function __construct($mysqli) {
            // This class requires utils/database.php
            // The input to the constructor is a handle to the sql session. Should be $conn
			if($mysqli->connect_errno > 0){
				die('Unable to connect to database [' . $mysqli->connect_error . ']');
			}
			
			$this->mysqli = $mysqli;
			
            // createStatement creates a new question
			$this->createStatement = $mysqli->prepare("INSERT INTO " . RepliesConnector::$TABLE_NAME . "(`" . RepliesConnector::$COLUMN_DESCRIPTION . "`, `" . RepliesConnector::$COLUMN_IMAGES . "`, `" . RepliesConnector::$COLUMN_REPLIER . "`) VALUES(?, ?, ?)");
			
            // selectByIdStatement searches for an question of the input Id. This is faster than selectStatement
            $this->selectByIdStatement = $mysqli->prepare("SELECT * FROM `" . RepliesConnector::$TABLE_NAME . "` WHERE `" . RepliesConnector::$COLUMN_ID . "` = ?");
            
            // selectAllStatement just grabs every question from the server
            $this->selectAllStatement = $mysqli->prepare("SELECT * FROM `" . RepliesConnector::$TABLE_NAME . "`");
			
            // deleteStatement, you guess it! Deletes a question of input id
            $this->deleteStatement = $mysqli->prepare("DELETE FROM " . RepliesConnector::$TABLE_NAME . " WHERE `" . RepliesConnector::$COLUMN_ID . "` = ?");
		
            // updateDescriptionStatement updates the descripton of the input id
            $this->updateDescriptionStatement = $mysqli->prepare("UPDATE " . RepliesConnector::$TABLE_NAME . " SET `" . RepliesConnector::$COLUMN_DESCRIPTION . "` = ? WHERE `" . RepliesConnector::$COLUMN_ID . "` = ?");
            
            // updateImagesStatement updates the images in an answer
            $this->updateImagesStatement = $mysqli->prepare("UPDATE " . RepliesConnector::$TABLE_NAME . " SET `" . RepliesConnector::$COLUMN_IMAGES . "` = ? WHERE `" . RepliesConnector::$COLUMN_ID . "` = ?");
            
            // updateScoreStatement updates the score of an answer
            $this->updateScoreStatement = $mysqli->prepare("UPDATE " . RepliesConnector::$TABLE_NAME . " SET `" . RepliesConnector::$COLUMN_SCORE . "` = ? WHERE `" . RepliesConnector::$COLUMN_ID . "` = ?");
            
            // updateAcceptedStatement updates the acceptance status of an answer
            $this->updateAcceptedStatement = $mysqli->prepare("UPDATE " . RepliesConnector::$TABLE_NAME . " SET `" . RepliesConnector::$COLUMN_ACCEPTED . "` = ? WHERE `" . RepliesConnector::$COLUMN_ID . "` = ?");
            
            // updateCommentsStatement updates the comments to an answer
            $this->updateCommentsStatement = $mysqli->prepare("UPDATE " . RepliesConnector::$TABLE_NAME . " SET `" . RepliesConnector::$COLUMN_COMMENTS . "` = ? WHERE `" . RepliesConnector::$COLUMN_ID . "` = ?");
                
        }
		
		public function create($description, $images, $replier) {
            // Create new reply using description, file name of images and name of the person replying
			if($description == NULL || $replier == NULL) return false; // if you didn't enter any title, subject or asker, the method stops
			
			$this->createStatement->bind_param("sss", $description, $images, $replier);
			if(!$this->createStatement->execute()) return false;
			
			return $this->mysqli->insert_id;
		}
        
		public function selectById($id) {
            // Select the reply of id
			$this->selectByIdStatement->bind_param("i", $id);
			if(!$this->selectByIdStatement->execute()) return false; // if the query didn't execute, return false

			$result = $this->selectByIdStatement->get_result();
			if(!$result) return false; // if the query didn't give a result, return false
			$question = $result->fetch_assoc();
			
			$this->selectByIdStatement->free_result(); // releases memory
			
			return $question;
		}
            
		public function selectAll() {
            // Grab everything from the table
			if(!$this->selectAllStatement->execute()) return false; // if the query didn't execute, return false
			$result = $this->selectAllStatement->get_result(); // frees memory
			$resultArray = $result->fetch_all(MYSQLI_ASSOC);
			return $resultArray;
		}
		
		public function updateDescription($description, $id) {
            // Updates account description of reply with id
			$this->updateDescriptionStatement->bind_param("si", $description, $id);
			
			if(!$this->updateDescriptionStatement->execute()) return false; // if the query didn't execute, return false
			return true;
		}
        
        public function updateImages($images, $id) {
            // Updates the file names of the images for reply with id
            $this->updateImagesStatement->bind_param("si", $images, $id);
            
            if(!$this->updateImagesStatement->execute()) return false; // if the query didn't execute, return false
            return true;
        }
        
        public function updateScore($score, $id) {
            // Updates the score of the reply with id
            $this->updateScoreStatement->bind_param("ii", $score, $id);
            
            if(!$this->updateScoreStatement->execute()) return false; // if the query didn't execute, return false
            return true;
        }
		
        public function updateAccepted($accepted, $id) {
            // Updates the accepted status of the reply with id
            $this->updateAcceptedStatement->bind_param("ii", $accepted, $id);
            
            if(!$this->updateAcceptedStatement->execute()) return false; // if the query didn't execute, return false
            return true;
        }
		
        public function updateComments($comments, $id) {
            // Updates the comments for reply with id
            $this->updateCommentsStatement->bind_param("si", $replies, $id);
            
            if(!$this->updateCommentsStatement->execute()) return false; // if the query didn't execute, return false
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
