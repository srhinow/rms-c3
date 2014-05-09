<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
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

?>
