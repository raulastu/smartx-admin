<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class FBLogin extends CI_Controller{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('FBUserModel');

        $CI = & get_instance();
        $CI->config->load("facebook",TRUE);
        $config = $CI->config->item('facebook');
        $this->load->library('Facebook', $config);

	}

	public function dologin(){

		$fb_user_id = $this->facebook->getUser();
		
		// print_r($fb_user_id);
		if($fb_user_id) {

            try {
            	$fql = 'SELECT username, first_name, last_name, pic_small from user where uid = ' . $fb_user_id;
		    	$ret_obj = $this->facebook->api(array(
		                                   'method' => 'fql.query',
		                                   'query' => $fql,
		                                 ));
		    	$fbUserName = $ret_obj[0]['username'];
		    	$fbPicSmallURL = $ret_obj[0]['pic_small'];
		    	$fbFirstName = $ret_obj[0]['first_name'];
		    	$fbLastName = $ret_obj[0]['last_name'];

		    	$userId = $this->FBUserModel->getFBUserName('123123');
		    	echo print_r($userId);
		    	if(!$userId){
		    		
		    	}

                $user_info = $this->facebook->api('/me');
                echo '<pre>'.htmlspecialchars(print_r($user_info, true)).'</pre>';
            } catch(FacebookApiException $e) {
                echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
                $fb_user_id = null;
            }
        } else {
            echo "<a href=\"{$this->facebook->getLoginUrl()}\">Login using Facebook</a>";
        }
	}
}
?>