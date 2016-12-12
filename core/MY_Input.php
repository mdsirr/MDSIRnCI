<?php
/**
 * Description of MY_Input
 *
 * @author MDSIR
 */
class MY_Input extends CI_Input{
    public function __construct() {
        parent::__construct();
    }
    
    public function post_without($index = 'generateid', $xss_clean = TRUE) {
        $without_index = is_array($index) ? $index : explode(',', str_replace(' ', '', $index));
        return array_diff_key(parent::post(NULL, $xss_clean), array_flip($without_index));
    }
}
