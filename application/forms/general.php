<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * general
 *
 * @author Simon Emms <simon@simonemms.com>
 */
class general_form extends MY_Form {
    
    
    
    
    
    /**
     * Forgotten Password
     * 
     * Reminds users of their password
     * 
     * @return array
     */
    public function forgotten_password() {
        
        /* Load stuff */
        $this->load->model('users');
        
        /* Create the form */
        $arrForm = array(
            array(
                'name' => 'email_address',
                'type' => 'form_input',
                'label' => $this->lang->line('form_emailaddress'),
                'rules' => 'required|valid_email',
            ),
        );

        /* Set the form */
        $this->set_fields($arrForm);

        /* Set the buttons */
        $this->add_button('submit', $this->lang->line('action_menu_submit'));

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
            
            /* Get the user for error checking */
            $arrUser = $this->users->is_email($arrData['email_address']);
            if($arrUser === false) {
                /* Non-existent user */
                $this->set_error('email_error', $this->lang->line('error_invalid_user_email'));
            } elseif($arrUser['active'] == '0') {
                /* Inactive user */
                $this->set_error('email_error', $this->lang->line('error_inactive_user_email'));
            }
            
            /* Validate the form */
            if($this->run()) {

                /**
                 * This is where you would put what happens
                 * AFTER we have validated the form. Don't
                 * forget to redirect to remove the POST
                 * headers
                 */
                
                /* Load stuff */
                $this->load->library('email');
                $this->load->model('abbreviation');
                
                /* Get the reset URL */
                $code = $this->abbreviation->shrink(array(
                    $this->users->get_rowId() => $arrUser[$this->users->get_rowId()],
                    'email_address' => $arrUser['email_address'],
                    'password' => $arrUser['password'],
                ));
                
                $reset_url = reset_password_url($code);
                
                $this->email->send_email(
                    $arrUser[$this->users->get_rowId()],
                    'forgotten_password_subject',
                    'home/forgotten_password',
                    array(
                        'reset_url' => $reset_url,
                    )
                );
                
                $this->session->set_flashdata($this->config->item('session_login_msg'), $this->lang->line('forgot_password_message', array('%e' => $arrData['email_address'])));
                
                redirect(site_url('/login'));
            }
        }

