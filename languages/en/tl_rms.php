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
 * @filesource
 */

/**
* global Operation
*/
$GLOBALS['TL_LANG']['tl_rms']['show_preview'] = array('Export','Rechnungen und deren Posten in CSV-Dateien exportieren.');
  
/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_rms']['tstamp']     = array('Release-Date', 'Date of release request.');
$GLOBALS['TL_LANG']['tl_rms']['ref_table']     = array('modified table', 'referenced table the amendment.');
$GLOBALS['TL_LANG']['tl_rms']['ref_author']     = array('Author of the change', '');
$GLOBALS['TL_LANG']['tl_rms']['ref_id']     = array('ID', 'Unique database ID of the changed record');
$GLOBALS['TL_LANG']['tl_rms']['ref_notice']     = array('Release Note', 'The note is the person responsible to assist the changes.');
$GLOBALS['TL_LANG']['tl_rms']['data']     = array('changed data', 'serialized data of the data record');
$GLOBALS['TL_LANG']['tl_rms']['status']     = array('status', 'indicates whether this release was already answered.');
$GLOBALS['TL_LANG']['tl_rms']['region'][0]   =  'region';
$GLOBALS['TL_LANG']['tl_rms']['preview_link'][0]   =  'Link to preview';
$GLOBALS['TL_LANG']['tl_rms']['last_edit'][0]   =  'last edit';
$GLOBALS['TL_LANG']['tl_rms']['status_options'] = array('0'=>'unfinished','1'=>'edited');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_rms']['settings']     = array('Release Management-Settings', '');
$GLOBALS['TL_LANG']['tl_rms']['acknowledge']     = array('Release Change', 'Release this change.');
$GLOBALS['TL_LANG']['tl_rms']['delete']     = array('delete change', 'delete this change.');
$GLOBALS['TL_LANG']['tl_rms']['edit']       = array('edit content', 'edit this content');
$GLOBALS['TL_LANG']['tl_rms']['show_diff']       = array('Show difference', 'View the processed difference.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_content']['rm_legend']      = 'Release Management';

/**
* Bereiche
*/
$GLOBALS['TL_LANG']['tl_rms']['sessions']['tl_article'] = 'Article :: element';
$GLOBALS['TL_LANG']['tl_rms']['sessions']['tl_content'] = 'Article :: element';
$GLOBALS['TL_LANG']['tl_rms']['sessions']['tl_newsletter'] = 'Newsletter :: element';
$GLOBALS['TL_LANG']['tl_rms']['sessions']['tl_calendar_events'] = 'Event :: element';
$GLOBALS['TL_LANG']['tl_rms']['sessions']['tl_news'] = 'News :: element';

/**
* Other Text
*/
$GLOBALS['TL_LANG']['tl_rms']['diff_new_content'] = '<h5>The content has been re-created.</h5> <p>Therefore, there is no version for comparison.</p>';
$GLOBALS['TL_LANG']['tl_rms']['info_new_edit'] = '*new created*';
