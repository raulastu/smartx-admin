<?php

class Pages extends CI_Controller {

	public function view($page = 'smartx_home')
	{
		if (file_exists('application/views/pages/'.$page.'.php')){
			// Whoops, we don't have a page for that!

			$data['title'] = ucfirst($page); // Capitalize the first letter
		
			// $this->load->view('templates/header', $data);
			$this->load->view('pages/'.$page, $data);
			// $this->load->view('templates/footer', $data);
			// show_404();
		}else{
			$this->load->view('pages/'.$page, $data);
		}
	}
}

?>