        /* Get the form for display */
        return $this->get_fields();
        
    }



    
    
    /**
     * Login
     * 
     * Gets the site login
     * 
     * @return array
     */
    public function login() {
        
        /* Load stuff */
        $this->load->model('users');
        
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
            
            if($this->users->valid_user($arrData['email_address'], $arrData['password'], false) === false) {
                /* Fail the login */
                $this->set_error('valid_login', $this->lang->line('error_login'));
            }
            
            /* Validate the form */
            if($this->run()) {

                /**
                 * This is where you would put what happens
                 * AFTER we have validated the form. Don't
                 * forget to redirect to remove the POST
                 * headers
                 */
                
                /* Valid login - let's save it */
                $this->users->save_login($arrData['email_address'], $arrData['password'], $arrData['save_login'], false);
                
                /* Where do we redirect to */
                
                $redirect = $this->session->userdata($this->config->item('session_redirect'));
                if($redirect === false) { $redirect = site_url(); }
                redirect($redirect);
            }
        }

        /* Get the form for display */
        return $this->get_fields();
    }
    
    
    
    
    
    
    
    
    
    /**
     * Paging
     * 
     * Allows you to display a paging form
     * 
     * @param array $arrConfig
     * @param array $arrPerPage
     * @param array $arrOrder
     * @param array $arrLabels
     * @param string $url
     * @return array
     */
    public function paging($arrConfig, $arrPerPage, $arrOrder, array $arrLabels = array(), $url = null) {
        
        /* Get the labels */
        $per_page = false;
        $display_order = false;
        
        if(array_key_exists('per_page', $arrLabels)) { $per_page = $arrLabels['per_page']; }
        if(array_key_exists('display_order', $arrLabels)) { $display_order = $arrLabels['display_order']; }
        
        if($per_page === false) { $per_page = $this->lang->line('form_per_page'); }
        if($display_order === false) { $display_order = $this->lang->line('form_display_order'); }
        
        /* Create the form */
        $arrForm = array(
            array(
                'id' => null,
                'name' => $this->config->item('per_page', 'rest'),
                'type' => 'form_dropdown',
                'label' => $per_page,
                'value' => $arrConfig['per_page'],
                'options' => $arrPerPage,
                'extra' => 'class="rest_redirect"',
                'add_select' => false,
            ),
            array(
                'id' => null,
                'name' => $this->config->item('order_by', 'rest'),
                'type' => 'form_dropdown',
                'label' => $display_order,
                'value' => $arrConfig['order_by'],
                'options' => $arrOrder,
                'extra' => 'class="rest_redirect"',
                'add_select' => false,
            ),
            array(
                'id' => null,
                'name' => 'url',
                'type' => 'form_hidden',
                'value' => $url,
                'class' => 'url',
            ),
        );

        /* Set the form */
        $this->set_fields($arrForm);

        /* Set the buttons */
        $this->add_button('submit', $this->lang->line('action_menu_go'));

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
            
            /* Validate the form */
            if($this->run()) {

                /**
                 * This is where you would put what happens
                 * AFTER we have validated the form. Don't
                 * forget to redirect to remove the POST
                 * headers
                 */
                
                if(empty($url)) { $url = current_url(); }
                
                /* Now get the REST index */
                $url = $this->input->rest_replace($this->config->item('per_page', 'rest'), $arrData[$this->config->item('per_page', 'rest')], $url);
                
                $url = $this->input->rest_replace($this->config->item('order_by', 'rest'), $arrData[$this->config->item('order_by', 'rest')], $url);
                
                redirect($url);
            }
        }

        /* Get the form for display */
        return $this->get_fields();
    
    }
    
    
    
    
    
    
    
    
    
    /**
     * Reset Password
     * 
     * Changes the password for the given user
     * 
     * @param int $userId
     * @return array
     */
    public function reset_password($userId) {
        
        /* Load stuff */
        $this->load->model('users');
        
        /* Create the form */
        $arrForm = array(
            array(
                'name' => 'password',
                'type' => 'form_password',
                'label' => $this->lang->line('form_new_password'),
                'rules' => 'required|min_length[6]',
            ),
            array(
                'name' => 'confirm_password',
                'type' => 'form_password',
                'label' => $this->lang->line('form_password_confirm'),
                'rules' => 'required|matches[password]',
            ),
        );

        /* Set the form */
        $this->set_fields($arrForm);

        /* Set the buttons */
        $this->add_button('submit', $this->lang->line('action_menu_submit'));

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
            
            /* Validate the form */
            if($this->run()) {

                /**
                 * This is where you would put what happens
                 * AFTER we have validated the form. Don't
                 * forget to redirect to remove the POST
                 * headers
                 */
                
                /* Valid - let's save it */
                $this->users->change_password($arrData['password'], $userId);
                
                $this->session->set_flashdata($this->config->item('session_login_msg'), $this->lang->line('change_password_message'));
                
                redirect(site_url('/login'));
            }
        }

        /* Get the form for display */
        return $this->get_fields();
    
    }
    
    
    
    
    
    
    
    
    
    /**
     * Search
     * 
     * Searches the system
     * 
     * @return array
     */
    public function search() {
        
        /* Get the search value */
        $value = null;
        if(preg_match('/^('.preg_quote(search_query_url(), '/').')/', current_url())) {
            $arrSearchUrl = explode('/', preg_replace('/^('.preg_quote(search_query_url(), '/').')(\/)?/', '', current_url()));
            
            /* Decode and set the search query */
            if(isset($arrSearchUrl[0])) { $value = urldecode($arrSearchUrl[0]); }
        }
        
        /* Create the form */
        $arrForm = array(
            array(
                'name' => 'search_input',
                'type' => 'form_input',
                'value' => $value,
            ),
        );

        /* Set the form */
        $this->set_fields($arrForm);

        /* Set the buttons */
        $this->add_button('submit', $this->lang->line('action_menu_go'), 'submit', 'search_action_icon');

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
            
            /* Validate the form */
            if($this->run()) {

                /**
                 * This is where you would put what happens
                 * AFTER we have validated the form. Don't
                 * forget to redirect to remove the POST
                 * headers
                 */
                
                $query = urlencode($arrData['search_input']);
                
                redirect(search_query_url($query));
            }
        }

        /* Get the form for display */
        return $this->get_fields();
    
    }
    


}
?>