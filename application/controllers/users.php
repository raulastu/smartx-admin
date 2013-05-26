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

		// print_r($rideRequestPolls);
		// return;
		//BEGIN AGGREGATING RIDES 
		$drivers = array();
		$rideIdWhichStatusIsOne=null;
		$rideObject=null;
		$aggregatedRides = array();
		for($i = 0 ; $i<count($rideRequestPolls); $i++){
			if($rideRequestPolls[$i]->status == 1){
				if($rideIdWhichStatusIsOne==null){
					$rideObject = $rideRequestPolls[$i];
					$rideIdWhichStatusIsOne = $rideRequestPolls[$i]->ride_id;
					$rideObject->assigned_tdrivers=array();
					array_push($rideObject->assigned_tdrivers, (object)array("tdriver_id"=>$rideRequestPolls[$i]->tdriver_id));
					unset($rideObject->tdriver_id);
				}else{
					//from one state-1 ride to another state-1 ride
					if($rideIdWhichStatusIsOne!=$rideRequestPolls[$i]->ride_id){
						array_push($aggregatedRides, $rideObject);

						$rideObject = $rideRequestPolls[$i];
						$rideIdWhichStatusIsOne = $rideRequestPolls[$i]->tdriver_id;
						$rideObject->assigned_tdrivers=array();
						array_push($rideObject->assigned_tdrivers, (object)array("tdriver_id"=>$rideRequestPolls[$i]->tdriver_id));
						unset($rideObject->tdriver_id);
					}else{
						array_push($rideObject->assigned_tdrivers, (object)array("tdriver_id"=>$rideRequestPolls[$i]->tdriver_id));
					}
				}
			}else{
				//from state-1 to non state-1 ride
				if($rideIdWhichStatusIsOne!=null){
					array_push($aggregatedRides, $rideObject);
					$rideIdWhichStatusIsOne=null;
					$rideObject=null;
				}
				$rideRequestPolls[$i]->assigned_tdrivers=array((object)array("tdriver_id"=>$rideRequestPolls[$i]->tdriver_id));
				unset($rideRequestPolls[$i]->tdriver_id);
				array_push($aggregatedRides, $rideRequestPolls[$i]);
			}
		}
		if($rideIdWhichStatusIsOne!=null){
			array_push($aggregatedRides, $rideObject);
			$rideIdWhichStatusIsOne=null;
			$rideObject=null;
		}
		
		//END AGGREGATING RIDES 

		$res = (object)array("users"=>$users, "tdrivers"=>$tdrivers, "rides"=>$aggregatedRides);
		// print_r($locations);
		$contents = $this->output
		                  ->set_content_type('application/json')
		                  ->set_output(json_encode($res));
		 // echo json_encode($data['tdrivers_locations']);
	}

	public function update_location()
	{
		$data = $this->input->post('data');
		$data = explode(':',$data);
		// print_r($data);
		$userId = $data[0];
		$lat = $data[1];
		$lng = $data[2];


		$affectedRows = $this->UserModel->updateUserLocation($userId, $lat, $lng);
		// print_r($locations);
		$contents = $this->output
		                  ->set_content_type('application/json')
		                  ->set_output(json_encode($affectedRows));
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
		echo $this->UserModel->createNewUser($newLocationsArray);
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
			if($distance<3.0){ // in km
				array_push($closestTDriverIds, $location->id);
			}
		}
		$this->UserModel->create_initial_request_poll($rideInfo->rideId,$closestTDriverIds);

		//...Send Push Notifications to drivers here

		$res= array("rideId"=>$rideInfo->rideId, "assigned_tdrivers"=>$closestTDriverIds, "pickup_location"=>array("lat"=>$rideInfo->lat, "lng"=>$rideInfo->lng));
		$contents = $this->output
	              ->set_content_type('application/json')
	              ->set_output(json_encode($res));
	}

	public function clear_rides_requests(){
		$ridesDeleted = $this->UserModel->clearRequestPolls();
		echo $ridesDeleted." rides deleted";
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



	public function fakelogin($username)
	{
		// $userName = $this->input->get('username');
		$userId = $this->UserModel->login($username);
		$contents = $this->output
                  ->set_content_type('application/json')
                  ->set_output(json_encode((object)($userId)));
	}

	public function update_user_location()
	{
		$userId = $this->input->post('userId');
		$lat = $this->input->post('lat');
		$lng = $this->input->post('lng');
		// $newLocationsArray = json_decode($newLocations);
		$this->UserModel->updateUserLocation($userId, $lat, $lng);
		echo "updated succesfully";
	}
}
?>