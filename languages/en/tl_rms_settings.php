<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_rms_settings']['control_group'] = array('Group with release authorization', 'Enter the user group to which shall have a release authorization.');
$GLOBALS['TL_LANG']['tl_rms_settings']['release_tables'] = array('Tables are to be considered', 'Enter the tables to which shall have a certification authorization.');
$GLOBALS['TL_LANG']['tl_rms_settings']['whitelist_domains'] = array('pages-trees are to be considered', 'all subpages of selected home pages to include in the release.');
$GLOBALS['TL_LANG']['tl_rms_settings']['sender_email']         = array('Receiving e-mail', 'You can enter one or more email addresses Kommo-separated.');
$GLOBALS['TL_LANG']['tl_rms_settings']['prevjump_newsletter']         = array('Newsletter preview page', 'Select the page with the newsletter-detail module.');
$GLOBALS['TL_LANG']['tl_rms_settings']['prevjump_news']         = array('News preview site', 'Select the page with the news-detail module.');
$GLOBALS['TL_LANG']['tl_rms_settings']['prevjump_calendar_events'] = array('event preview site', 'Select the page with the event-detail module.');

