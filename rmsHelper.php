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
 * set namespace
 */
 namespace SvenRhinow\rms;


/**
 * Class rmsHelper
 *
 * @copyright  Sven Rhinow 2004-2014
 * @author     Sven Rhinow <kservice@sr-tag.de>
 * @package    rms
 */

class rmsHelper extends \Backend
{
    /* -------------------------------------------------------------------------
     * Vars
     */

    // instance
    protected static $instance = null;



	/**
	* hold rms settings as array
	* @var array
	*/
	protected $settings = array();

    /**
     * Constructor
     */
    protected function __construct()
    {
    	parent::__construct();

    	// Import
        $this->import("BackendUser","User");
        $this->import("String");

        $this->settings = self::getSettings();

        // Load Helper
        $String 	=	\String::getInstance();
        $Database  =	\Database::getInstance();
    }

    /**
     * Returns the rmsHelper
     * @return rmsHelper
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    static public function getSettings()
	{
	    $Database = \Database::getInstance();
	    $resObj = $Database->prepare('SELECT * FROM `tl_rms_settings`')
				   ->limit(1)
				   ->execute();

	    if(!$resObj->numRows)  return array();

	    return $resObj->row();
	 }

	 /**
	 * ueberprueft ob der Benutzer ohne Freigaberechte ist
	 */
	 public function isMenmberOfSlaves()
	 {
	 	// $this->import('User','BackendUser');
	 	return (!$this->User->isMemberOf($this->settings['control_group'])  && !$this->BackendUser->isAdmin) ? true : false;
	 }

	 /**
	 * ueberprueft ob der Benutzer ohne Freigaberechte ist
	 */
	 public function isMemberOfMasters()
	 {
	 	$this->import('BackendUser');
	 	return ($this->BackendUser->isMemberOf($this->settings['control_group']) || $this->BackendUser->isAdmin) ? true : false;
	 }

	/**
	* implement Backend - callbacks
	* @var string
	*/
	public function handleBackendUserAccessControlls($strTable)
	{

	    if(TL_MODE != 'BE')	return;

	    $arrAllowedTables = $this->settings['release_tables'] ? deserialize($this->settings['release_tables']) : array();
		if(!in_array($strTable, $arrAllowedTables)) return;

	    if(!$this->settings['control_group']) $this->settings['control_group'] = 0;

	    if(!$GLOBALS['TL_CONFIG']['rms_active']) $GLOBALS['TL_CONFIG']['rms_active'] = false;

	    $protectedContent = ($strTable == 'tl_content') ? $this->isContentRmsProtected() : true;

	    if (!$this->isMemberOfMasters() && $protectedContent  || \Input::get("author") && ($GLOBALS['TL_CONFIG']['rms_active']))
	    {

			$GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'] = 'rmsTable';
			$GLOBALS['TL_DCA'][$strTable]['config']['ondelete_callback'][] = array('SvenRhinow\rms\rmsDefaultCallbacks','onDeleteCallback');

			if (\Input::get("act")=="edit")
			{
			    
			    $GLOBALS['TL_DCA'][$strTable]['config']['getrms_callback'][] = array($strTable.'_rms','onEditCallback');
			    $GLOBALS['TL_DCA'][$strTable]['config']['onsubmit_callback'][] = array($strTable.'_rms','onSubmitCallback');
			}


	    }

	}

	/**
	* Frontend-Preview from not released Content if set get-Parameter 'do=preview'
	* HOOK: getContentElement
	* @var object
	* @var string
	* @return string
	*/
	public function previewContentElement($objElement, $strBuffer)
	{
	    $arrTables = deserialize($this->settings['release_tables']);

	    if($this->Input->get('do') == 'preview' || in_array(\Input::get('table'),$arrTables))
	    {
			$id = false;

			//region
			switch($this->Input->get('region'))
			{
				case 'news':
				case 'newsletter':
				case 'calendar_events':
				    $typePrefix = 'mod_';
				break;
				default:
				    $typePrefix = 'ce_';
        	}
        	$objStoredData = $this->Database->prepare("SELECT `data` FROM `tl_rms` WHERE `ref_id`=? AND `ref_table`=?")
									->execute($objElement->id, 'tl_content');

			if ($objStoredData->numRows  == 1)
			{
				$objRow =  $this->overwriteDbObj($objElement, deserialize($objStoredData->data));
				$objRow->typePrefix = $typePrefix;

				$objRow->published = 1; // news,newsletter
				$objRow->invisible = 0; // content

				$strClass = $this->findContentElement($objRow->type);
				$objElement = new $strClass($objRow);
				$strBuffer = $objElement->generate();
			}
	    }
	    else
	    {
			if($objElement->rms_first_save) $strBuffer = '';
	    }

	    return  $strBuffer;
	}

