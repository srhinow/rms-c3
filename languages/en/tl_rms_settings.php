<?php

/**
 * Contao Open Source CMS
 *
 * PHP version 5
 * @copyright  Sven Rhinow Webentwicklung 2012 <http://www.sr-tag.de>
 * @author     Stefan Lindecke  <stefan@ktrion.de>
 * @author     Sven Rhinow <kservice@sr-tag.de> 
 * @package    rms (Release Management System)
 * @license    LGPL 
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_rms_settings']['control_group'] = array('Group with release authorization', 'Enter the user group to which shall have a release authorization.');
$GLOBALS['TL_LANG']['tl_rms_settings']['fallback_master_member']         = array('Fallback RMS editor', 'If in any area, although rms was enabled but not associated rms editor exists, this will be notified here.');
$GLOBALS['TL_LANG']['tl_rms_settings']['extent_emailto']         = array('additional recipient e-mail addresses', 'Here you may include one or more email addresses separated by commas. This email address will also be notified of all changes. The field can be left blank.');
$GLOBALS['TL_LANG']['tl_rms_settings']['ignore_fields']         = array('to ignore fields', 'Enter the field name (separated by commas) that are to be ignored by the rms module. The field name must be from the database (eg rms_first_save');
$GLOBALS['TL_LANG']['tl_rms_settings']['ignore_content_types']         = array('to ignore content elements', 'Enter the field name (separated by commas) that are to be ignored by the rms module. The field name must be from the database (eg colsetStart,colsetPart,colsetEnd');



