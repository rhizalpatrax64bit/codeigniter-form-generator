<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Form_generator
 *
 * This is where the bulk of the form generation
 * takes place.  If you wish to override anything,
 * please do it in MY_Form_validation so you can
 * easily upgrade the library
 *
 * @author Simon Emms <simon@simonemms.com>
 * @since 30-Dec-2011
 */

class Form_generator extends CI_Form_validation {


    /* Version of this library */
    const FORM_VER = 1.0;


    /* Prepend for add form type function */
    const FORM_ADD_PREPEND = '_add_';


    /* Name of default add function */
    const FORM_DEFAULT_ADD = 'form_default';
    
    
    /* Prepend for output form type function */
    const FORM_OUTPUT_PREPEND = '_output_';
    
    
    /* Name of default output function */
    const FORM_DEFAULT_OUTPUT = 'form_default';
    
    
    /* Prepend for input form data function */
    const FORM_INPUT_PREPEND = '_input_';
    
    
    /* Name of default form input */
    const FORM_DEFAULT_INPUT = 'set_default';
    
    
    /* Form ID field */
    const FORM_ID = 'form_id';
    
    
    
    protected $_arrFields = array();
    protected $_arrButtons = array();
    public static $formId = null;
    protected $_arrKeys = array(); /* Used by the field specific stuff  */
    protected $_arrOverride = array();
    protected $_url;
    protected $_multipart = false;
    protected $_button_template = false;
    protected $_arrHelp = array();
    protected $_arrFormVars = false;
    protected $_arrDetails = array(); /* Where the POST lives */
    protected $_saved_form_id = null;
    protected $_needs_formId = true;
    protected $_errors_display = false;
    
    /* Ignored values */
    protected $_arrIgnored = array(
        'form_plain',
        'form_captcha',
    );
    
    /* Do we have a CAPTCHA in the form */
    protected $_has_captcha = false;
    
    /* Do we have required in the form */
    protected $_has_required = false;
    
    /* New fields with data taken from existing */
    protected $_arrFieldReplace = array(
        'id' => 'name',
        'field' => 'name'
    );
    
    
    
    /* These must always be in, with their default data */
    protected $_arrAdd = array(
        'value' => null,
    );
    
    
    
    
    public function __construct($rules = array()) {
        parent::__construct($rules);
        
        /* Load the helpers */
        $objCI = &get_instance();
        $objCI->load->helper('url');
        $objCI->load->helper('form');
        $objCI->load->config('form_validation');
        $objCI->lang->load('form_validation');
        $objCI->lang->load('MY_form_validation');
        
        if(isset($this->_arrIgnore)) {
            $this->_arrIgnored = array_merge($this->_arrIgnored, $this->_arrIgnore);
        }
        
        /* Set URL to this page by default */
        $this->_url = current_url();
    }
    
    
    
    
    
    
    /**
     * Add Form CAPTCHA
     * 
     * Mark that we have a CAPTCHA field
     * 
     * @param array $arrFields
     * @param string $name
     * @param string $id
     * @return array
     */
    protected function _add_form_captcha($arrFields, $name = null, $id = null) {
        /* Mark that we have CAPTCHA */
        $this->_has_captcha = true;
        
        return $arrFields;
    }






    /**
     * Add Form Dropdown
     * 
     * @param type $arrField
     * @param type $name
     * @param type $id
     * @return array
     */
    protected function _add_form_dropdown($arrField, $name = null, $id = null) {
        if(is_null($id)) {
            /* ID must be set in the extra string */
            $id = $arrField['id'];
        }
        
        /* Ensure the default stuff is added */
        if(!array_key_exists('options', $arrField)) { $arrField['options'] = array(); }
        if(!array_key_exists('extra', $arrField)) { $arrField['extra'] = null; }
        
        /* Check for required - change to required_dropdown */
        if(array_key_exists('rules', $arrField) && $arrField['rules'] != '') {
            $arrRules = explode('|', $arrField['rules']);
            if(count($arrRules) > 0) {
                foreach($arrRules as $key => $value) {
                    if(strtolower($value) == 'required') {
                        /* Required is set - change to required_dropdown */
                        $arrRules[$key] =  'required_dropdown';
                    }
                }
            }
            $arrField['rules'] = implode('|', $arrRules);
        }
        
        /* ID must be set in the extra string */
        $id = 'id="'.$id.'"';
        if(!preg_match("/{$id}/", $arrField['extra'])) {
            /* Not already in there */
            $arrField['extra'] .= " {$id}";
            $arrField['extra'] = trim($arrField['extra']);
        }
        
        return $arrField;
    }
    
    
    
    
    
    
    /**
     * Add Form Dropdown
     * 
     * @param type $arrField
     * @param type $name
     * @param type $id
     * @return array
     */
    public function _add_form_hidden($arrField, $name = null, $id = null) {
        if(!array_key_exists('name', $arrField)) { $arrField['name'] = $arrField['id']; }
        return $arrField;
    }
    
    
    
    
    
    
    
    /**
     * Add Keys
     * 
     * Ensures that there are certain keys in
     * always
     * 
     * @param array $arrField
     * @return array
     */
    protected function _add_keys($arrField) {
        if(count($this->_arrAdd) > 0) {
            foreach($this->_arrAdd as $key => $value) {
                if(!array_key_exists($key, $arrField)) {
                    $arrField[$key] = $value;
                }
            }
        }
        
        return $arrField;
    }
    
    
    
    
    
    
    /**
     * Decode Form ID
     * 
     * Decodes the form ID from the form that's given
     * 
     * @param array $arrFields
     * @return string/null
     */
    protected function _decode_form_id($arrFields = null) {
        if(array_key_exists(self::FORM_ID, $arrFields)) {
            if(array_key_exists('form_hidden', $arrFields[self::FORM_ID])) {
                /* Get the value */
                if(array_key_exists('value', $arrFields[self::FORM_ID]['form_hidden'])) {
                    $formId = $arrFields[self::FORM_ID]['form_hidden']['value'];
                    
                    /* Save the formId */
                    $this->_saved_form_id = $formId;
                }
            }
        }
        return $this->_saved_form_id;
    }
    
    




