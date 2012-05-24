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
     * Get Calendar Value
     *
     * Checks the value of a calendar.  If either
     * day/month/year is not set, it returns false
     * (which, if is required, will then fail.  If
     * an illegal date is specified (eg, 31st Feb)
     * then it will throw an error for said illegal
     * date.
     *
     * @param string $name
     * @param array $arrData
     * @return string/false
     */
    protected function _input_form_calendar($name, array $arrData = array()) {
        $objCI = &get_instance();
        
        /* Do it in Y-m-d format */
        $arrFields = array(
            'year' => $objCI->config->item('form_calendar_year'),
            'month' => $objCI->config->item('form_calendar_month'),
            'day' => $objCI->config->item('form_calendar_day'),
        );
        
        $arrValue = array();
        $arrCheck = array();
        foreach($arrFields as $key => $field) {
            $field = $name.$field;
            $val = $objCI->input->post($field, true);
            $arrCheck[$key] = 0;
            if($val > 0) {
                if($val < 10 && strlen($val) < 2) { $val = "0{$val}"; }
                $arrValue[$key] = $val;
            } else {
                /* Default value */
                $arrValue[$key] = 0;
            }
        }
        
        /* Is it required */
        $required = false;
        if(array_key_exists('rules', $arrData) && preg_match('/required/', $arrData['rules'])) {
            $required = true;
        }

        /* Validate the date */
        if($required === false && ($arrValue == $arrCheck)) {
            /* Empty and unrequired */
            $value = implode('-', $arrValue);
            $_POST[$name] = $value;
            
            return $value;
            
        } elseif(checkdate($arrValue['month'], $arrValue['day'], $arrValue['year'])) {
            /* Valid date - update the $_POST so the run() function can see the value */
            $value = implode('-', $arrValue);
            $_POST[$name] = $value;
            
            $error = false;
            
            /* Check it's not before min or after max date */
            if(array_key_exists('minDate', $arrData) && strtotime($value) < strtotime($arrData['minDate'])) {
                /* Date before min date */
                $error = 'form_before_min_date';
                $date = $arrData['minDate'];
            }
            
            if(array_key_exists('maxDate', $arrData) && strtotime($value) > strtotime($arrData['maxDate'])) {
                $error = 'form_after_max_date';
                $date = $arrData['maxDate'];
            }

            /* Is there an error present */
            if($error !== false) {
                /* Error - return false */
                $objCI->lang->load('form_validation');
                $label = array_key_exists('label', $arrData) && !empty($arrData['label']) ? $arrData['label'] : $name;
                $message = str_replace(array('%s', '%d'), array($label, $date), $objCI->lang->line($error));
                $this->set_error($name, $message);
                return false;
            } else {
                /* Return as a string */
                return $value;
            }
        } else {
            /* Invalid date */
            $objCI->lang->load('form_validation');
            $label = array_key_exists('label', $arrData) && !empty($arrData['label']) ? $arrData['label'] : $name;
            $message = str_replace('%s', $label, $objCI->lang->line('form_invalid_date'));
            $this->set_error($name, $message);
            return false;
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
     * Form Calendar
     *
     * Outputs a calendar box.  The date should be
     * in Y-m-d format and set to 'value' key.  If you
     * want to specify the start year to something,
     * set it in $first_year
     *
     * @param array $arrForm
     * @param string $first_year
     * @param string $last_year
     * @return string
     */
    protected function _output_form_calendar($type, array $arrForm = null) {
        $objCI = &get_instance();
        
        /* Check for min/max date in Y-m-d format */
        $minDate = array_key_exists('minDate', $arrForm) && preg_match('/^(\d{4})\-(\d{2})\-(\d{2})$/', $arrForm['minDate']) ? $arrForm['minDate'] : null;
        $maxDate = array_key_exists('maxDate', $arrForm) && preg_match('/^(\d{4})\-(\d{2})\-(\d{2})$/', $arrForm['maxDate']) ? $arrForm['maxDate'] : null;

        if(is_null($minDate)) {
            $first_year = $objCI->config->item('form_calendar_start_year');
            if($first_year === false) { $first_year = date('Y'); }
        }
        else { $first_year = date('Y', strtotime($minDate)); }

        if(is_null($maxDate)) {
            $last_year = date('Y');
            
            /* Set max date as final date of this year */
            $maxDate = $last_year.'-12-31';
        }
        else { $last_year = date('Y', strtotime($maxDate)); }

        /* Loads */
        $objCI->load->helper('date');
        $objCI->load->library('calendar');

        /* Build the value */
        $value = $arrForm['value'];
        $arrValue = explode('-', $value);
        $set_day = isset($arrValue[2]) ? $arrValue[2] : 0;
        $set_month = isset($arrValue[1]) ? $arrValue[1] : 0;
        $set_year = isset($arrValue[0]) ? $arrValue[0] : 0;

        /* Get the select name */
        $name = $arrForm['name'];
        $day_name = $name.$objCI->config->item('form_calendar_day');
        $month_name = $name.$objCI->config->item('form_calendar_month');
        $year_name = $name.$objCI->config->item('form_calendar_year');

        /* Day dropdown */
        $arrDay = array($objCI->lang->line('form_day_select'));
        for($day = 1; $day <= 31; $day++) {
            $arrDay[$day] = $day;
        }

        /* Month dropdown */
        $arrMonth = array($objCI->lang->line('form_month_select'));
        for($month = 1; $month <= 12; $month++) {
            $month_number = $month < 10 ? '0'.$month : $month;
            $arrMonth[$month] = $objCI->calendar->get_month_name($month_number);
        }

        /* Year dropdown */
        if($last_year == $first_year) {
            $arrYear = array($first_year => $first_year);
        } else {
            $arrYear = array($objCI->lang->line('form_year_select'));
            for($year = $last_year; $year >= $first_year; $year--) {
                $arrYear[$year] = $year;
            }
        }
        
        /* Get day/month names */
        $arrLang = array(
            'days' => array(
                'cal_su', 'cal_mo', 'cal_tu', 'cal_we', 'cal_th', 'cal_fr', 'cal_sa',
            ),
            'months' => array(
                'cal_jan', 'cal_feb', 'cal_mar', 'cal_apr', 'cal_may', 'cal_jun',
                'cal_jul', 'cal_aug', 'cal_sep', 'cal_oct', 'cal_nov', 'cal_dec',
            ),
        );

        /* Check if we've got anything in the input class */
        $arrPost = array(
            'day' => $day_name,
            'month' => $month_name,
            'year' => $year_name,
        );
        
        foreach($arrPost as $var => $post) {
            $var = 'set_'.$var;
            $post = $objCI->input->post($post, true);
            if($post !== false) {
                ${$var} = $post;
            } elseif(${$var} != 0) {
            } else {
                ${$var} = 0;
            }
        }

        /* Open the form */
        $form = '<div class="form_calendar';
        if(array_key_exists('class', $arrForm)) { $form .= " {$arrForm['class']}"; }
        $form .= '" id="'.$name.'">';
        
        $form .= form_dropdown($day_name, $arrDay, $set_day, 'id="'.$day_name.'" class="calendar_day"');
        $form .= form_dropdown($month_name, $arrMonth, $set_month, 'id="'.$month_name.'" class="calendar_month"');
        $form .= form_dropdown($year_name, $arrYear, $set_year, 'id="'.$year_name.'" class="calendar_year"');

        /* JS Selector - add by default */
        if(array_key_exists('select', $arrForm) === false || $arrForm['select'] === true) {
            $form .= '<input type="hidden" class="calendar_selector" />';
        }

        /* MinDate */
        $form .= '<input type="hidden" value="'.$minDate.'" class="calendar_minDate" />';

        /* MinDate */
        $form .= '<input type="hidden" value="'.$maxDate.'" class="calendar_maxDate" />';
        
        /* Output the days and months */
        foreach($arrLang as $type => $lang) {
            $form .= '<input type="hidden" class="form_calendar_lang_'.$type.'" value="';
            foreach($lang as $id => $name) {
                if($id != 0) { $form .= ','; }
                $form .= $objCI->lang->line($name);
            }
            $form .= '" />';
        }

        /* Close the form */
        $form .= '</div>';

        return $form;
    }
    
    
    
    
    
    
    
    
    
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