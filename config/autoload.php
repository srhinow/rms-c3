<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Rms
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'SvenRhinow',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'SvenRhinow\rms\rmsVersions'        	=> 'system/modules/rms/classes/rmsVersions.php',
	'SvenRhinow\rms\rmsAjax'				=> 'system/modules/rms/classes/rmsAjax.php',
	'SvenRhinow\rms\rmsHelper'          	=> 'system/modules/rms/classes/rmsHelper.php',
	'SvenRhinow\rms\rmsDefaultCallbacks'	=> 'system/modules/rms/classes/rmsDefaultCallbacks.php',

	// Modules
	'Contao\ModuleNewsReaderRMS'        	=> 'system/modules/rms/modules/ModuleNewsReaderRMS.php',

	// Drivers
	'Contao\DC_rmsTable'                 	=> 'system/modules/rms/drivers/DC_rmsTable.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'be_rmsdiff' 						=> 'system/modules/rms/templates',
	'mail_review_question' 				=> 'system/modules/rms/templates',
));
