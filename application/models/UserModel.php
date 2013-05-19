<?php
class UserModel extends CI_Model {

	public function __construct()
	{
		$this->load->database();
	}

	public function getUserById($fbId)
	{	
	// 		$query = "SELECT user_id FROM fb_user_users WHERE fb_id = '".$fbID."'";
 //  	$id = getRow($query);
 //   	return $id;

		$this->db->select('user_id');
		$query = $this->db->get_where('fb_users', array('fb_id' => $fbId));
		return $query->result()->user_id;
	}

	function getUserLocations(){
		$sql = "SELECT concat('u',user_id) as id, lat, lng FROM user_latest_locations";
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0){
			return $query->result();
		}
		return null;
	}

	function getEveryoneLocations(){
		$sql = "SELECT concat('u',user_id) as id, lat, lng FROM user_latest_locations UNION
			SELECT concat('d',tdriver_id) as id, X(location) as lat, Y(location) as lng FROM tdriver_latest_locations";
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0){
			return $query->result();
		}
		return null;
	}

	function createNewUser($initialLocation){

		$sql = "INSERT INTO users(first_name, last_name, email, username) VALUES ('same name','same','same@gmail.com','same')";
		$query = $this->db->query($sql);
		$lastInsertId = $this->db->insert_id();
		$sql = "INSERT INTO user_latest_locations(user_id, lat, lng, reg_date) VALUES (LAST_INSERT_ID(),?,?,NOW())";
		$query = $this->db->query($sql, $initialLocation);
		return $lastInsertId;
	}

	function clearUserLocations(){
		$sql="DELETE FROM users";
		$quer = $this->db->query($sql);
		$sql="DELETE FROM user_latest_locations";
		$quer = $this->db->query($sql);
		return $this->db->affected_rows();
	}


}