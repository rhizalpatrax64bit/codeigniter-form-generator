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


}
?>