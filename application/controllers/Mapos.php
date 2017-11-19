<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Mapos extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in())
		{
			// redirect them to the login page
			redirect('auth/login', 'refresh');
		}
	}

	public function index()
	{
		$this->template->build('mapos/dashboard');
	}

}

/* End of file App.php */
/* Location: ./application/controllers/App.php */