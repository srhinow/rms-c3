<?php

/**
 * Contao Open Source CMS
 *
 * PHP version 5
 * @copyright  Sven Rhinow Webentwicklung 2014 <http://www.sr-tag.de>
 * @author     Stefan Lindecke  <stefan@ktrion.de>
 * @author     Sven Rhinow <kservice@sr-tag.de>
 * @package    rms for Contao 3 (Release Management System)
 * @license    LGPL
 */

/**
 * System configuration
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{rms_legend:hide},rms_active';

$GLOBALS['TL_DCA']['tl_settings']['fields']['rms_active'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['rms_active'],
			'exclude'                 => true,
			'inputType'               => 'checkbox'
		);

