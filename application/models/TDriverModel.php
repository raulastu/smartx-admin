<?php

class TDriverModel extends CI_Model{

	public function __construct(){
		$this->load->database();
	}

	function takeRide($tdriverId, $rideId, $lat, $lng){
		//expire ride for the rest
		$sql = "UPDATE ride_request_polls 
					SET status = 5
					WHERE ride_id = ?";
		$query = $this->db->query($sql,array($rideId));

		//implicitly decline all other ride requests
		$sql = "UPDATE ride_request_polls 
					SET status = 4
					WHERE tdriver_id = ?";
		$query = $this->db->query($sql,array($tdriverId));

		//take ownership of this ride
		$sql = "UPDATE ride_request_polls 
					SET status = 2
					WHERE ride_id = ? AND tdriver_id=?";
		$query = $this->db->query($sql,array($rideId, $tdriverId));

		//set ride status to TDRIVER_COMING
		$sql = "UPDATE taxi_rides
					SET status = 2
					WHERE ride_id = ?";
		$query = $this->db->query($sql,array($rideId));

		$sql = "INSERT INTO ride_events(ride_id, lat, lng, type, tdriver_id, reg_date) 
				VALUES (?,?,?,2,?,NOW())";
		$query = $this->db->query($sql,array($rideId, $lat, $lng, $tdriverId));
		return true;
	}

	function startRiding($tdriverId, $rideId, $lat, $lng){
		$sql = "UPDATE taxi_rides
					SET status = 7
					WHERE ride_id = ?";
		$query = $this->db->query($sql,array($rideId));

		$sql = "INSERT INTO ride_events(ride_id, lat, lng, type, tdriver_id, reg_date) 
				VALUES (?,?,?,7,?,NOW())";
		$query = $this->db->query($sql, array($rideId, $lat, $lng, $tdriverId));

		return $this->db->affected_rows();
	}

	function getTDriverLocations(){
		$sql = "SELECT tdriver_id as id, X(location) as lat, Y(location) as lng FROM tdriver_latest_locations";
		$query = $this->db->query($sql);
		return $query->result();
	}

	function updateTDriverLocation($tdriverId, $lat, $lng){

		$sql = "INSERT INTO tdriver_location_histories (tdriver_id, lat, lng, reg_date) VALUES (?,?,?,NOW())";
		$query = $this->db->query($sql,array($tdriverId, $lat, $lng));

		$sql = "UPDATE tdriver_latest_locations SET location = POINT(?,?), reg_date = NOW() WHERE tdriver_id = ?";
		$query = $this->db->query($sql, array($lat, $lng, $tdriverId));

		// return current location to pick up user, if any (used for testing)
		$sql = "SELECT rrp.ride_id, ua.lat, ua.lng 
				FROM ride_request_polls rrp JOIN taxi_rides tr using(ride_id) 
					JOIN user_addresses ua on(tr.origin_address_id = ua.address_id) 
				WHERE tdriver_id = ? AND rrp.status = 2";
		$query = $this->db->query($sql, array($tdriverId));
		return $res = $query->row();
	}

	function createNewTDriver($initialLocation){

		$sql = "INSERT INTO tdrivers(name) VALUES ('same name')";
		$query = $this->db->query($sql);
		$lastInsertId = $this->db->insert_id();
		$sql = "INSERT INTO tdriver_latest_locations(tdriver_id, location, reg_date) VALUES (LAST_INSERT_ID(),POINT(?,?),NOW())";
		$query = $this->db->query($sql, $initialLocation);
		return $lastInsertId;
	}

	// function insertNewTDriversLocations($locationArray){
	// 	if(count($locationArray)==0){
	// 		return false;
	// 	}
	// 	$sql = "INSERT INTO tdrivers(name) VALUES ('same name')";
	// 	$query = $this->db->query($sql);

	// 	$sql = "INSERT INTO tdriver_latest_locations(tdriver_id, location, last_seen) VALUES ";
	// 	$N = count($locationArray)/2;
	// 	for($i=0; $i<$N; $i++){
	// 		$sql.="(LAST_INSERT_ID(),POINT(?,?),NOW())";
	// 		if($i<$N-1){
	// 			$sql.=',';
	// 		} 
	// 	}
	// 	// echo $N."--".$sql;
	// 	$query = $this->db->query($sql, $locationArray);
	// 	return $this->db->affected_rows();
	// }

	function clearTDriversLocations(){
		$sql="DELETE FROM tdrivers";
		$quer = $this->db->query($sql);
		$sql="DELETE FROM tdriver_latest_locations";
		$quer = $this->db->query($sql);
		return $this->db->affected_rows();
	}

}
?>