    /**
     * Do Form ID
     *
     * Converts an array into an MD5 string. Identifies
     * the form from other forms on the page once we have
     * posted it.
     *
     * @param array $arrFields
     * @return string
     */
    protected function _do_form_id(array $arrFields = null, $force_generate = false) {
        if(count($arrFields) > 0 && (is_null(self::$formId) || $force_generate)) {
            $arrID = array();
            foreach($arrFields as $field) {
                if(is_multi_array($field) && count($field) > 0) {
                    foreach($field as $node) {
                        $arrID[] = $node['name'];
                    }
                } else {
                    $arrID[] = $field['name'];
                }
            }
            if($force_generate) {
                return md5(serialize($arrID));
            } else {
                self::$formId = md5(serialize($arrID));
                return self::$formId;
            }
        } elseif(is_null(self::$formId) === false) {
            return self::$formId;
        }
        return null;
    }
    
    




    /**
     * Duplicate Keys
     *
     * Because CI clearly hasn't thought it out, there's
     * some fields that are actually repeating the same
     * data.  Add it in here to save repeating in the
     * controller.
     *
     * @param array $arrFields
     * @return array
     */
    protected function _duplicate_keys($arrFields) {
        if(count($this->_arrFieldReplace) > 0) {
            foreach($this->_arrFieldReplace as $new => $old) {
                if(array_key_exists($old, $arrFields) && !array_key_exists($new, $arrFields)) {
                    $arrFields[$new] = $arrFields[$old];
                }
            }
        }
        return $arrFields;
    }





    /**
     * Field Specific
     *
     * If any specific elements must be validated or within
     * an input, then this is the place to do it.
     *
     * @param string $name
     * @param array $arrFields
     */
    protected function _field_specific($name = null, array $arrFields = null) {
        $sectioned = is_null($name);
        if(is_null($arrFields)) { $arrFields = $this->_arrFields; }

        /* Run through and validate the fields */
        if(count($arrFields) > 0) {
            foreach($arrFields as $key => $field) {
                if(is_multi_array($field)) {
                    /* Reiterate this function if the form is sectioned */
                    $this->_field_specific($key, $field);
                } else {
                    /* Run the function */
                    $this->_arrKeys = array();
                    
                    /* Get the function */
                    $function = '_field_specific_';
                    $function .= $field['type'];
                    
                    if(method_exists($this, $function)) {
                        $arrTmp = $this->{$function}($field);
                        
                        /* Check if we're returning the whole field array */
                        if(is_array($arrTmp) && count($arrTmp) > 0) {
                            $arrFields[$key] = $arrTmp;
                        }
                    }

                    /* Is it required */
                    if(array_key_exists('rules', $field) && preg_match('/required/', $field['rules'])) {
                        $arrFields[$key]['required'] = true;
                        
                        /* Do this field have required in it */
                        if($this->_has_required === false) {
                            $this->_has_required = true;
                        }
                    }

                    /* Error check */
                    if(!array_keys_exist($this->_arrKeys, $field)) {
                        $keys = print_r($this->_arrKeys, true);
                        show_error("The {$field['name']} field requires all these extra keys: <pre>{$keys}</pre>");
                    }

                    /* Set this to the fields array */
                    if($sectioned) {
                        $this->_arrFields = $arrFields;
                    } else {
                        $this->_arrFields[$name] = $arrFields;
                    }
                }
            }
        }
    }
    
    
    
    
    
    
    /**
     * Output Form CAPTCHA
     * 
     * Outputs the CAPTCHA field
     * 
     * @param string $type
     * @param array $arrForm
     * @return string
     */
    protected function _output_form_captcha($type, $arrForm) {
        /* Load relevant stuff */
        $objCI = &get_instance();
        $objCI->load->library('Recaptcha');
        return $objCI->recaptcha->get_html();
    }
    
    
    
    
    
    
    /**
     * Output Form Default
     * 
     * Outputs the form element HTML
     * 
     * @param string $type
     * @param array $arrForm
     * @return string
     */
    protected function _output_form_default($type, $arrForm) { return $type($arrForm); }
    
    
    
    
    
    
    /**
     * Output Form Dropdown
     * 
     * Returns a select box
     * 
     * @param string $type
     * @param array $arrForm
     * @return string
     */
    protected function _output_form_dropdown($type, $arrForm) {
        $add_select = true;
        if(array_key_exists('add_select', $arrForm) && is_bool($arrForm['add_select'])) { $add_select = $arrForm['add_select']; }
        
        $arrOptions = array();
        
        /* First, add the prepend key */
        if($add_select) {
            $objCI = &get_instance();
            $arrOptions[] = $objCI->lang->line('dropdown_select');
        }
        
        /* Check the options are an array */
        if(!is_array($arrForm['options'])) { $arrForm['options'] = array($arrForm['options']); }
        
        /* Add in the options again */
        if(count($arrForm['options']) > 0) {
            foreach($arrForm['options'] as $key => $value) {
                $arrOptions[$key] = $value;
            }
        }
        
        /* Return the string */
        return $type($arrForm['name'], $arrOptions, $arrForm['value'], $arrForm['extra']);
    }
    
    
    
    
    /**
     * Output Form Hidden
     * 
     * Outputs a hidden form
     * 
     * @param string $type
     * @param array $arrForm
     * @return string
     */
    protected function _output_form_hidden($type, $arrForm) {
        if(array_key_exists('id', $arrForm)) {
            /* Form hidden with ID */
            $form = '<input type="hidden" id="'.$arrForm['id'].'" name="'.$arrForm['name'].'" value="'.$arrForm['value'].'"';
            if(array_key_exists('class', $arrForm)) { $form .= ' class="'.$arrForm['class'].'"'; }
            $form .= ' />';
            return $form;
        } else {
            /* Use the default form_hidden */
            return $type($arrForm['name'], $arrForm['value']);
        }
    }