	/**
	* Overwrite db-object with data-array
	* @var object
	* @var string
	* @return object
	*/
	public function overwriteDbObj($origObj, $newArr)
	{
	    if(is_array($newArr) && count($newArr) > 0)
	    {
	 		foreach($newArr as $k => $v)
	 		{
			    $origObj->$k = $v;
	 		}
	    }
	    return  $origObj;
	}


	/**
	* send Email for new release if Checkbox selected
	* @var object
	*/
	public function sendEmailInfo($varValue, \DataContainer $dc)
	{
	    $strTable = $this->Input->get("table");
        $this->settings = $this->getSettings();

	    $this->import("BackendUser");

	    if($varValue == 1)
	    {
		//mail from editor to Super-Editor (question)
		if(!$this->isMemberOfMasters())
		{
            $text =  $dc->Input->post('rms_notice');
		    $text .= "\nPfad: ".$this->Environment->url.$this->Environment->requestUri;

		    $email = new \Email();
		    $email->from = $this->BackendUser->email;
		    $email->charset = 'utf-8';
		    $email->subject = 'Freigabe-Aufforderung';
		    $email->text = $text;
		    $email->sendTo(($this->settings['sender_email']) ? $this->settings['sender_email'] : $GLOBALS['TL_CONFIG']['adminEmail']);
		}
		else
		//send Email from Super-Editor to editor  (answer)
		{
		    //get the author-email from this change
		    $lastEditorObj = $this->Database->prepare('SELECT * FROM `tl_user` WHERE `id`=?')
		    ->limit(1)
		    ->execute($this->Input->get('author'));

		    if(!$lastEditorObj->email) return;

            $text =  $dc->Input->post('rms_notice');
		    $text .= "\nPfad: ".$this->Environment->url.$this->Environment->requestUri;

		    $email = new \Email();
		    $email->from = $this->BackendUser->email;
		    $email->charset = 'utf-8';
		    $email->subject = 'Freigabe-Aufforderung (Antwort)';
		    $email->text = $text;
		    $email->sendTo($lastEditorObj->email);
		}

	    }

	    //disable everytime sendEmail
	    $this->Database->prepare('UPDATE `'.$strTable.'` SET `rms_release_info`="" WHERE `id`=?')->execute($dc->id);

	    return '';
	}

	/**
	* overwrite the old entry if entry acknowdlge
	* @var object
	*/
	public function acknowdlgeEntry(\DataContainer $dc)
	{
		$objData = $this->Database->prepare("SELECT data,ref_table,ref_id FROM tl_rms WHERE id=?")->limit(1)->execute($dc->id);
		$arrData = unserialize($objData->data);

		if(is_array($arrData) && count($arrData)>0)
		{
		    unset($arrData['id']);
		    unset($arrData['pid']);
		    $arrData['rms_notice'] = '';
		    $arrData['rms_release_info'] = '';
		    $arrData['rms_first_save'] = '';
		    $arrData['rms_new_edit'] = '';
		    $arrData['tstamp'] = time();


		    //correct enny fields
		    switch($objData->ref_table)
		    {
				case 'tl_calendar_events':
				    $arrData['published'] = 1;
				break;
				case 'tl_news':
				    $arrData['published'] = 1;
				break;
				case 'tl_content':
				    unset($arrData['published']);
				    $arrData['invisible'] = '';
				break;
				case 'tl_newsletter':
				    unset($arrData['published']);
				break;
		    }

		    $objUpdate = $this->Database->prepare("UPDATE ".$objData->ref_table." %s WHERE id=?")->set($arrData)->execute($objData->ref_id);

		    $this->Database->prepare("DELETE FROM tl_rms WHERE id=?")->execute($dc->id);
        }
 		$this->redirect(str_replace('&key=acknowledge', '', $this->Environment->request));

	}

	/**
	* open Frontend with nooutorised Content
	*/
	public function showPreviewInBrowser()
	{
		$this->redirect($this->getPreviewLink());
	}

