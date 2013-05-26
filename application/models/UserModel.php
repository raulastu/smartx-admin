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

	function clearRequestPolls(){
		$sql = "DELETE FROM ride_request_polls";
		$query = $this->db->query($sql);
		$sql = "DELETE FROM taxi_rides";
		$query = $this->db->query($sql);
		return $this->db->affected_rows();
	}

	function getUserLocations(){
		$sql = "SELECT user_id as id, lat, lng FROM user_latest_locations";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function getRideRequestPolls(){
		$sql = "SELECT rrp.ride_id , tr.user_id, rrp.tdriver_id, rrp.status , ua.lat as pickup_lat, ua.lng as pickup_lng
			FROM ride_request_polls rrp JOIN taxi_rides tr using(ride_id) JOIN user_addresses ua on(tr.origin_address_id=ua.address_id) WHERE rrp.status IN (1,2)";
		$query = $this->db->query($sql);
		return $query->result();
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

		$origin = $this->getLatLngFromUserAddress($originAddressId);
		// print_r($origin);
		$sql = "INSERT INTO  ride_events(ride_id, lat, lng, type, reg_date)
			VALUES (?,?,?, 1,NOW())";
		$query = $this->db->query($sql,array($lastInsertId, $origin->lat, $origin->lng));
		return (object)array('rideId'=>$lastInsertId,'lat'=>$origin->lat, 'lng'=>$origin->lng);
	}

	function updateUserLocation($userId, $lat, $lng){

		$sql = "INSERT INTO user_location_histories (user_id, lat, lng, reg_date) VALUES (?,?,?,NOW())";
		$query = $this->db->query($sql,array($userId, $lat, $lng));

		$sql = "UPDATE user_latest_locations SET lat =?, lng=?, reg_date = NOW() WHERE user_id = ?";
		$query = $this->db->query($sql, array($lat, $lng, $userId));
		return $this->db->affected_rows();
	}

	function create_initial_request_poll($rideId, $tdriverIdArray){
		$N = count($tdriverIdArray);
		if($N==0) return;

		$sql = "INSERT INTO ride_request_polls(ride_id, tdriver_id, status, reg_date)
			VALUES";
		
		for($i=0;$i<$N;$i++){
			$sql.=" (".$rideId.", ?, 1, NOW())";
			if($i<$N-1){
				$sql.=',';	
			}
		}
		$query = $this->db->query($sql,$tdriverIdArray);

	}

	function getLatLngFromUserAddress($userAddressId){
		$sql = "SELECT creator_user_id, lat, lng FROM user_addresses WHERE address_id = ?";
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
	
	function login($userName){
		$sql="SELECT user_id FROM users WHERE username = ?";
		$query = $this->db->query($sql, array($userName));
		return $query->row();
	}


}