    /**
     * Output Form Plain
     * 
     * Returns a plain text in place of a form
     * 
     * @param array $arrForm
     * @return string
     */
    protected function _output_form_plain($type, array $arrForm = array()) {
        $output = '<div class="form_plain"';
        if(array_key_exists('id', $arrForm)) { $output .= ' id="'.$arrForm['id'].'"'; }
        $output .= '>';
        if(array_key_exists('value', $arrForm)) { $output .= $arrForm['value']; }
        $output .= '</div>';
        return $output;
    }
    
    
    
    
    
    /**
     * Form Row
     * 
     * Output the HTML for the form row
     * 
     * @param array $field
     * @param string $type
     * @param string $key
     * @param array $arrData
     * @return string 
     */
    protected function _form_row($field, $type, $key, $arrData) {
        
        $objCI = &get_instance();
        
        $output = '';
        
        /* Open the row and label */
        if($type != 'form_hidden') {
            /* Start the row */
            $output .= '<div class="';
            $output .= $type.'_wrapper '.$arrData['config']['row_class'];
            if(array_key_exists('required', $field) && $field['required']) { $output .= ' '.$arrData['config']['required_class']; }
            if(array_key_exists('hideJS', $field) && $field['hideJS'] === true) { $output .= ' '.$arrData['config']['hideJS_class']; }
            if(array_key_exists('hidden', $field) && $field['hidden'] === true) { $output .= ' '.$arrData['config']['hidden_class']; }
            if(array_key_exists($field['name'], $arrData['error_array'])) { $output .= ' '.$arrData['config']['error_class']; }
            $output .= '">';
            
            /* Check for a label */
            if(array_key_exists($key, $arrData['labels'])) {
                /* Check for a required flag */
                $label = $arrData['labels'][$key];
                $label_name = $label['name'];
                
                /* Check for label_format */
                $label_format = $objCI->config->item('label_format', 'form_validation');
                if($label_format !== false && $label_format != '%l') {
                    $label_name = str_replace('%l', $label_name, $label_format);
                }
                
                if(array_key_exists('required', $field) && $field['required']) {
                    /* Required */
                    $flag = $objCI->config->item('required_flag', 'form_validation');
                    if(is_string($flag) && preg_match('/(\%l)/', $flag)) {
                        $label_name = str_replace('%l', $label_name, $flag);
                    }
                }
                
                $output .= form_label($label_name, $field['id'], $label['attributes']);
            }
            
            /* Open the line */
            $output .= '<div class="'.$arrData['config']['line_class'].'">';
        }
        
        /* Output the form fields */
        $function = self::FORM_OUTPUT_PREPEND;
        $function .= $type;
        
        if(method_exists($this, $function) === false) {
            $function = self::FORM_OUTPUT_PREPEND;
            $function .= self::FORM_DEFAULT_OUTPUT;
        }
        
        $output .= $this->{$function}($type, $field);
        
        /* Close the row and label */
        if($type != 'form_hidden') {
        
            /* Close the line */
            $output .= '</div>';
            
            /* Check for a help label - can only display if it's a visible form */
            $output .= $this->_output_help_label($field, $arrData['help']);
            
            /* Close the row */
            $output .= '</div>';
        }
        
        return $output;
    }
    
    
    
    
    
    
    /**
     * Generate Form View
     * 
     * Generates the view data of the form.  Done as
     * a separate method to output_form() so it can
     * be extended as required.
     * 
     * This is done as if it were a view file.
     * 
     * @param array $arrData 
     * @return string
     */
    protected function _generate_form_view(array $arrData) {
        
        /* Get the CI instance */
        $objCI = &get_instance();
        
        /* Start the output */
        $output = '';
        
        /* Do we do the start */
        if($arrData['start']) {
            
            /* Open the form */
            if($this->_multipart) {
                /* Form has an image */
                $output .= form_open_multipart($this->_url, $arrData['form_vars']);
            } else {
                /* Ordinary form to open */
                $output .= form_open($this->_url, $arrData['form_vars']);
            }
        }
        
        /* Are there any errors to display */
        if($arrData['display_errors'] && strlen($arrData['errors']) > 0) {
            /* Encase it in a div */
            $output .= '<div class="error_outer_wrapper">';
            $output .= '<h3>'.$objCI->lang->line('form_error_title').'</h3>';
            $output .= '<'.$arrData['config']['errors']['wrapper_open'].' class="'.$arrData['config']['errors']['class'].'">';
            $output .= $arrData['errors'];
            $output .= '<'.$arrData['config']['errors']['wrapper_close'].'>';
            $output .= '</div>';
        }
        
        /* Output the fields */
        if(isset($arrData['form']) && count($arrData['form']) > 0) {
            foreach($arrData['form'] as $key => $arrFields) {
                if(count($arrFields) > 0) {
                    foreach($arrFields as $type => $field) {
                        /* Build the row */
                        $output .= $this->_form_row($field, $type, $key, $arrData);
                    }
                }
            }
        }
        
        /* Output the required string */
        if($this->_has_required) {
            $show_required = array_key_exists('show_required', $arrData) && is_bool($arrData['show_required']) ? $arrData['show_required'] : true;
            if($show_required) {
                $required_flag = $objCI->config->item('required_flag', 'form_validation');
                if($required_flag === false) { $required_flag = ''; }
                
                /* Extract %l */
                $required_flag = trim(str_replace('%l', '', $required_flag));
                $required_text = false;
                
                if($required_flag != '') {
                    $required_text = $this->language('required_message', array('%l' => $required_flag));
                }
                
                if($required_text !== false) {
                    $output .= '<div class="required_text row">';
                    $output .= $required_text;
                    $output .= '</div>';
                }
            }
        }
        
        /* Output the actionmenu */
        if(($arrData['end'] === true && $arrData['hide_actionmenu'] === false) || ($arrData['end'] === false && $arrData['actionmenu'] === true)) {
            $output .= '<div class="'.$arrData['config']['buttons']['class'].'_wrapper">';
            if($arrData['buttonTemplate'] !== false) {
                /* Import the template file */
                include($arrData['buttonTemplate']);
            } elseif(count($arrData['buttons']) > 0) {
                /* Generate it */
                $output .= '<'.$arrData['config']['buttons']['wrapper_open'].' class="'.$arrData['config']['buttons']['class'].'">';
                
                if($arrData['title'] !== false) {
                    /* Add in an actionmenu title */
                    $output .= '<'.$arrData['config']['buttons']['delimiter_open'].' class="'.$arrData['config']['buttons']['class'].'-title">';
                    $output .= $objCI->lang->line($arrData['title']);
                    $output .= '<'.$arrData['config']['buttons']['delimiter_close'].'>';
                }
                /* Do the buttons */
                foreach($arrData['buttons'] as $key => $button) {
                    $output .= '<'.$arrData['config']['buttons']['delimiter_open'];
                    $output .= ' class="'.$arrData['config']['buttons']['class'].'-row '.$button['name'].'-button';
                    if($key == count($arrData['buttons']) - 1) { $output .= ' last'; } /* Mark the last element */
                    $output .= '">';
                    
                    if(strtolower($button['type']) == 'html') {
                        $output .= $button['value'];
                    } else {
                        $function = 'form_'.$button['type'];
                        $output .= $function($button);
                    }
                    
                    $output .= '<'.$arrData['config']['buttons']['delimiter_close'].'>';
                }
                
                /* Close it */
                $output .= '<'.$arrData['config']['buttons']['wrapper_close'].'>';
            }
            $output .= '</div>';
        }
        
        /* Do we close the form */
        if($arrData['end']) {
            /* Close form */
            $output .= form_close();
            
            /* Make sure no floats/overflows knacker up the form */
            $output .= '<div style="clear: both;"></div>';
            
        }
        
        return $output;
        
    }
    
    
    
    
    
    
    /**
     * Output Help Label
     * 
     * Output the help label
     * 
     * @param array $arrField
     * @param array $arrHelp
     * @return string
     */
    protected function _output_help_label($arrField, $arrHelp) {
        $string = '';
        if(is_array($arrHelp) && array_key_exists($arrField['name'], $arrHelp)) {
            $objCI = &get_instance();
            
            $string = '<div class="'.$objCI->config->item('help_class', 'form_validation').'">'.$arrHelp[$arrField['name']].'</div>';
        }
        return $string;
    }
    
    
    
    
    
    
    /**
     * Pre Input Validation
     * 
     * This is activated by the activate()
     * method, once we know we're activating
     * this form.  Done before the input is
     * put into $_arrDetails
     */
    protected function _pre_input_validation() {
        
        /* The correct form has been submitted - check for CAPTCHA */
        if($this->_has_captcha) {
            /* Get the CI instance */
            $objCI = &get_instance();
            
            /* There's CAPTCHA - check it's right */
            $objCI->load->library('recaptcha');

            /* Get the CAPTCHA response */
            if($objCI->recaptcha->check_answer() === false) {
                /* CAPTCHA is false - set the message and fail */
                $this->set_error('check_captcha', $objCI->lang->line('recaptcha_incorrect_response'));
            } else {
                /* Succeeded - unset the CAPTCHA stuff */
                unset($_POST['recaptcha_challenge_field']);
                unset($_POST['recaptcha_response_field']);
            }
        }
        
    }
    
    
    
    
    
    
    
