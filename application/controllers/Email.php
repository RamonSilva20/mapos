<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Email extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('email');
    }

    public function process()
    {
        if (!$this->input->is_cli_request()) {
            show_404();
        }

        $this->email->send_queue();
    }

    public function retry()
    {
        if (!$this->input->is_cli_request()) {
            show_404();
        }

        $this->email->retry_queue();
    }
}
