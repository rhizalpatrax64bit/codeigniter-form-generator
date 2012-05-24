<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * MY_Form
 *
 * Form object parent. It extends the form_valdation
 * class
 *
 * @author Simon Emms <simon@simonemms.com>
 * @since 17-Oct-2011
 */
class MY_Form {



    /**
     * __get
     *
     * Allows models to access CI's loaded classes using the same
     * syntax as controllers.
     *
     * @access private
     */
    public function __get($key) {
        $CI =& get_instance();
        return $CI->$key;
    }






    /**
     * Call
     *
     * This call is to give the impression this
     * extends the form_validation class when it
     * doesn't as that creates a new instance
     * of the library
     *
     * @param string $name
     * @param array $arrVars
     * @return mixed
     */
    public function __call($name, $arrVars) {
        return call_user_func_array(array($this->form_validation, $name), $arrVars);
    }




}
?>