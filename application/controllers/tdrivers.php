<?php
class TDrivers extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('TDriverModel');
		$this->load->model('UserModel');
	}

	public function locations()
	{
		$locations = $this->TDriverModel->getTDriverLocations();
		// print_r($locations);
		$contents = $this->output
		                  ->set_content_type('application/json')
		                  ->set_output(json_encode($locations));
		 // echo json_encode($data['tdrivers_locations']);
	}

	public function take_ride(){
		$driverId = $newLocations = $this->input->post('tdriver_id');
		$rideId = $newLocations = $this->input->post('ride_id');
		$actionLat = $newLocations = $this->input->post('lat');
		$actionLng = $newLocations = $this->input->post('lng');
		return $this->TDriverModel->takeRide($driverId, $rideId, $actionLat, $actionLng);
	}

	public function create_tdriver()
	{
		$newLocations = $this->input->post('data');
		
		$newLocationsArray = json_decode($newLocations);
		echo $this->TDriverModel->createNewTDriver($newLocationsArray);

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
		$locationToGo = $this->TDriverModel->updateTDriverLocation($tdriverId, $lat, $lng);
		// print_r($locationToGo);
		if($locationToGo!= null && $this->distance($locationToGo->lat, $locationToGo->lng, $lat, $lng)<=0.02){
			$this->TDriverModel->startRiding($tdriverId, $locationToGo->ride_id, $lat, $lng);
			$contents = $this->output
		                  ->set_content_type('application/json')
		                  ->set_output(json_encode(array()));
		}else{
			$contents = $this->output
		                  ->set_content_type('application/json')
		                  ->set_output(json_encode($locationToGo));
		}
		
	}

	public function clear_locations()
	{
		$ridesDeleted = $this->UserModel->clearRequestPolls();
		$locationsDeleted = $this->TDriverModel->clearTDriversLocations();
		echo $locationsDeleted;
	}



	public function get_closest()
	{
		$selectedPoint = $this->input->post('selectedPoint');
		$selectedPointArray = json_decode($selectedPoint);
		$selectedPointId=$selectedPointArray->selected_id;
		$selectedPointLat=$selectedPointArray->lat;
		$selectedPointLng=$selectedPointArray->lng;
		// print_r($selectedPoint);
		$driverLocations = $this->TDriverModel->getTDriverLocations();
		$N = count($driverLocations);
		$arr = array();
		// print_r($driverLocations);
		for ($i=0; $i < $N; $i++) {
			$location = $driverLocations[$i];
			if($location->id==$selectedPointId)
				continue;
			array_push($arr,array(
					'from'=>$selectedPointId,
					'to'=>$location->id,
					'distance'=>$this->distance($selectedPointLat, $selectedPointLng, $location->lat, $location->lng)
				)
			);
		}
		$contents = $this->output
		                  ->set_content_type('application/json')
		                  ->set_output(json_encode($arr));
		// echo json_encode($arr);
	}

	// not working 
	private function getAngle($lat1, $lon1, $lat2, $lon2){
		$deltaY = $lat1- $lat2;
		$deltaX = $lon2 - $lon1;
		return 360 + atan2($deltaY, $deltaX) * M_PI/180 ;
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