<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MY_Loader
 *
 * @author Simon Emms <simon@simonemms.com>
 */
class MY_Loader extends CI_Loader {






    var $_ci_forms = array();
    var $_ci_form_paths	= array();







    /**
     * Construct
     *
     * Extends construct to allow form
     * paths
     */
    public function __construct() {
        parent::__construct();

        /* Add the form paths */
        $this->_ci_form_paths = array(APPPATH);
    }







    /**
     * Add Package Path
     *
     * Extends package path to allow the form
     * paths
     *
     * @param string $path
     */
    public function  add_package_path($path) {
        $path = rtrim($path, '/').'/';

        array_unshift($this->_ci_form_paths, $path);

        parent::add_package_path($path);
    }







    /**
     * Form
     *
     * Loads the form object
     * 
     * @param string $form
     * @param string $name
     * @return null
     */
    public function form($form, $name = '') {

        /* Load the form validation class */
        $objCI = &get_instance();
        $objCI->load->library('form_validation');

        if (is_array($form)) {
            foreach ($form as $babe) {
                $this->form($babe);
            }
            return;
        }

        if ($form == '') {
            return;
        }

        $path = '';

        // Is the form in a sub-folder? If so, parse out the filename and path.
        if (($last_slash = strrpos($form, '/')) !== FALSE) {
            // The path is in front of the last slash
            $path = substr($form, 0, $last_slash + 1);

            // And the form name behind it
            $form = substr($form, $last_slash + 1);
        }

        if ($name == '') {
            $name = $form;
        }

        if (in_array($name, $this->_ci_forms, TRUE)) {
            return;
        }

        $name = $form.'_form';
        $class = ucfirst($name);

        $CI =& get_instance();
        if (isset($CI->$class)) {
            show_error('The form name you are loading is the name of a resource that is already being used: '.$name);
        }

        foreach ($this->_ci_form_paths as $form_path) {

            if ( ! file_exists($form_path.'forms/'.$path.$form.EXT)) {
                continue;
            }

            if ( ! class_exists('MY_Form')) {
                load_class('Form', 'core');
            }
            
            require_once($form_path.'forms/'.$path.$form.EXT);

            $CI->$name = new $class();

            /* Run the autoload */
            if(method_exists($CI->$name, '__load')) { $CI->$name->__load(); }

            $this->_ci_forms[] = $class;

            return;

        }

        // couldn't find the form
        show_error('Unable to locate the form you have specified: '.$form);

    }
    
    
    
    
}

?>