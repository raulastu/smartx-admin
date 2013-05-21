<?php
class Users extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('UserModel');
		$this->load->model('TDriverModel');
	}

	public function everyone_locations()
	{
		$users = $this->UserModel->getUserLocations();
		$tdrivers = $this->TDriverModel->getTDriverLocations();
		$rideRequestPolls = $this->UserModel->getRideRequestPolls();
		$tempArr=array();
		$res = (object)array("users"=>$users, "tdrivers"=>$tdrivers, "rides"=>$rideRequestPolls);
		// print_r($locations);
		$contents = $this->output
		                  ->set_content_type('application/json')
		                  ->set_output(json_encode($res));
		 // echo json_encode($data['tdrivers_locations']);
	}
	public function locations()
	{
		$locations = $this->UserModel->getUserLocations();
		// print_r($locations);
		$contents = $this->output
		                  ->set_content_type('application/json')
		                  ->set_output(json_encode($locations));
		 // echo json_encode($data['tdrivers_locations']);
	}

	public function create_user()
	{
		$newLocations = $this->input->post('data');
		
		$newLocationsArray = json_decode($newLocations);
		// print_r($newLocationsArray);
		echo "u".$this->UserModel->createNewUser($newLocationsArray);
	}

	public function start_ride(){
		$userId = $this->input->post('u');
		$userAddressId = $this->input->post('i');
		if($userAddressId!=null){
			$address = $this->input->post('a');

			if($address==null){
				//Use existing address
				// Do nothing
			}else{
				//Modify existing address
				$reference = $this->input->post('r');
				$Lat = $this->input->post('la');
				$Lng = $this->input->post('ln');
				$userAddressId = $this->UserModel->updateUserAddress($userAddressId, $address, $reference, $lat, $lng);
			}
		}else{
			//Brand new address
			$address = $this->input->post('a');
			$reference = $this->input->post('r');
			$lat = $this->input->post('la');
			$lng = $this->input->post('ln');
			$userAddressId = $this->UserModel->createUserAddress($address, $reference, $lat, $lng, $userId);
		}
		
		// print_r($newLocationsArray);
		$rideInfo = $this->UserModel->startRide($userId, $userAddressId);

		$locations = $this->TDriverModel->getTDriverLocations();
		$N = count($locations);
		$closestTDriverIds = array();
		// print_r($locations);
		for ($i=0; $i < $N; $i++) {
			$location = $locations[$i];
			$distance = $this->distance($rideInfo->lat, $rideInfo->lng, $location->lat, $location->lng);
			if($distance<2.0){ // in km
				array_push($closestTDriverIds, $location->id);
			}
		}
		$this->UserModel->create_initial_request_poll($rideInfo->rideId,$closestTDriverIds);
		//Send Push Notifications to drivers

		$res= array("rideId"=>$rideInfo->rideId, "assigned_tdrivers"=>$closestTDriverIds);
		$contents = $this->output
	              ->set_content_type('application/json')
	              ->set_output(json_encode($res));
	}

	public function clear_rides_requests(){
		$this->UserModel->clearRequestPolls();
		echo " rides deleted";
	}

	public function update_driver_location()
	{
		$data = $this->input->post('data');
		$data = explode(':',$data);
		// print_r($data);
		$tdriverId = $data[0];
		$lat = $data[1];
		$lng = $data[2];

		// $newLocationsArray = json_decode($newLocations);
		echo $this->TDriverModel->updateTDriverLocation($tdriverId, $lat, $lng);
	}

	public function clear_locations()
	{
		echo $this->UserModel->clearUserLocations();
	}

	public function get_closest()
	{
		$selectedPoint = $this->input->post('selectedPoint');
		$selectedPointArray = json_decode($selectedPoint);
		$selectedPointId=$selectedPointArray->tdriver_id;
		$selectedPointLat=$selectedPointArray->lat;
		$selectedPointLng=$selectedPointArray->lng;
		// print_r($selectedPoint);
		$locations = $this->TDriverModel->getTDriverLocations();
		$N = count($locations);
		$arr = array();
		// print_r($locations);
		for ($i=0; $i < $N; $i++) {
			$location = $locations[$i];
			if($location->tdriver_id==$selectedPointId)
				continue;
			array_push($arr,array(
					'from'=>intval($selectedPointId),
					'to'=>$location->tdriver_id,
					'distance'=>$this->distance($selectedPointLat, $selectedPointLng, $location->lat, $location->lng)
				)
			);
		}
		$contents = $this->output
		                  ->set_content_type('application/json')
		                  ->set_output(json_encode($arr), JSON_NUMERIC_CHECK);
		// echo json_encode($arr);
	}

	private function distance($lat1, $lon1, $lat2, $lon2) {
		$R = 6371; // km
		$dLat = $this->toRad($lat2 - $lat1);
		$dLon = $this->toRad($lon2 - $lon1);
		$lat1 = $this->toRad($lat1);
		$lat2 = $this->toRad($lat2);

		$a = sin($dLat / 2.0) * sin($dLat / 2.0)
				+ sin($dLon / 2.0) * sin($dLon / 2.0) * cos($lat1)
				* cos($lat2);
		$c = 2 * atan2(sqrt($a), sqrt(1.0 - $a));
		$d = $R * $c;
		return $d;
	}

	private function toRad($val) {
		return $val *  M_PI/ 180;
	}



	public function index()
	{
		echo "ola k ase";
	}
}
?>