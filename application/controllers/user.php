<?php
class User extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
	}

	public function index()
	{
		$data['fb_user'] = $this->user_model->get_user('123123');
		$data['title'] = 'User profile';

		$this->load->view('templates/header', $data);
		$this->load->view('user/index', $data);
		$this->load->view('templates/footer');
	}

	public function view($slug)
	{
		$data['fb_user'] = $this->news_model->get_news($slug);
	}
}
?>