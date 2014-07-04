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
 * Table tl_newsletter
 */
require_once(TL_ROOT.'/system/config/localconfig.php');

if($GLOBALS['TL_CONFIG']['rms_active'])
{
    $this->loadLanguageFile('tl_default');  
    
    $GLOBALS['TL_DCA']['tl_newsletter']['config']['onload_callback'][] = array('tl_newsletter_rms','addRmsFields');
    $GLOBALS['TL_DCA']['tl_newsletter']['list']['operations']['send']['button_callback'] = array('tl_newsletter_rms','checkSendIcon');

    /**
    * add operation show Preview
    */
    $GLOBALS['TL_DCA']['tl_newsletter']['list']['operations']['showPreview'] = array
	(
		'label'               => &$GLOBALS['TL_LANG']['tl_calendar_events']['show_preview'],
		'href'                => 'key=showPreview',
		'class'               => 'browser_preview',
		'icon'                => 'page.gif',
		'attributes'          => 'target="_blank"',
		'button_callback' => array('tl_newsletter_rms','checkPreviewIcon')
	);
};

/**
* Fields
*/
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['ptable']['ignoreDiff'] = true;

$GLOBALS['TL_DCA']['tl_newsletter']['fields']['rms_first_save'] = array
(
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_newsletter']['fields']['rms_new_edit'] = array
(
    'sql'                     => "char(1) NOT NULL default ''",
    'ignoreDiff'            => true,        
);

$GLOBALS['TL_DCA']['tl_newsletter']['fields']['rms_ref_table'] = array
(
    'sql'                     => "char(55) NOT NULL default ''",
    'ignoreDiff'            => true,
);

$GLOBALS['TL_DCA']['tl_newsletter']['fields']['rms_notice'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['MSC']['rms_notice'],
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'textarea',
    'eval'                    => array('mandatory'=>false, 'rte'=>FALSE),
    'sql'                     => "longtext NULL"        
);

$GLOBALS['TL_DCA']['tl_newsletter']['fields']['rms_release_info'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['MSC']['rms_release_info'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'sql'                     => "char(1) NOT NULL default ''",
    'ignoreDiff'            => true,
    'save_callback' => array
    (
        array('SvenRhinow\rms\rmsHelper', 'sendEmailInfo')
    )
);


/**
 * Class tl_newsletter_rms
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2013
 * @author     Leo Feyer <https://contao.org>
 * @package    Controller
 */
class tl_newsletter_rms extends \Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
	parent::__construct();
	$this->import('BackendUser', 'User');
    }

    /**
     * Return the "toggle send-button"
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function checkSendIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $this->import('Database');

        //test rms
        $rmsObj = $this->Database->prepare('SELECT * FROM `tl_rms` WHERE `ref_table`=? AND `ref_id`=?')
				 ->execute('tl_newsletter',$row['id']);
        if($rmsObj->numRows > 0) return '';
        else return '<a href="'.$this->addToUrl('id='.$row['id'].'&'.$href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';

    }
    /**
     * Return the "toggle preview-button"
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function checkPreviewIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $this->import('Database');
        $this->import('SvenRhinow\rms\rmsHelper','rmsHelper');
        $previewLink = $this->rmsHelper->getPreviewLink($row['id'],'tl_newsletter');

        //test rms
        $rmsObj = $this->Database->prepare('SELECT * FROM `tl_rms` WHERE `ref_table`=? AND `ref_id`=?')
				 ->execute('tl_newsletter',$row['id']);
        if($rmsObj->numRows > 0) return '<a href="'.$previewLink.'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
        else return '';

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
        foreach($GLOBALS['TL_DCA'][$strTable]['palettes'] as $name => $field)
        {
            if(in_array($name,$rm_palettes_blacklist)) continue;

            $GLOBALS['TL_DCA'][$strTable]['palettes'][$name] .=  ';{rms_legend:hide},rms_notice,rms_release_info';
        }

    }
}
