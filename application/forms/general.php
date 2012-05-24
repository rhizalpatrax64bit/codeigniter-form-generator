<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * general
 *
 * @author Simon Emms <simon@simonemms.com>
 */
class general_form extends MY_Form {



    
    
    /**
     * Login
     * 
     * Gets the site login
     * 
     * @return array
     */
    public function login() {
        
        /* Create the form */
        $arrForm = array(
            array(
                'name' => 'email_address',
                'type' => 'form_input',
                'label' => $this->lang->line('form_emailaddress'),
                'rules' => 'required|valid_email',
            ),
            array(
                'name' => 'password',
                'type' => 'form_password',
                'label' => $this->lang->line('form_password'),
                'rules' => 'required',
            ),
            array(
                'name' => 'save_login',
                'type' => 'form_checkbox',
                'label' => $this->lang->line('form_save_login'),
                'value' => '1',
            ),
            array(
                'name' => 'captcha',
                'type' => 'form_captcha',
                'label' => $this->lang->line('form_captcha'),
            ),
            array(
                'name' => 'forgotten_password',
                'type' => 'form_plain',
                'value' => '<a href="'.site_url('/forgotten-password').'">'.$this->lang->line('form_forgotten_password').'</a>',
            ),
        );

        /* Set the form */
        $this->set_fields($arrForm);

        /* Set the buttons */
        $this->add_button('submit', $this->lang->line('action_menu_login'));

        /* Activate the form */
        if($this->activate()) {

            /**
             * This is where any validation rules that
             * are not part of the default CI things - in
             * this example, this would be where you validate
             * the login credentials in the database.
             */
            
            /* Get the form data */
            $arrData = $this->get_details();
            
            /**
             * Example of customer error.  This would
             * be a validation test in the users model
             */
            $this->set_error('valid_login', $this->lang->line('error_login'));
            
            /* Validate the form */
            if($this->run()) {

                /**
                 * This is where you would put what happens
                 * AFTER we have validated the form. Don't
                 * forget to redirect to remove the POST
                 * headers
                 */
                
                /**
                 * In this example, we'd access the same method in the model
                 * and then redirect to the login page.
                 */
                redirect(current_url());
            }
        }

        /* Get the form for display */
        return $this->get_fields();
        
    }
    


}
?>