    /**
     * Post Input Validation
     * 
     * This is activated by the activate method. Done
     * once the input has been set. 
     */
    protected function _post_input_validation() {
        
    }
    
    
    
    
    
    
    /**
     * Replace Form Default
     * 
     * Replace the form value
     * 
     * @param array $arrField
     * @return string
     */
    protected function _replace_form_default($arrField) {
        
        /* Get the input class */
        $objInput = &load_class('Input', 'core');
        
        $value = $objInput->post($arrField['name'], true);
        
        if($value !== false) {
            /* If not ignored */
            if(!isset($this->_arrIgnored) || !is_array($this->_arrIgnored) || !in_array($arrField['type'], $this->_arrIgnored)) {
                /* Only add if not in ignore array */
                if(is_string($value)) { $value = trim($value); }
                
                /* Return the value */
                return $value;
            }
        } elseif(array_key_exists('value', $arrField)) {
            /* Return what was already set */
            return $arrField['value'];
        }
        
        /* Nothing */
        return null;
    }
    
    
    
    
    
    /**
     * Replace Form Password
     * 
     * Replaces the password value with an empty
     * string for display purposes
     * 
     * @param string $arrField
     * @return string 
     */
    protected function _replace_form_password($arrField) {
        
        /* Get the input class */
        $objInput = &load_class('Input', 'core');
        
        $value = $objInput->post($arrField['name'], true);
        
        /* Don't show the password if set */
        if($value !== false) { $arrField['value'] = ''; }
        
        return $arrField['value'];
    }
    
    
    
    
    
    /**
     * Reset Get Fields
     * 
     * Resets the form ID 
     */
    protected function _reset_get_fields() { self::$formId = null; }
    
    
    
    
    
    /**
     * Input Set Default
     * 
     * Gets the input detail for the default form
     * type
     * 
     * @param string $name
     * @param array $arrField
     * @return string
     */
    protected function _input_set_default($name, array $arrField = array()) {
        $objCI = &get_instance();
        
        $value = $objCI->input->post($name);
        if(is_string($value)) { $value = trim($value); }
        return $value;
        
    }
    
    
    
    
    
    
    
    
    /**
     * Set Input Data
     * 
     * Sets the input data
     * 
     * @param array $arrField
     */
    protected function _set_input_data(array $arrField) {
        
        /* Get the input function */
        $function = self::FORM_INPUT_PREPEND;
        $function .= $arrField['type'];
        
        if(method_exists($this, $function) === false) {
            /* Use the default one */
            $function = self::FORM_INPUT_PREPEND;
            $function .= self::FORM_DEFAULT_INPUT;
        }
        
        /* Add the data */
        if(!in_array($arrField['type'], $this->_arrIgnored)) {
            $this->_arrDetails[$arrField['name']] = $this->{$function}($arrField['name'], $arrField);
        }
    }





