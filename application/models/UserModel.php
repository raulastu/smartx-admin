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

	function getUserRide($userId){
		$sql = "SELECT a.lat, a.lng, t.request_date FROM taxi_rides t JOIN user_addresses a using(user_id) 
		WHERE user_id = ? AND ";
		$query = $this->db->query($sql,array($userId));
		return $query->result();
	}

	function startRide($userId, $originAddressId){
		$sql = "INSERT INTO  taxi_rides(user_id, origin_address_id, request_date, status)
			VALUES (?,?, NOW(), 1)";
		$query = $this->db->query($sql,array($userId, $originAddressId));
		$lastInsertId = $this->db->insert_id();

		$LatLng = $this->getLatLngFromUserAddress($originAddressId);
		// print_r($LatLng);
		$sql = "INSERT INTO  ride_events(ride_id, lat, lng, type, reg_date)
			VALUES (?,?,?, 1,NOW())";
		$query = $this->db->query($sql,array($lastInsertId, $LatLng->lat, $LatLng->lng));
		return $lastInsertId;
	}

	function getLatLngFromUserAddress($userAddressId){
		$sql = "SELECT lat, lng FROM user_addresses WHERE address_id = ?";
		$query = $this->db->query($sql,array($userAddressId));
		return $query->row();
	}
	//
	function getUserAddress($addressId){
		$sql = "SELECT address, reference, lat, lng, FROM user_addresses
		WHERE address_id = ?";
		$query = $this->db->query($addressId);
		return $query->result();
	}
	function createUserAddress($address, $reference, $lat, $lng, $userId){
		$sql = "INSERT INTO  user_addresses(address, reference, lat, lng, reg_date, creator_user_id)
			VALUES (?,?,?,?, NOW(),?)";
		$query = $this->db->query($sql,array($address, $reference, $lat, $lng, $userId));
		return $lastInsertId = $this->db->insert_id();;
	}

	function updateUserAddress($addressId, $address, $reference, $lat, $lng){
		$sql = "UPDATE  user_addresses
		SET address = ?
		reference = ?,
		lat = ?,
		lng = ?,
		reg_date = NOW()
		WHERE address_id = ?";
		$query = $this->db->query(array($address, $reference, $lat, $lng, $addressId));
		return $addressId;
	}


}