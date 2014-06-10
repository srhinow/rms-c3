<?php
/**
 * PHP version 5
 * @copyright  Sven Rhinow Webentwicklung 2014 <http://www.sr-tag.de>
 * @author     Stefan Lindecke  <stefan@ktrion.de>
 * @author     Sven Rhinow <kservice@sr-tag.de>
 * @package    rms for Contao 3 (Release Management System)
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

/**
 * Table tl_calendar
 */
if($GLOBALS['TL_CONFIG']['rms_active'])
{
	$this->loadLanguageFile('tl_default');
		
	$GLOBALS['TL_DCA']['tl_calendar']['config']['onload_callback'][] = array('tl_calendar_rms','addRmsFields');
	$GLOBALS['TL_DCA']['tl_calendar']['list']['operations']['editheader']['href'] = 'act=edit&table=tl_calendar';
    
	// Palettes
	$GLOBALS['TL_DCA']['tl_calendar']['palettes']['__selector__'][] = 'rms_protected';

	// Subpalettes
	$GLOBALS['TL_DCA']['tl_calendar']['subpalettes']['rms_protected'] = 'rms_master_member';
    
	// Fields
	$GLOBALS['TL_DCA']['tl_calendar']['fields']['rms_protected'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['MSC']['rms_protected'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true),
			'sql'                     => "char(1) NOT NULL default ''"
	);
    $GLOBALS['TL_DCA']['tl_calendar']['fields']['rms_master_member'] = array
    (
		'label'                   => &$GLOBALS['TL_LANG']['MSC']['rms_master_member'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'foreignKey'              => 'tl_user.name',
		'eval'                    => array('mandatory'=>true,'chosen'=>true, 'tl_class'=>'w50'),
		'sql'                     => "int(10) unsigned NOT NULL default '0'",
		'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
    );
   $GLOBALS['TL_DCA']['tl_calendar']['fields']['ptable']['ignoreDiff'] = true;
    
    $GLOBALS['TL_DCA']['tl_calendar']['fields']['rms_first_save'] = array
	(
		'sql'					  => "char(1) NOT NULL default ''",
		'ignoreDiff'			=> true,
	);

	$GLOBALS['TL_DCA']['tl_calendar']['fields']['rms_new_edit'] = array
	(
		'sql'					  => "char(1) NOT NULL default ''"
	);

    $GLOBALS['TL_DCA']['tl_calendar']['fields']['rms_notice'] = array
	(
		'label'                   => &$GLOBALS['TL_LANG']['MSC']['rms_notice'],
		'exclude'                 => true,
		'search'                  => true,
		'inputType'               => 'textarea',
		'eval'                    => array('mandatory'=>false, 'rte'=>FALSE),
		'sql'					  => "longtext NULL"
	);

    $GLOBALS['TL_DCA']['tl_calendar']['fields']['rms_release_info'] = array
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
 * Class tl_calendar_rms
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @package    Controller
 */
class tl_calendar_rms extends \Backend
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
		foreach($GLOBALS['TL_DCA']['tl_calendar']['palettes'] as $name => $field)
        {
			if(in_array($name,$rm_palettes_blacklist)) continue;

			$GLOBALS['TL_DCA']['tl_calendar']['palettes'][$name] .=  ';{rms_settings_legend:hide},rms_protected;{rms_legend:hide},rms_notice,rms_release_info';
        }

	}
}
