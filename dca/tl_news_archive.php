<?php

/**
 * Contao Open Source CMS
 *
 * PHP version 5
 * @copyright  Sven Rhinow Webentwicklung 2014 <http://www.sr-tag.de>
 * @author     Stefan Lindecke  <stefan@ktrion.de>
 * @author     Sven Rhinow <kservice@sr-tag.de>
 * @package    rms for Contao 3 (Release Management System)
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

/**
 * Table tl_news_archive
 */
if($GLOBALS['TL_CONFIG']['rms_active'])
{

	$this->loadLanguageFile('tl_default');

	$GLOBALS['TL_DCA']['tl_news_archive']['config']['onload_callback'][] = array('tl_news_archive_rms','addRmsFields');
	$GLOBALS['TL_DCA']['tl_news_archive']['list']['operations']['editheader']['href'] = 'act=edit&table=tl_news_archive';
    
	// Palettes
	$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['__selector__'][] = 'rms_protected';

	// Subpalettes
	$GLOBALS['TL_DCA']['tl_news_archive']['subpalettes']['rms_protected'] = 'rms_master_member,rms_preview_jumpTo';
    
	// Fields
	$GLOBALS['TL_DCA']['tl_news_archive']['fields']['rms_protected'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['MSC']['rms_protected'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true),
			'sql'                     => "char(1) NOT NULL default ''"
	);

    $GLOBALS['TL_DCA']['tl_news_archive']['fields']['rms_master_member'] = array
    (
		'label'                   => &$GLOBALS['TL_LANG']['MSC']['rms_master_member'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'foreignKey'              => 'tl_user.name',
		'eval'                    => array('mandatory'=>true,'chosen'=>true),
		'sql'                     => "int(10) unsigned NOT NULL default '0'",
		'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
    );

	$GLOBALS['TL_DCA']['tl_news_archive']['fields']['rms_preview_jumpTo'] = array
	(
		'label'                   => &$GLOBALS['TL_LANG']['MSC']['rms_preview_jumpTo'],
		'exclude'                 => true,
		'inputType'               => 'pageTree',
		'foreignKey'              => 'tl_page.title',
		'eval'                    => array('mandatory'=>false, 'fieldType'=>'radio'),
		'sql'                     => "int(10) unsigned NOT NULL default '0'",
		'relation'                => array('type'=>'hasOne', 'load'=>'eager')
	);
	
    $GLOBALS['TL_DCA']['tl_news_archive']['fields']['ptable']['ignoreDiff'] = true;
    
    $GLOBALS['TL_DCA']['tl_news_archive']['fields']['rms_first_save'] = array
	(
		'sql'					  => "char(1) NOT NULL default ''",
		'ignoreDiff'			=> true,
	);

	$GLOBALS['TL_DCA']['tl_news_archive']['fields']['rms_new_edit'] = array
	(
		'sql'					  => "char(1) NOT NULL default ''"
	);
	
	$GLOBALS['TL_DCA']['tl_news_archive']['fields']['rms_ref_table'] = array
	(
		'sql'					  => "char(55) NOT NULL default ''",
		'ignoreDiff'			=> true,
	);

    $GLOBALS['TL_DCA']['tl_news_archive']['fields']['rms_notice'] = array
	(
		'label'                   => &$GLOBALS['TL_LANG']['MSC']['rms_notice'],
		'exclude'                 => true,
		'search'                  => true,
		'inputType'               => 'textarea',
		'eval'                    => array('mandatory'=>false, 'rte'=>FALSE),
		'sql'					  => "longtext NULL"
	);

    $GLOBALS['TL_DCA']['tl_news_archive']['fields']['rms_release_info'] = array
	(
		'label'                   => &$GLOBALS['TL_LANG']['MSC']['rms_release_info'],
		'exclude'                 => true,
		'inputType'               => 'checkbox',
		'sql'					  => "char(1) NOT NULL default ''",
		'ignoreDiff'			=> true,
		'save_callback' => array
		(
			array('SvenRhinow\rms\rmsHelper', 'sendEmailInfo')
		)
	);
	
}

/**
 * Class tl_news_archive_rms
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @package    Controller
 */
class tl_news_archive_rms extends \Backend
{
	/**
	* add RMS-Fields in enny news archive palettes (DCA)
	* @var object
	*/
	public function addRmsFields(\DataContainer $dc)
	{
	    //defined blacklist palettes
		$rm_palettes_blacklist = array('__selector__');

	    //add Field in meny content-elements
		foreach($GLOBALS['TL_DCA']['tl_news_archive']['palettes'] as $name => $field)
        {
			if(in_array($name,$rm_palettes_blacklist)) continue;

			$GLOBALS['TL_DCA']['tl_news_archive']['palettes'][$name] .=  ';{rms_settings_legend:hide},rms_protected;{rms_legend:hide},rms_notice,rms_release_info';
        }

	}	
}