    /**
     * Set Rules
     *
     * Extends the parent method to allow setting of
     * rules for specified fields
     *
     * @param mixed $field
     * @param string $label
     * @param string $rules
     */
    public function set_rules($field, $label = '', $rules = '') {
        if(is_array($field) && !is_numeric(key($field)) && count($field) > 0) {
            /* If not numeric, this is specified when set */
            foreach($field as $inner) {
                parent::set_rules($inner);
            }
        } else {
            /* Just use the parent method */
            parent::set_rules($field, $label, $rules);
        }
    }
    
    
    
    
    
    /**
     * Activate
     *
     * Activates the form validation operation.  Returns
     * true if this table is being submitted, false if
     * nothing to do with us here.  Useful to allow you
     * to have multiple forms (ie, login/search) per page
     *
     * @return bool
     */
    public function activate() {
        /* Get the instance */
        $objCI = &get_instance();
        
        if(!is_null($this->_arrFields)) {
            /* Has anything been posted? */
            $post_formId = $objCI->input->post(self::FORM_ID);
            
            if($post_formId !== false) {
                
                /* Get the form ID */
                $formId = $this->_do_form_id($this->_arrFields, true);
                
                /* Check the ID passed is the same as the one generated */
                if($post_formId === $formId) {

                    /* Run custom validation rules */
                    if(method_exists($this, '_pre_input_validation')) { $this->_pre_input_validation(); }

                    /* Set the input fields */
                    $this->set_input();

                    /* Run custom validation rules */
                    if(method_exists($this, '_post_input_validation')) { $this->_post_input_validation(); }

                    /* Set the rest of the rules */
                    $this->set_rules($this->_arrFields);
                    
                    /* We have activated the form*/
                    return true;
                }
            }
        }
        /* Not activated */
        return false;
    }
    
    
    
    
    
    
    /**
     * Add Button
     *
     * Add a button to the form. To set an HTML element, set
     * $name to null, the HTML in $value and $type = 'html'
     *
     * @param array/string $name
     * @param string $value
     * @param string $type
     * @param string $id
     */
    public function add_button($name, $value = null, $type='submit', $id = null) {
        $arrType = array(
            'submit',
            'reset',
            'button',
            'html',
        );
        $arrKeys = array(
            'name',
            'value',
        );
        if(in_array($type, $arrType) || is_array($name)) {
            if(!$this->_arrButtons) { $this->_arrButtons = array(); }
            if(is_array($name)) {
                /* Make sure a type is set */
                if(!array_key_exists('type', $name)) { $name['type'] = $type; }
                if($name['type'] == 'button') { $arrKeys[] = 'content'; }
                if(array_keys_exist($arrKeys, $name)) {
                    $this->_arrButtons[] = $name;
                } else {
                    show_error("Not enough data in the button array");
                }
            } elseif(!is_null($value)) {
                $arrButton = array();
                if($type == 'button') { $arrButton['content'] = $value; }
                $arrButton = array(
                    'name' => $name,
                    'value' => $value,
                    'type' => $type,
                );
                /* Do we need to add the ID */
                if(!is_null($id) && is_string($id)) { $arrButton['id'] = $id; }
                
                $formId = $this->_do_form_id($this->_arrFields);
                
                $this->_arrButtons[$formId][] = $arrButton;
            } else {
                show_error("Value cannot be null");
            }
        } else {
            show_error("{$type} is not a valid type");
        }
    }





    /**
     * Add CSS Class
     *
     * Add CSS class to an HTML string
     *
     * @param string $classname
     * @param string $html
     * @return string
     */
    public function add_css_class($classname, $html) {
        $classname = strtolower($classname);
        $html = strtolower($html);
        if($html && preg_match("/class([[:space:]]+)?=/", $html)) {
            $html = preg_replace("/class([[:space:]]+)?=([[:space:]]+)?(\"|')/", "class=\\3{$classname} ", $html);
        } else {
            $classname = ' class="'.$classname.'"';
            $html = preg_replace('/(\<)(\w+)(\>)/', '\\1\\2'.$classname.'\\3', $html);
        }

        return $html;
    }
    
    
    
    
    
    /**
     * Alnum Space
     * 
     * Alpha-numeric with space
     * 
     * @param string $str
     * @return bool
     */
    public function alnum_space($str) {
        return ( ! preg_match("/^([a-z0-9\s])+$/i", $str)) ? FALSE : TRUE;
    }
    
    
    
    
    
    /**
     * Alpha Dash Space
     * 
     * Alpha numeric with dash, underscore and space
     * @param string $str
     * @return bool
     */
    public function alpha_dash_space($str) {
        return ( ! preg_match("/^([-a-z0-9_-\s])+$/i", $str)) ? FALSE : TRUE;
    }
    
    
    
    
    
    /**
     * Alpha Space
     * 
     * Alpha with space
     * 
     * @param string $str
     * @return bool
     */
    public function alpha_space($str) {
        return ( ! preg_match("/^([a-z\s])+$/i", $str)) ? FALSE : TRUE;
    }
    
    
    
    
    
    /**
     * Disable Multipart 
     * 
     * Cancels multipart data form
     */
    public function disable_multipart() { $this->_multipart = false; }
    
    
    
    
    
    /**
     * Enable Multipart
     * 
     * Multipart data, to allow uploading
     * of files
     */
    public function enable_multipart() { $this->_multipart = true; }







    /**
     * Format Fields
     *
     * Format the fields in a way to use the Smarty form function
     *
     * @param array $arrForm
     * @return array
     */
    public function format_fields(array $arrForm) {
        $arrNew = array();
        if(count($arrForm) > 0) {
            foreach($arrForm as $key => $form) {
                $type = $form['type'];
                unset($form['type']);
                if($type != 'form_dropdown_add') { unset($form['label']); }

                /* Add the show count */
                if(array_key_exists('show_count', $form) && $form['show_count'] === true) {
                    if(preg_match('/max_length\[(\d+)\]/i', $form['rules'], $arrCount)) {
                        $form['show_count'] = $arrCount[1];
                    } else {
                        $form['show_count'] = 'true';
                    }
                } else {
                    $form['show_count'] = false;
                }

                $arrNew[$form['name']][$type] = $form;
            }
        }
        return $arrNew;
    }





