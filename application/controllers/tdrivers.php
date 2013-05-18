<?php
class TDrivers extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('TDriverModel');
	}

	public function locations()
	{
		$locations = $this->TDriverModel->getTDriversLocations();

		$contents = $this->output
		                  ->set_content_type('application/json')
		                  ->set_output(json_encode($locations));
		 // echo json_encode($data['tdrivers_locations']);
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
		$data = explode('|',$data);
		$tdriverId = $data[0];
		$lat = $data[1];
		$lng = $data[2];

		$newLocationsArray = json_decode($newLocations);
		echo $this->TDriverModel->updateTDriverLocation($newLocationsArray, $lat, $lng);
	}

	public function clear_locations()
	{
		$this->TDriverModel->clearTDriversLocations($newLocationsArray);
	}



	public function get_closest()
	{
		$selectedPoint = $this->input->post('selectedPoint');
		$selectedPointArray = json_decode($selectedPoint);
		$selectedPointId=$selectedPointArray->tdriver_id;
		$selectedPointLat=$selectedPointArray->lat;
		$selectedPointLng=$selectedPointArray->lng;
		// print_r($selectedPoint);
		$locations = $this->TDriverModel->getTDriversLocations();
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
					'distance'=>$this->distance($selectedPointLat, $selectedPointLng, $location->latitude, $location->longitude),
					'angle'=>$this->getAngle($selectedPointLat, $selectedPointLng, $location->latitude, $location->longitude)
				)
			);
		}
		$contents = $this->output
		                  ->set_content_type('application/json')
		                  ->set_output(json_encode($arr));
		// echo json_encode($arr);
	}

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