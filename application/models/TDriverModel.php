<?php

class TDriverModel extends CI_Model{

	public function __construct(){
		$this->load->database();
	}


	function getTDriversLocations(){
		$sql = "SELECT tdriver_id, X(location) as latitude, Y(location) as longitude FROM tdriver_latest_locations";
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0){
			return $query->result();
		}
		return null;
	}

	function updateTDriverLocation($tdriverId, $lat, $lng){

		$sql = "INSERT INTO tdriver_location_histories (tdriver_id, lat, lng, NOW()) VALUES (?,?,?)";
		$query = $this->db->query($array($tdriverId, $lat, $lng));

		$sql = "UPDATE tdriver_latest_locations SET location = POINT(?,?) WHERE tdriver_id = ?";
		$query = $this->db->query($array($tdriverId, $lat, $lng));
		return true;
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