    /**
     * Get Button Template
     *
     * Get the button template
     *
     * @return string/false
     */
    public function get_button_template() {
        $strTemplate = false;
        if($this->_button_template !== false) {
            $strTemplate = $this->_button_template;
            $this->_button_template = false;
        }
        return $strTemplate;
    }






    /**
     * Get Buttons
     *
     * Get the buttons
     *
     * @
     * @return mixed
     */
    public function get_buttons($formId = null) {
        $arrButtons = $this->_arrButtons;
        if(is_null($formId) === null) {
            /* Return everything */
            $this->_arrButtons = null;
            return $arrButtons;
        } elseif(array_key_exists($formId, $arrButtons)) {
            /* Just return the specified buttons */
            unset($this->_arrButtons[$formId]);
            return $arrButtons[$formId];
        }
        /* Return empty array */
        return null;
    }
    
    
    
    
    /**
     * Get Details
     * 
     * Returns the $_POST data
     * 
     * @return array
     */
    public function get_details() { return $this->_arrDetails; }




    /**
     * Get Errors
     *
     * Get the erros as an HTML string
     *
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
     public function get_errors($clear_errors = true) {
        /* No errrors, validation passes! */
        if (count($this->_error_array) === 0) {
            $strErrors = '';
        }
        
        $objCI = &get_instance();
        $arrConfig = $objCI->config->item('form_validation');

        $prefix = '<'.$arrConfig['errors']['delimiter_open'].'>';
        $suffix = '<'.$arrConfig['errors']['delimiter_close'].'>';

        /* Generate the error string */
        $strErrors = '';
        $x = 0;
        foreach ($this->_error_array as $val) {
            if ($val != '') {
                if($x == 0) {
                    $strErrors .= $this->add_css_class('first', $prefix);
                } else {
                    $strErrors .= $prefix;
                }

                $strErrors .= $val.$suffix."\n";
                $x++;
            }
        }

