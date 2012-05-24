<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * MY_Form_validation
 *
 * This is where any coding is done that is
 * project-specific.  Please make any custom
 * changes here and not in Form_generator.php
 * so you can keep the software up-to-date.
 *
 * @author Simon Emms <simon@simonemms.com>
 * @since 04-Dec-2011
 */

/* Load the Form_generation class - this extends the CI_Form_validation class */
require_once('Form_generator'.EXT);

class MY_Form_validation extends Form_generator {


    /* Fields that are ignored from POST */
    protected $_arrIgnore = array(
    );
    
    
    
    
    
    
    
    
    
    /**
     * Output Form Checkbox Multi
     * 
     * Outputs multiple checkboxes for one field
     * 
     * @param string $type
     * @param array $arrForm
     * @return string
     */
    protected function _output_form_checkbox_multi($type, array $arrForm = array()) {
        
        if(!array_key_exists('options', $arrForm) || !is_array($arrForm['options']) || count($arrForm['options']) <= 0) {
            /* Throw error - need options */
            show_error("The {$type} form requires options");
        }
        
        
        /* Values */
        $name = $arrForm['name'];
        $selected = $arrForm['value'];
        if(!is_array($selected)) { $selected = array($selected); }
        
        /* Do the form */
        $form = form_fieldset(null, 'id="'.$arrForm['id'].'"');
        
        $x = 0;
        foreach($arrForm['options'] as $value => $label) {
            $id = $name.'_'.$x;
            
            /* Convert value to a string to check - if blank and value an int, it gets false positive */
            $checked = in_array((string) $value, $selected);
            
            $class = 'fieldset_row';
            if($x == 0) { $class .= ' first'; }
            
            $form .= '<div class="'.$class.'">'."\n";
            $form .= form_label($label, $id)."\n";
            $form .= '<div class="fieldset_line">'."\n";
            $form .= form_checkbox($name.'[]', $value, $checked, 'id="'.$id.'" class="'.$name.'"')."\n";
            $form .= '</div>'."\n";
            $form .= '</div>'."\n";
            
            $x++;
        }
        
        $form .= form_fieldset_close();
        
        return $form;
        
    }
    
    
    
    
    
    
    
    
    /**
     * Output Form Radio Multi
     * 
     * Identical to checkbox - just uses radio buttons
     * instead
     * 
     * @param string $type
     * @param array $arrForm
     * @return string
     */
    protected function _output_form_radio_multi($type, array $arrForm = array()) {
        
        if(!array_key_exists('options', $arrForm) || !is_array($arrForm['options']) || count($arrForm['options']) <= 0) {
            /* Throw error - need options */
            show_error("The {$type} form requires options");
        }
        
        /* Values */
        $name = $arrForm['name'];
        $selected = $arrForm['value'];
        
        /* Do the form */
        $form = form_fieldset(null, 'id="'.$arrForm['id'].'"');
        
        $x = 0;
        foreach($arrForm['options'] as $value => $label) {
            $id = $name.'_'.$x;
            
            /* Convert value to a string to check - if blank and value an int, it gets false positive */
            $checked = ((string) $selected == (string) $value);
            
            $class = 'fieldset_row';
            if($x == 0) { $class .= ' first'; }
            
            $form .= '<div class="'.$class.'">'."\n";
            $form .= form_label($label, $id)."\n";
            $form .= '<div class="fieldset_line">'."\n";
            $form .= form_radio($name, $value, $checked, 'id="'.$id.'" class="'.$name.'"')."\n";
            $form .= '</div>'."\n";
            $form .= '</div>'."\n";
            
            $x++;
        }
        
        $form .= form_fieldset_close();
        
        return $form;
        
    }


}
?>