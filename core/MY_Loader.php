<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class MY_Loader extends CI_Loader{
	
    function __construct()
    {
        parent::__construct();
    }
        
    public function view_form($view, $vars = array(), $return = FALSE) 
    {
        $this->helper('form');
        return $this->view($view, $vars, $return);
    }

}