        if($clear_errors) { $this->_error_array = array(); }
        return $strErrors;
    }
    
    
    
    
    
    
    /**
     * Get Fields
     *
     * Gets the fields so the controller can pass this through
     * to the view
     *
     * @param string $specify
     * @return array
     */
    public function get_fields($specify = null) {
        if(count($this->_arrFields) > 0 || count($this->getButtons() > 0)) {
            $formId = $this->_do_form_id($this->_arrFields);

            /* Run any necessary things on the form fields */
            $this->_field_specific();

            /* Create the form ID - this allows us to run multiple forms on one page */
            $arrFormId = array(
                'name' => self::FORM_ID,
                'value' => $formId,
                'type' => 'form_hidden',
            );

            if(is_null($specify)) {
                /* Put formId at the top, so we can get the indentifier immediately */
                array_unshift($this->_arrFields, $arrFormId);
                
                /* Reset things */
                $this->_reset_get_fields();

                /* Once this has been called, can't add anything to it */
                $arrFields = $this->_arrFields;
                $this->_arrFields = null;
            } else {
                if(array_key_exists($specify, $this->_arrFields)) {
                    $arrFields = $this->_arrFields[$specify];
                    
                    $arrSections = array_keys($this->_arrFields);

                    /* Unset so it's not found again */
                    unset($this->_arrFields[$specify]);

                    if($this->_needs_formId && array_search($specify, $arrSections) == 0) {
                        /* If this has gotten the last form element, add the ID */
                        $arrFields[] = array(
                            'name' => self::FORM_ID,
                            'value' => self::$formId,
                            'type' => 'form_hidden',
                        );

                        /* Reset things */
                        $this->_reset_get_fields();
                        
                        /* Set form ID to false */
                        $this->_needs_formId = false;
                    }
                    
                    /* If last one, make sure the needs_formId is set to true */
                    if(empty($this->_arrFields)) { $this->_needs_formId = true; }
                } else {
                    /* Doesn't exist - just exit */
                    return null;
                }
            }
            
            /* Do we need to override any of the values */
            if(count($this->_arrOverride) > 0 && is_array($arrFields) && count($arrFields) > 0) {
                $array = array();
                foreach($this->_arrOverride as $column => $value) {
                    if(preg_match('/(\w+)\[(\w+)\]/', $column, $arrMatch)) {
                        $array[$arrMatch[1]][$arrMatch[2]] = $value;

                        unset($this->_arrOverride[$column]);
                    }
                }

                /* Add it back in */
                if(count($array) > 0) { $this->_arrOverride = array_merge($this->_arrOverride, $array); }

                foreach($arrFields as $fieldId => $field) {
                    if(array_key_exists('name', $field) && array_key_exists($field['name'], $this->_arrOverride)) {
                        /* Value */
                        $value = $this->_arrOverride[$field['name']];

                        /* Change the field value */
                        $arrFields[$fieldId]['value'] = $value;

                        if(is_array($value) && array_key_exists('options', $field) && is_array($field['options']) && count($field['options']) > 0) {
                            foreach($field['options'] as $id => $option) {
                                if(array_key_exists($option['name'], $value)) {
                                    $arrFields[$fieldId]['options'][$id]['value'] = $value[$option['name']];
                                }
                            }
                        }

                        /* If it's an upload box, we need to change the field type to the viewer */
                        if($field['type'] == 'form_upload' && $value > 0) {
                            $arrFields[$fieldId]['type'] = 'form_file_viewer';
                        }
                    }
                }
            }
            
            return $arrFields;
        } else {
            /* Empty fields - just return array */
            return array();
        }
    }





    /**
     * Get Help
     *
     * Gets the help array
     *
     * @return array
     */
    public function get_help() {
        /* Clearout anything not used */
        if(count($this->_arrHelp) > 0) {
            foreach($this->_arrHelp as $key => $help) {
                if((empty($help) && $help != '0') || $help === false) {
                    unset($this->_arrHelp[$key]);
                }
            }
        }
        return $this->_arrHelp;
    }
    
    
    
    
    
    
    /**
     * Get Form Vars
     * 
     * Return the form variables and
     * reset them
     * 
     * @return array
     */
    public function get_form_vars() {
        $arrVars = $this->_arrFormVars;
        $this->_arrFormVars = false;
        return $arrVars;
    }






    /**
     * Get Labels
     *
     * Get the labels for the Smarty form function
     *
     * @param array $arrForm
     * @return array
     */
    public function get_labels(array $arrForm) {
        $arrLabels = array();
        if(count($arrForm) > 0) {
            foreach($arrForm as $key => $form) {
                if(array_key_exists('label', $form)) {

                    $arrLabel = array(
                        'name' => $form['label'],
                        'attributes' => array(),
                    );

                    if(array_key_exists('label_attributes', $form)) {
                        $arrLabel['attributes'] = $form['label_attributes'];
                    }

                    $arrLabels[$form['name']] = $arrLabel;
                }
            }
        }

        return $arrLabels;
    }
    
    
    
    
    
    
    /**
     * Get Submit
     *
     * Gets which submit we're using
     *
     * @param string $name
     * @return string
     */
    public function get_submit($name = null) {
        
        $objCI = &get_instance();

        /* Get the buttons */
        $arrButtons = $this->_arrButtons;
        $arrKeys = array(
            'name', 'value', 'type',
        );
        
        /* Get the form ID */
        $formId = $objCI->input->post(self::FORM_ID);
        
        if(is_array($arrButtons) && array_key_exists($formId, $arrButtons)) {
            $arrButtons  = $arrButtons[$formId];

            if(is_array($arrButtons) && count($arrButtons) > 0) {
                foreach($arrButtons as $button) {
                    if(is_array($button) && array_keys_exist($arrKeys, $button)) {

                        if(is_null($name) || $button['name'] == $name) {
                            /* Search for the input */
                            $input = $objCI->input->post($button['name'], true);
                            if($input !== false) {
                                if($input == $button['value']) {
                                    if(is_null($name)) {
                                        return $button['name'];
                                    } else {
                                        return true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return false;
    }
    
    
    
    
    
    
    
    public function is_active($formId) {
        /* Get the formId from the POST */
        $objCI = &get_instance();
        $post_formId = $objCI->input->post(self::FORM_ID);
        
        if($post_formId !== false && $post_formId == $formId) {
            return true;
        }
        return false;
        
    }
    
    
    
    
    
    
    /**
     * Language
     * 
     * Get the language value and also perform
     * a string replace on it.  If you pass an
     * array, the keys will be replaced by the
     * values.
     * 
     * Eg. the string "hello %n", pass,
     * array('%n' => 'Dave') and it will return
     * "hello Dave"
     * 
     * @param string $string
     * @param array $arrReplace
     * @return string/false
     */
    public function language($string, array $arrReplace = array()) {
        /* Get the language */
        $objCI = &get_instance();
        $lang = $objCI->lang->line($string);
        
        /* Replace values */
        if($lang !== false && count($arrReplace) > 0) {
            $lang = str_replace(array_keys($arrReplace), array_values($arrReplace), $lang);
        }
        
        return $lang;
    }
    
    
    
    
    
    
    
    /**
     * Output Form
     * 
     * Generates the HTML for the form
     * 
     * @param array $arrParams 
     * @return string
     */
    public function output_form(array $arrParams = array()) {
        
        /* Load CI */
        $objCI = &get_instance();
        
        /* Do we start the <form> */
        $start = (array_key_exists('start', $arrParams) && is_bool($arrParams['start'])) ? $arrParams['start'] : true;

        /* Is this the end of the </form> */
        $end = (array_key_exists('end', $arrParams) && is_bool($arrParams['end'])) ? $arrParams['end'] : true;

        /* Do we show the actionmenu - default is on the $end */
        $actionmenu = ($end === false && array_key_exists('actionmenu', $arrParams) && is_bool($arrParams['actionmenu'])) ? $arrParams['actionmenu'] : false;

        /* Do we show the title */
        $title = $actionmenu === true && array_key_exists('title', $arrParams) && is_scalar($arrParams['title']) ? (string) $arrParams['title'] : false;

        /* Do we hide the action menu */
        $hide_actionmenu = array_key_exists('hide_actionmenu', $arrParams) && is_bool($arrParams['hide_actionmenu']) ? $arrParams['hide_actionmenu'] : false;
        
        /* Show required - by default, show */
        $show_required = array_key_exists('show_required', $arrParams) && is_bool($arrParams['show_required']) ? $arrParams['show_required'] : true;

        /* Do we clear the errors */
        $clear_errors = array_key_exists('errors', $arrParams) && is_bool($arrParams['errors']) ? $arrParams['errors'] : $end;

        /* Have we got a form we're writing */
        if(array_key_exists('fields', $arrParams)) {
            $arrForm = $objCI->form_validation->format_fields($arrParams['fields']);
            $arrLabels = $objCI->form_validation->get_labels($arrParams['fields']);
        } else {
            $arrForm = array();
            $arrLabels = array();
        }

        /* Show errors - by default, show on start */
        $show_errors = array_key_exists('errors', $arrParams) && is_bool($arrParams['errors']) ? $arrParams['errors'] : null;
        if(is_null($show_errors)) {
            /* Only show errors if there something in the form - we may have to get the form_id */
            $show_errors = (count($arrForm) > 0 && $this->_errors_display === false) ? true : false;
            
            /* Make sure the errors are only shown once */
            if($show_errors) { $this->_errors_display = true; }
        }
        
        /* Reset the errors_display */
        if($end) { $this->_errors_display = false; }
        
        /* Get the page config */ 
        $arrConfig = $objCI->config->item('form_validation');
        
        /* Get the form variables */
        $arrFormVars = $this->get_form_vars();
        
        /* Get the formId */
        $formId = $this->_decode_form_id($arrForm);
        
        /* Check for any buttons */
        $arrButtons = $end || ($end === false && $actionmenu) ? $this->get_buttons($formId) : false;
        
        /* Check for a button template */
        $strTemplate = $end || ($end === false && $actionmenu) ? $this->get_button_template() : false;
        
        $arrErrors = array();
        $strErrors = null;
        if($this->is_active($formId)) {
            /* Get the errors array */
            $arrErrors = $this->_error_array;

            /* Get errors as a string */
            $strErrors = $this->get_errors($clear_errors);
        }
        
        /* Get the help */
        $arrHelp = $this->get_help();
        
        $arrData = array(
            'form' => $arrForm,
            'labels' => $arrLabels,
            'buttons' => $arrButtons,
            'config' => $arrConfig,
            'form_vars' => $arrFormVars,
            'display_errors' => $show_errors,
            'errors' => $strErrors,
            'error_array' => $arrErrors,
            'buttonTemplate' => $strTemplate,
            'help' => $arrHelp,
            'start' => $start,
            'end' => $end,
            'actionmenu' => $actionmenu,
            'hide_actionmenu' => $hide_actionmenu,
            'title' => $title,
            'show_required' => $show_required,
        );
        
        return $this->_generate_form_view($arrData);
    }
    
    
    
    
    
    
    /**
     * Required Dropdown
     * 
     * Performs a required function on a dropdown box.  If set
     * to 0 or nothing, it treats as the "select" option
     * 
     * @param number $str
     * @return bool
     */
    public function required_dropdown($str) {
        if($str == '' || $str == '0') {
            return false;
        } else {
            return true;
        }
    }
    
    
    
    
    
    
    /**
     * Set Error
     *
     * Used to set custom error messages.  If $message is
     * null, it just adds the error array - useful for triggering
     * which part of the form has failed
     *
     * @param string $field
     * @param string $message
     */
    public function set_error($field, $message = null) {
        $objCI = &get_instance();
        $translate = $objCI->lang->line($message);
        if($translate !== false) { $message = $translate; }
        $this->_error_array[$field] = $message;
    }






    /**
     * Set Fields
     *
     * Set the fields for the form
     *
     * @param array $arrFields
     */
    public function set_fields($arrFields, $name = null, $id = null) {

        if(count($arrFields) > 0) {
            if(is_array($arrFields) && is_array(current($arrFields))) {
                foreach($arrFields as $field) {
                    $this->set_fields($field, $name, $id);
                }
            } else {
                /* Get the function */
                $function = self::FORM_ADD_PREPEND;
                $function .= $arrFields['type'];

                /* Check the function exists - if not, use default */
                if(method_exists($this, $function) === false) {
                    $function = self::FORM_ADD_PREPEND;
                    $function .= self::FORM_DEFAULT_ADD;
                }
                
                /* Ensure these keys are in */
                $arrFields = $this->_add_keys($arrFields);
                
                /* Add in the duplicate keys */
                $arrFields = $this->_duplicate_keys($arrFields);

                /* Do we add this field to the display - useful if not every row needs to appear */
                if((array_key_exists('display', $arrFields) && $arrFields['display'] === true) || (array_key_exists('display', $arrFields) === false)) {
                    /* Run the add function */
                    if(method_exists($this, $function)) {
                        $arrFields = $this->$function($arrFields, $name, $id);
                    }
                    
                    /* Update the value with the POST data */
                    $replace = '_replace_';
                    $replace .= $arrFields['type'];

                    /* Check the function exists - if not, use default */
                    if(method_exists($this, $replace) === false) {
                        $replace = '_replace_';
                        $replace .= self::FORM_DEFAULT_ADD;
                    }
                    
                    $arrFields['value'] = $this->$replace($arrFields);
                }
                
                /* Save the field */
                if(is_null($name)) {
                    if(is_null($id)) {
                        /* Add the field */
                        $this->_arrFields[] = $arrFields;
                    } else {
                        /* Replace the field */
                        $this->_arrFields[$id] = $arrFields;
                    }
                } else {
                    if(is_null($id)) {
                        /* Add the field */
                        $this->_arrFields[$name][] = $arrFields;
                    } else {
                        /* Replace the field */
                        $this->_arrFields[$name][$id] = $arrFields;
                    }
                }
            }
        }

    }




    public function set_form_vars(array $arrVars) {
        if(!$this->_arrFormVars) { $this->_arrFormVars = array(); }
        if(count($arrVars) > 0) {
            foreach($arrVars as $key => $var) {
                $this->_arrFormVars[$key] = $var;
            }
        }
    }
    
    
    
    
    
    
    /**
     * Set Input
     *
     * For each submitted field, it grabs the data.
     */
    public function set_input() {
        if(count($this->_arrFields) > 0) {
            foreach($this->_arrFields as $id => $field) {
                if(is_array($field) && is_array(current($field))) {
                    foreach($field as $inner) {
                        /* Multi-level form added */
                        $this->_set_input_data($inner);
                    }
                } else {
                    /* Single level form added */
                    $this->_set_input_data($field);
                }
            }
        }
    }
    
    
    



    /**
     * Set URL
     *
     * Set the URL that the form will post to
     *
     * @param string $url
     */
    public function set_url($url) {
        if(is_string($url) && !empty($url)) {
            $this->_url = $url;
        }
    }

    

}







/**
 * Array Keys Exist
 *
 * Does the array_key_exist function for many
 * keys
 *
 * @param array $arrKey
 * @param array $arrArray
 * @return bool
 */
if(!function_exists('array_keys_exist')) {
    function array_keys_exist($arrKey, $arrArray) {
        if(count($arrKey) > 0) {
            foreach($arrKey as $key) {
                if(!array_key_exists($key, $arrArray)) {
                    return false;
                }
            }
        }
        return true;
    }
}







/**
 * Is Multi Array
 *
 * Checks to see if the first element in the
 * array is also an array.  Under most situations,
 * (at least with my coding style) this would
 * mean that it is a multilevel array.
 *
 * @author Simon Emms
 * @param array $array
 * @return bool
 */
if(!function_exists('is_multi_array')) {
    function is_multi_array($array = null) {
        if(is_array($array)) {
            if(is_array(current($array))) {
                /* First element value is array - return true */
                return true;
            }
        }
        return false;
    }
}
?>