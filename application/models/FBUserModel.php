<?php

class FBUser_Model extends CI_Model{

	public function __construct(){
		$this->load->database();
	}

	//testing method
	function getFBUserName($fbID){
		$sql = "SELECT username FROM fb_users WHERE fb_id = ?";
		$query = $this->db->query($sql, array($fbID));
		if ($query->num_rows() > 0){
			return $query->row();
		}
		return null;
	}

	function getAppUserId($fbID){
		$sql = "SELECT user_id FROM fb_user_users WHERE fb_id = ?";
		$query = $this->db->query($sql, array($fbID));
		if ($query->num_rows() > 0){
			return $query->row();
		}
   		return null;
	}

	function isFBUserRegistered($fbID){
	    $sql = "SELECT updated_time  FROM fb_users WHERE fb_id = ?";
	    $query = $this->db->query($sql, array($fbID));
	    return $query->num_rows()==1;
	}


	function insertHometownIfNotExists($fbHometown){
		$sql = "SELECT count(hometown_id) FROM fb_hometowns where hometown_id = ?";
		$query = $this->db->query($sql, array($fbHometown['id']));
		if($query->num_rows()==0){
			$insertSQL = "INSERT INTO fb_hometowns (hometown_id, name) VALUES ('?','?')";
			$query = $this->db->query($insertSQL, array($fbHometown['id'], $fbHometown['home']));
			runQuery($insert);
		}
	}

	function DAOFBUser_insertLocationIfNotExists($fbLocation){
		$query = "SELECT count(location_id) FROM fb_locations WHERE location_id = '".$fbLocation['id']."'";
		$n = getRow($query);
		if($n==0){
			$insert = "INSERT INTO fb_locations (location_id, name) VALUES ('".$fbLocation['id']."','".$fbLocation['name']."')";
			runQuery($insert);
		}
	}
	function DAOFBUser_insertFBUserEducationIfNotExists($fb_id, $fbEducation){
		
		$deleteQuery="DELETE FROM fb_user_education where fb_id = '".$fb_id."'";
		runQuery($deleteQuery);
		
		foreach ($fbEducation as $key => $value) {
			DAOFBUser_insertSchoolIfNotExists($value['school']);

			if(isset($value['year'])){
				$year = "'".$value['year']['name']."'";
			}else{
				$year= "null";
			}
			$insertQuery = "INSERT INTO fb_user_education (fb_id, school_id, year, type) VALUES 
				('".$fb_id."',
				'".$value['school']['id']."',
				".$year.",
				'".$value['type']."')";
			runQuery($insertQuery);
		}
		
	}
	function DAOFBUser_insertSchoolIfNotExists($fbSchool){
		$query = "SELECT count(school_id) FROM fb_schools where school_id = '".$fbSchool['id']."'";
		$n = getRow($query);
		if($n==0){
			$insert = "INSERT INTO fb_schools (school_id, name) VALUES ('".$fbSchool['id']."','".$fbSchool['name']."')";
			runQuery($insert);
		}
	}

}
?>