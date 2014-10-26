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
 * Table tl_calendar_events
 */

if($GLOBALS['TL_CONFIG']['rms_active'])
{
	$this->loadLanguageFile('tl_default');	
    /**
    * change dca from tl_calendar_events
    */
    $GLOBALS['TL_DCA']['tl_calendar_events']['config']['onload_callback'][] = array('tl_calendar_events_rms','addRmsFields');
    $GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations']['toggle']['button_callback'] = array('tl_calendar_events_rms','toggleIcon');

    /**
    * add operation show Preview
    */
    $GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations']['showPreview'] = array
	(
		'label'               => &$GLOBALS['TL_LANG']['tl_calendar_events']['show_preview'],
		'href'                => 'key=showPreview',
		'class'               => 'browser_preview',
		'icon'                => 'page.gif',
		'attributes'          => 'target="_blank"',
		'button_callback' => array('tl_calendar_events_rms','checkPreviewIcon')
	);
}

/**
* Fields
*/
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['ptable']['ignoreDiff'] = true;

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['rms_first_save'] = array
(
	'sql'					  => "char(1) NOT NULL default ''",
    'ignoreDiff'            => true,		
);
$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['rms_new_edit'] = array
(
    'sql'                     => "char(1) NOT NULL default ''",
    'ignoreDiff'            => true,        
);

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['rms_ref_table'] = array
(
	'sql'					  => "char(55) NOT NULL default ''",
	'ignoreDiff'			=> true,
);

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['rms_notice'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['MSC']['rms_notice'],
	'exclude'                 => true,
	'search'                  => true,
	'inputType'               => 'textarea',
	'eval'                    => array('mandatory'=>false, 'rte'=>false),
	'sql'					  => "longtext NULL"
);
$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['rms_release_info'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['MSC']['rms_release_info'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'sql'					  => "char(1) NOT NULL default ''",
    'ignoreDiff'            => true,		
	'save_callback' => array
	(
		array('SvenRhinow\rms\rmsHelper', 'sendEmailInfo')
	)
);


/**
 * Class tl_calendar_events_rms
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2013
 * @author     Leo Feyer <https://contao.org>
 * @package    Controller
 */
class tl_calendar_events_rms extends Backend
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
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
		$this->import('Database');

		//test rms
		$rmsObj = $this->Database->prepare('SELECT * FROM `tl_rms` WHERE `ref_table`=? AND `ref_id`=?')
					 ->execute('tl_calendar_events',$row['id']);

		if($rmsObj->numRows > 0)
		{
			return '';
		}
		else
		{
			if (strlen($this->Input->get('tid')))
			{
				$this->toggleVisibility($this->Input->get('tid'), ($this->Input->get('state') == 1));
				$this->redirect($this->getReferer());
			}

			// Check permissions AFTER checking the tid, so hacking attempts are logged
			if (!$this->User->isAdmin && !$this->User->hasAccess('tl_calendar_events::published', 'alexf'))
			{
				return '';
			}

			$href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

			if (!$row['published'])
			{
				$icon = 'invisible.gif';
			}

			return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
		}
   }

    /**
     * Disable/enable a user group
     * @param integer
     * @param boolean
     */
    public function toggleVisibility($intId, $blnVisible)
    {
	    // Check permissions to edit
	    $this->Input->setGet('id', $intId);
	    $this->Input->setGet('act', 'toggle');

	    // Check permissions to publish
	    if (!$this->User->isAdmin && !$this->User->hasAccess('tl_calendar_events::published', 'alexf'))
	    {
		    $this->log('Not enough permissions to publish/unpublish event ID "'.$intId.'"', 'tl_calendar_events toggleVisibility', TL_ERROR);
		    $this->redirect('contao/main.php?act=error');
	    }

	    $this->createInitialVersion('tl_calendar_events', $intId);

	    // Trigger the save_callback
	    if (is_array($GLOBALS['TL_DCA']['tl_calendar_events']['fields']['published']['save_callback']))
	    {
		    foreach ($GLOBALS['TL_DCA']['tl_calendar_events']['fields']['published']['save_callback'] as $callback)
		    {
			    $this->import($callback[0]);
			    $blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
		    }
	    }

	    // Update the database
	    $this->Database->prepare("UPDATE tl_calendar_events SET tstamp=". time() .", published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
				       ->execute($intId);

	    $this->createNewVersion('tl_calendar_events', $intId);

	    // Update the RSS feed (for some reason it does not work without sleep(1))
	    sleep(1);
	    $this->import('Calendar');
	    $this->Calendar->generateFeed(CURRENT_ID);
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
        $this->import('SvenRhinow\rms\rmsHelper', 'rmsHelper');
        $previewLink = $this->rmsHelper->getPreviewLink($row['id'],'tl_calendar_events');

        //test rms
        $rmsObj = $this->Database->prepare('SELECT * FROM `tl_rms` WHERE `ref_table`=? AND `ref_id`=?')
				 ->execute('tl_calendar_events',$row['id']);

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
