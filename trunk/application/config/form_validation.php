<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * form_validation
 *
 * @author Simon Emms <simon@simonemms.com>
 */

$config['form_validation'] = array(
    'required_class' => 'required',
    'row_class' => 'row',
    'line_class' => 'line',
    'hideJS_class' => 'hideJS',
    'hidden_class' => 'hidden',
    'error_class' => 'error',
    'label_format' => '%l', /* %l becomes the text, anything else is printed as written (eg, a trailing colon) */
    'required_flag' => '%l &#42;', /* %l becomes the text, anything else is to show it's required (eg, *) */
    'help_class' => 'help_label',
    'buttons' => array(
        'class' => 'action-menu',
        'wrapper_open' => 'ul',
        'wrapper_close' => '/ul',
        'delimiter_open' => 'li',
        'delimiter_close' => '/li',
    ),
    'errors' => array(
        'class' => 'error-wrapper',
        'wrapper_open' => 'ul',
        'wrapper_close' => '/ul',
        'delimiter_open' => 'li',
        'delimiter_close' => '/li',
    ),
);

$config['form_calendar_start_year'] = date('Y') - 10;
$config['form_calendar_day'] = '_day';
$config['form_calendar_month'] = '_month';
$config['form_calendar_year'] = '_year';

?>