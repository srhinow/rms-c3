<?php
/**
 * PHP version 5
 * @copyright  Sven Rhinow Webentwicklung 2014 <http://www.sr-tag.de>
 * @author     Stefan Lindecke  <stefan@ktrion.de>
 * @author     Sven Rhinow <kservice@sr-tag.de>
 * @package    rms for Contao 3 (Release Management System)
 * @license    LGPL
 */


/**
 * Table tl_article
 */
if($GLOBALS['TL_CONFIG']['rms_active'])
{

	$this->loadLanguageFile('tl_default');

    /**
    * change dca from tl_article
    */
	$GLOBALS['TL_DCA']['tl_article']['config']['onload_callback'][] = array('tl_article_rms','addRmsFields');
	$GLOBALS['TL_DCA']['tl_article']['config']['onrestore_callback'][] = array('tl_article_rms','onRestoreCallback');
	$GLOBALS['TL_DCA']['tl_article']['list']['operations']['editheader']['href'] = 'act=edit&table=tl_article';	

	/**
	* Fields
	*/
	$GLOBALS['TL_DCA']['tl_article']['fields']['ptable']['ignoreDiff'] = true;
	
	$GLOBALS['TL_DCA']['tl_article']['fields']['rms_first_save'] = array
	(
		'sql'					  => "char(1) NOT NULL default ''",
		'ignoreDiff'			=> true,
	);

	$GLOBALS['TL_DCA']['tl_article']['fields']['rms_new_edit'] = array
	(
		'sql'					  => "char(1) NOT NULL default ''",
		'ignoreDiff'			=> true,
	);
	
	$GLOBALS['TL_DCA']['tl_article']['fields']['rms_ref_table'] = array
	(
		'sql'					  => "char(55) NOT NULL default ''",
		'ignoreDiff'			=> true,
	);

    $GLOBALS['TL_DCA']['tl_article']['fields']['rms_notice'] = array
	(
		'label'                   => &$GLOBALS['TL_LANG']['MSC']['rms_notice'],
		'exclude'                 => true,
		'search'                  => true,
		'inputType'               => 'textarea',
		'eval'                    => array('mandatory'=>false, 'rte'=>FALSE),
		'sql'					  => "longtext NULL"
	);
    $GLOBALS['TL_DCA']['tl_article']['fields']['rms_release_info'] = array
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
 * Class tl_rms_calendar_events
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2013
 * @author     Leo Feyer <https://contao.org>
 * @package    Controller
 */
class tl_article_rms extends \Backend
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
		parent::__construct();
    }

	/**
	* add RMS-Fields in menny content-elements (DCA)
	* @var object
	*/
	public function addRmsFields(\DataContainer $dc)
	{
	    $strTable = $this->Input->get("table");

	    //defined blacklist palettes
		$rm_palettes_blacklist = array('__selector__');

	    //add Field in meny content-elements
		foreach($GLOBALS['TL_DCA']['tl_article']['palettes'] as $name => $field)
        {
			if(in_array($name,$rm_palettes_blacklist)) continue;

			$GLOBALS['TL_DCA']['tl_article']['palettes'][$name] .=  ';{rms_settings_legend:hide},rms_protected;{rms_legend:hide},rms_notice,rms_release_info';
        }

	}

}
