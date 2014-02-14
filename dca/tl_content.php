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
 * Table tl_content
 */
if($GLOBALS['TL_CONFIG']['rms_active'])
{
    /**
    * change dca from tl_content
    */
	$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array('tl_content_rms','addRmsFields');
	$GLOBALS['TL_DCA']['tl_content']['config']['onrestore_callback'][] = array('tl_content_rms','onRestoreCallback');

	$GLOBALS['TL_DCA']['tl_content']['list']['operations']['toggle']['button_callback'] = array('tl_content_rms','toggleIcon');
	$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['child_record_callback']   = array('tl_content_rms', 'addCteType');

    /**
    * add operation show Preview
    */
    $GLOBALS['TL_DCA']['tl_content']['list']['operations']['showPreview'] = array
    (
		'label'               => &$GLOBALS['TL_LANG']['tl_content']['show_preview'],
		'href'                => 'key=showPreview',
		'class'               => 'browser_preview',
		'icon'                => 'page.gif',
		'attributes'          => 'target="_blank"',
		'button_callback' => array('tl_content_rms','checkPreviewIcon')
    );

	/**
	* Fields
	*/
	$GLOBALS['TL_DCA']['tl_content']['fields']['rms_first_save'] = array
	(
		'sql'					  => "char(1) NOT NULL default ''"
	);

	$GLOBALS['TL_DCA']['tl_content']['fields']['rms_new_edit'] = array
	(
		'sql'					  => "char(1) NOT NULL default ''"
	);

    $GLOBALS['TL_DCA']['tl_content']['fields']['rms_notice'] = array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_content']['rms_notice'],
		'exclude'                 => true,
		'search'                  => true,
		'inputType'               => 'textarea',
		'eval'                    => array('mandatory'=>false, 'rte'=>FALSE),
		'sql'					  => "longtext NULL"
	);
    $GLOBALS['TL_DCA']['tl_content']['fields']['rms_release_info'] = array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_content']['rms_release_info'],
		'exclude'                 => true,
		'inputType'               => 'checkbox',
		'sql'					  => "char(1) NOT NULL default ''",
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
class tl_content_rms extends \Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
		parent::__construct();
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
        $this->import("BackendUser","User");

		//test rms
		$rmsObj = $this->Database->prepare('SELECT * FROM `tl_rms` WHERE `ref_table`=? AND `ref_id`=?')
					 ->execute('tl_content',$row['id']);

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
			if (!$this->User->isAdmin && !$this->User->hasAccess('tl_content::invisible', 'alexf'))
			{
				return '';
			}

			$href .= '&amp;id='.$this->Input->get('id').'&amp;tid='.$row['id'].'&amp;state='.$row['invisible'];

			if ($row['invisible'])
			{
				$icon = 'invisible.gif';
			}

			return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
		}
   }


	/**
	 * Toggle the visibility of an element
	 * @param integer
	 * @param boolean
	 */
	public function toggleVisibility($intId, $blnVisible)
	{
		// Check permissions to edit
		$this->Input->setGet('id', $intId);
		$this->Input->setGet('act', 'toggle');


		// Check permissions to publish
		if (!$this->User->isAdmin && !$this->User->hasAccess('tl_content::invisible', 'alexf'))
		{
			$this->log('Not enough permissions to show/hide content element ID "'.$intId.'"', 'tl_content toggleVisibility', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		$this->createInitialVersion('tl_content', $intId);

		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_content']['fields']['invisible']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_content']['fields']['invisible']['save_callback'] as $callback)
			{
				$this->import($callback[0]);
				$blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
			}
		}

		// Update the database
		$this->Database->prepare("UPDATE tl_content SET tstamp=". time() .", invisible='" . ($blnVisible ? '' : 1) . "' WHERE id=?")
					   ->execute($intId);

		$this->createNewVersion('tl_content', $intId);
		$this->log('A new version of record "tl_content.id='.$intId.'" has been created'.$this->getParentRecords('tl_content', $intId), 'tl_content toggleVisibility()', TL_GENERAL);
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
        $previewLink = $this->rmsHelper->getPreviewLink($row['id'],'tl_content');

        //test rms
        $rmsObj = $this->Database->prepare('SELECT * FROM `tl_rms` WHERE `ref_table`=? AND `ref_id`=?')
				 ->execute('tl_content',$row['id']);

        if($rmsObj->numRows > 0) return '<a href="'.$previewLink.'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
        else return '';

    }
	/**
	 * Add the type of content element
	 * @param array
	 * @return string
	 */
	public function addCteType($arrRow)
	{
		$key = $arrRow['invisible'] ? 'unpublished' : 'published';
		$type = $GLOBALS['TL_LANG']['CTE'][$arrRow['type']][0] ?: '&nbsp;';
		$class = 'limit_height';

		// Remove the class if it is a wrapper element
		if (in_array($arrRow['type'], $GLOBALS['TL_WRAPPERS']['start']) || in_array($arrRow['type'], $GLOBALS['TL_WRAPPERS']['separator']) || in_array($arrRow['type'], $GLOBALS['TL_WRAPPERS']['stop']))
		{
			$class = '';

			if (($group = $this->getContentElementGroup($arrRow['type'])) !== null)
			{
				$type = $GLOBALS['TL_LANG']['CTE'][$group] . ' (' . $type . ')';
			}
		}

		// Add the group name if it is a single element (see #5814)
		elseif (in_array($arrRow['type'], $GLOBALS['TL_WRAPPERS']['single']))
		{
			if (($group = $this->getContentElementGroup($arrRow['type'])) !== null)
			{
				$type = $GLOBALS['TL_LANG']['CTE'][$group] . ' (' . $type . ')';
			}
		}

		// Add the ID of the aliased element
		if ($arrRow['type'] == 'alias')
		{
			$type .= ' ID ' . $arrRow['cteAlias'];
		}

		// Add the protection status
		if ($arrRow['protected'])
		{
			$type .= ' (' . $GLOBALS['TL_LANG']['MSC']['protected'] . ')';
		}
		elseif ($arrRow['guests'])
		{
			$type .= ' (' . $GLOBALS['TL_LANG']['MSC']['guests'] . ')';
		}
		elseif($arrRow['rms_new_edit'])
		{
			$type .= '<span style="color:red;"> (' . $GLOBALS['TL_LANG']['MSC']['rms_new_edit'][0] . ')</span>';
		}
		// Add the headline level (see #5858)
		if ($arrRow['type'] == 'headline')
		{
			if (is_array(($headline = deserialize($arrRow['headline']))))
			{
				$type .= ' (' . $headline['unit'] . ')';
			}
		}

		// Limit the element's height
		if (!$GLOBALS['TL_CONFIG']['doNotCollapse'])
		{
			$class .=  ' h64';
		}

		return '
<div class="cte_type ' . $key . '">' . $type . '</div>
<div class="' . trim($class) . '">
' . $this->getContentElement($arrRow['id']) . '
</div>' . "\n";
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
	* overwrite table-data and backup in tmp-table if current BackendUser a low-level-redakteur
	* @param object
	* @param object
	* @return string or object
	*/
	public function onEditCallback(\DataContainer $dc, $liveDataObj)
	{
	    $this->import("BackendUser");
        $this->import('SvenRhinow\rms\rmsHelper', 'rmsHelper');

		$userID =  (\Input::get("author")) ? \Input::get("author") :  $this->BackendUser->id;
	    $strTable = \Input::get("table");
		$contentId = \Input::get("id");

		// if (\Input::post('FORM_SUBMIT') == $strTable) return '';

		if(!$userID || !$strTable || !$contentId) return;

    	//loesche evtl Leichen in tmp-table
		$this->Database->prepare('DELETE FROM tl_rms_tmp WHERE ref_id=? AND ref_table=? AND ref_author=?')
						->execute
						(
							$contentId,
							$strTable,
							$userID
						);

		//sichere live-daten
		$set = array
		(
			'data' => serialize($liveDataObj->fetchAssoc()),
			'ref_id' => $contentId,
			'ref_table' => $strTable,
			'ref_author' => $userID,
			'tstamp' => time()
		);

	    $this->Database->prepare("INSERT INTO tl_rms_tmp %s")
			->set($set)
			->execute();

		//hole nicht freigegebene Daten von dem Redakteur fuer diesen Content
    	$objStoredData = $this->Database->prepare("SELECT data FROM tl_rms WHERE ref_id=? AND ref_table=? AND ref_author=?")
										->limit(1)
										->execute
										(
											$contentId,
											$strTable,
											$userID
										);


    	//wenn bereits eine nicht freigegebene Bearbeitung vorliegt
	    if ($objStoredData->numRows > 0)
	    {
			$rmsArr = unserialize($objStoredData->data);
			return $rmsArr;
	    }

	    return '';
	}

	/**
	* set or update a entry in rms-table
	* @param object
	*/
	public function onSubmitCallback(\DataContainer $dc)
	{

		$this->import('SvenRhinow\rms\rmsHelper', 'rmsHelper');

		$userID =  (\Input::get("author")) ? \Input::get("author") :  $this->BackendUser->id;
	    $strTable = \Input::get("table");
		$intId = \Input::get("id");

		if(!$userID || !$strTable || !$intId) return;

		// Get the currently available fields
		$arrFields = array_flip($this->Database->getFieldnames($strTable));

		//create db-field-array with new data
		foreach($arrFields as $fieldName => $colNum)
		{
			if(in_array($fieldName, array('PRIMARY','INDEX'))) continue;
			$newData[$fieldName] = $dc->activeRecord->{$fieldName};
		}

		//hole gesicherte und freigegebene Daten von dem Redakteur für diesen Content
    	$tmpDataObj = $this->Database->prepare("SELECT data FROM tl_rms_tmp WHERE ref_id=? AND ref_table=? AND ref_author=?")
			->limit(1)
			->execute
			(
				$intId,
				$strTable,
				$userID
			);

		//wenn z.B. der Datensatz neu angelegt wurde
		if($tmpDataObj->numRows > 0) $data = unserialize($tmpDataObj->data);
		else $data = $newData;


		// create / first-save
		$isNewEntryObj = $this->Database->prepare('SELECT count(*) c FROM `'.$strTable.'` WHERE `id`=? AND `tstamp`=?')
						->limit(1)
						->execute($intId,0);

		if ((int) $isNewEntryObj->c == 1)
		{
		    $data['tstamp'] = time();
		    $data['rms_first_save'] = 1;
		}

		//overwrite with live-data
		$data['rms_new_edit'] = 1;
		$data['rms_notice'] = $newData['rms_notice'];

		$objUpdate = $this->Database->prepare("UPDATE ".$strTable." %s WHERE id=?")->set($data)->execute($intId);

		//status
		$status = $this->rmsHelper->isMemberOfMasters() ?  1 : 0;

		//overwrite with new-data
		$newRmsData = ($data['type'] == $newData['type']) ? array_merge($data, $newData) : $newData;

		$arrSubmitData = array
		(
			'tstamp' => time(),
			'ref_id' => $intId,
			'ref_table' =>  $strTable,
			'ref_author' => $userID,
			'ref_notice' => $newRmsData['rms_notice'],
			'status' => $status,
			'data'=> $newRmsData
		);

		//existiert schon eine Bearbeitung
		$objData = $this->Database->prepare("SELECT id FROM tl_rms WHERE ref_id=? AND ref_table=? AND ref_author=?")
									->execute(
										$this->Input->get("id"),
										$this->Input->get("table"),
										$userID );

		if ($objData->numRows == 1)
		{
			 $this->Database->prepare("UPDATE tl_rms %s WHERE id=?")
				->set($arrSubmitData)
				->execute($objData->id);
		}
		else
		{
			$this->Database->prepare("INSERT INTO tl_rms %s")->set($arrSubmitData)->execute();
		}
	}

	//overwrite only rms-date und reset live-data
	public function onRestoreCallback($intPid, $strTable, $data, $intVersion)
	{
		$this->import('SvenRhinow\rms\rmsHelper', 'rmsHelper');

		if($this->rmsHelper->isMemberOfMasters() && \Input::get('author'))
		{

			//hole letzte Livedaten
			$tmpDataObj = $this->Database->prepare("SELECT `data` FROM `tl_rms_tmp` WHERE  ref_id=? AND ref_table=? AND ref_author=?")
										->limit(1)
										->execute
										(
											$intPid,
											$strTable,
											\Input::get('author')
										);

			$liveData = ($tmpDataObj->numRows > 0) ? unserialize($tmpDataObj->data) : $data;

			//ueberschreibe wieder die Livedaten
			$this->Database->prepare("UPDATE ".$strTable." %s  WHERE id=?")
							->set($liveData)
							->execute($intPid);

			//ersetze frei-zugebenedes Release mit Versions-Daten
			$set = array('data' => $data);
			$this->Database->prepare("UPDATE tl_rms %s WHERE ref_id=? AND ref_table=? AND ref_author=?")
				->set($set)
				->execute($intPid, $strTable, \Input::get('author'));
		}

	}

}
