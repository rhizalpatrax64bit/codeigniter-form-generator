<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * MY_url_helper
 *
 * @author Simon Emms <simon@simonemms.com>
 */




/**
 * Output Form
 * 
 * Outputs the form HTML
 * 
 * @param array $arrParams
 * @return string
 */
function output_form(array $arrParams = array()) {
    /* Get CI instance */
    $objCI = &get_instance();
    
    $objCI->load->library('form_validation');
    
    return $objCI->form_validation->output_form($arrParams);
}




?>