	/**
	* get Preview Link-Date
	*/
	 public function getPreviewLink($id='',$table='')
	 {

	 	$return = array();

	 	if($id == '') $id = $this->Input->get('id');
	 	if($table == '') $table = $this->Input->get('table');
	 	if(!$this->settings) $this->settings = $this->getSettings();

	 	switch($this->Input->get('table'))
		{
			case 'tl_content':

			    $pageObj = $this->Database->prepare('SELECT `p`.* FROM `tl_page` `p`
			    LEFT JOIN `tl_article` `a` ON `p`.`id`=`a`.`pid`
			    LEFT JOIN `tl_content` `c` ON `a`.`id` = `c`.`pid`
			    WHERE `c`.`id`=?')
					    ->limit(1)
					    ->execute($id);

	             if($pageObj->numRows > 0) $strUrl = $this->generateFrontendUrl($pageObj->row(),'/do/preview');
			    $strPreviewUrl = $this->Environment->base.$strUrl;

			break;
			case 'tl_newsletter':

			    //get Preview-Link
			    if($this->settings['prevjump_newsletter'])
			    {
				$objJumpTo = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?" . (!BE_USER_LOGGED_IN ? " AND published=1" : ""))
											->limit(1)
											->execute($this->settings['prevjump_newsletter']);

				if ($objJumpTo->numRows)
				{
					$strUrl = $this->generateFrontendUrl($objJumpTo->fetchAssoc(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ?  '/%s' : '/do/preview/items/%s'));
				}
			    }

			    //get Link-Title
			    $pageObj = $this->Database->prepare('SELECT * FROM `tl_newsletter` WHERE `id`=?')
						      ->limit(1)
						      ->execute($id);

			    $strPreviewUrl = sprintf($strUrl, $pageObj->alias);

			break;
			case 'tl_calendar_events':

			    if($this->settings['prevjump_calendar_events'])
			    {
				$objJumpTo = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?" . (!BE_USER_LOGGED_IN ? " AND published=1" : ""))
											->limit(1)
											->execute($this->settings['prevjump_calendar_events']);

				if ($objJumpTo->numRows)
				{
					$strUrl = $this->generateFrontendUrl($objJumpTo->fetchAssoc(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ?  '/%s' : '/do/preview/events/%s'));
				}
			    }

			    //get Link-Title
			    $pageObj = $this->Database->prepare('SELECT * FROM `tl_calendar_events` WHERE `id`=?')
						      ->limit(1)
						      ->execute($id);

			    $strPreviewUrl = sprintf($strUrl, $pageObj->alias);

			break;
			case 'tl_news':

			    if($this->settings['prevjump_news'])
			    {
					$objJumpTo = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?" . (!BE_USER_LOGGED_IN ? " AND published=1" : ""))
												->limit(1)
												->execute($this->settings['prevjump_news']);

					if ($objJumpTo->numRows)
					{
					    $strUrl = $this->generateFrontendUrl($objJumpTo->fetchAssoc(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ?  '/%s' : '/do/preview/items/%s'));
					}
			    }

			    //get Link-Title
			    $pageObj = $this->Database->prepare('SELECT * FROM `tl_news` WHERE `id`=?')
						      ->limit(1)
						      ->execute($id);

			    $strPreviewUrl = sprintf($strUrl, $pageObj->alias);
			break;
		}

		return $strPreviewUrl;

	 }


	 protected function isContentRmsProtected()
	 {

		if($this->Input->get('table') == 'tl_content')
		{
			$this->settings =  $this->getSettings();
			$protectedRootPages = $this->settings['whitelist_domains'] ? deserialize($this->settings['whitelist_domains']) : array();
			$return = false;

			$curPageObj = $this->Database->prepare('SELECT `p`.* FROM `tl_page` `p`
			LEFT JOIN `tl_article` `a` ON `p`.`id`=`a`.`pid`
			LEFT JOIN `tl_content` `c` ON `a`.`id` = `c`.`pid`
			WHERE `c`.`id`=?')
					->limit(1)
					->execute($this->Input->get('id'));

			$rootId = $this->recursivePage($curPageObj->pid);

			if(in_array($rootId,$protectedRootPages)) $return = true;
		}
		return $return;
	 }

	 protected function recursivePage($pid=0)
	 {
	    $returnId = $pid;

	    if(intval($pid) > 0)
	    {
			$Page = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
					->limit(1)
					->execute($pid);

			if($Page->type == 'root')
			{
			   return $Page->id;
			}
			else
			{
			    return $this->recursivePage($Page->pid);
			}
	    }
	}

	public function convert_to_obj($array)
	{
	    // if(is_array($array))
	    // {
		   //  $obj= object;

		   //  foreach ($array as $k=> $v)
		   //  {
		   //      $obj->{$k} = $v;
	    // 	}
	    // 	return $obj;
    	// }
    	// return false;
    	return (object) $array;
	}

}
