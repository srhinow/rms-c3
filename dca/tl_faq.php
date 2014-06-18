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
 * Table tl_faq
 */
require_once(TL_ROOT.'/system/config/localconfig.php');

if($GLOBALS['TL_CONFIG']['rms_active'])
{
    $this->loadLanguageFile('tl_default');  

    $GLOBALS['TL_DCA']['tl_faq']['config']['onload_callback'][] = array('tl_faq_rms','addRmsFields');

    /**
    * Fields
    */
    $GLOBALS['TL_DCA']['tl_faq']['fields']['ptable']['ignoreDiff'] = true;
    
    $GLOBALS['TL_DCA']['tl_faq']['fields']['rms_first_save'] = array
    (
        'sql'                     => "char(1) NOT NULL default ''",
        'ignoreDiff'            => true,
    );

    $GLOBALS['TL_DCA']['tl_faq']['fields']['rms_new_edit'] = array
    (
        'sql'                     => "char(1) NOT NULL default ''"
    );
    
    $GLOBALS['TL_DCA']['tl_faq']['fields']['rms_ref_table'] = array
    (
        'sql'                     => "char(55) NOT NULL default ''",
        'ignoreDiff'            => true,
    );

    $GLOBALS['TL_DCA']['tl_faq']['fields']['rms_notice'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['MSC']['rms_notice'],
        'exclude'                 => true,
        'search'                  => true,
        'inputType'               => 'textarea',
        'eval'                    => array('mandatory'=>false, 'rte'=>FALSE),
        'sql'                     => "longtext NULL"
    );

    $GLOBALS['TL_DCA']['tl_faq']['fields']['rms_release_info'] = array
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
};

/**
 * Class tl_faq_rms
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2013
 * @author     Leo Feyer <https://contao.org>
 * @package    Controller
 */
class tl_faq_rms extends \Backend
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
    
    /**
    * custom modify the rms-Preview
    * used from rmsHelper->modifyForPreview() -> is a parseTemplate->HOOK
    * @param object
    * @param array
    * @return object
    */
    public function modifyForPreview($templObj, $newArr)
    {
        global $objPage;

        $origObj = clone $templObj;

        if(is_array($newArr) && count($newArr) > 0)
        {
            foreach($newArr as $k => $v)
            {               
                $templObj->$k = $v;
            }
            
            //author
            $objAuthor = $this->Database->prepare('SELECT * FROM `tl_user` WHERE `id`=?')->limit(1)->execute($templObj->author);
            $this->Template->info = sprintf($GLOBALS['TL_LANG']['MSC']['faqCreatedBy'], \Date::parse($templObj->dateFormat, $templObj->tstamp), $objAuthor->name);

        }
        return $templObj;
    }    
}
