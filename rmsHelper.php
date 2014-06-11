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

        // if not set -> set default values
	    if(!$this->settings['control_group']) $this->settings['control_group'] = 0;
	    if(!$GLOBALS['TL_CONFIG']['rms_active']) $GLOBALS['TL_CONFIG']['rms_active'] = false;

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
	 	$this->import('BackendUser');
	 	return (!$this->BackendUser->isMemberOf($this->settings['control_group'])  && !$this->BackendUser->isAdmin) ? true : false;
	 }

	 /**
	 * ueberprueft ob der Benutzer mit Freigaberechte ist
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

	    /**
	    * deprecated
		*
		* $arrAllowedTables = $this->settings['release_tables'] ? deserialize($this->settings['release_tables']) : array();
		* if(!in_array($strTable, $arrAllowedTables)) return;
		*/
		
		$protected = false;

		switch($strTable)
		{
			case 'tl_content':
				$protected = $this->rmsIsContentProtected($strTable);
			break;
			default:
				$protected = $this->rmsIsTableProtected($strTable);
		}

	    /**
	    * deprecated
		*		
	    * $protected = ($strTable == 'tl_content' ) ? $this->rmsIsContentProtected() : true;
		*/

	    if ($this->isMenmberOfSlaves() && $protected && ($GLOBALS['TL_CONFIG']['rms_active'])  || \Input::get("author"))
	    {

			$GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'] = 'rmsTable';

			//falls keine separaten callbacks existieren die Standart-Callbacks aufrufen
			$GLOBALS['TL_DCA'][$strTable]['config']['ondelete_callback'][] = (method_exists($strTable.'_rms', 'onDeleteCallback')) ? array($strTable.'_rms','onDeleteCallback') : array('SvenRhinow\rms\rmsDefaultCallbacks','onDeleteCallback');

			if (\Input::get("act") == "edit")
			{			    
				$GLOBALS['TL_DCA'][$strTable]['config']['getrms_callback'][] = (method_exists($strTable.'_rms', 'onEditCallback')) ? array($strTable.'_rms','onEditCallback') : array('SvenRhinow\rms\rmsDefaultCallbacks','onEditCallback');
				$GLOBALS['TL_DCA'][$strTable]['config']['onsubmit_callback'][] = (method_exists($strTable.'_rms', 'onSubmitCallback')) ?  array($strTable.'_rms','onSubmitCallback') : array('SvenRhinow\rms\rmsDefaultCallbacks','onSubmitCallback');			
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

	//ToDo: rewrite this method and create a Hook for other modules
	public function previewContentElement($objElement, $strBuffer)
	{

	    // return if this ignored field
	    $ignoreFieldArr = explode( ',', str_replace(' ','',$this->settings['ignore_fields']) );
	    if(is_array($ignoreFieldArr) && in_array($objElement->type, $ignoreFieldArr) ) return $strBuffer;

	    if(\Input::get('do') == 'preview' && $objElement->rms_new_edit == 1)
	    {
			// print_r($objElement);

			$id = false;

			//region
			// ToDo: test of old
			// switch($this->Input->get('region'))
			// {
			// 	case 'news':
			// 	case 'newsletter':
			// 	case 'calendar_events':
			// 	    $typePrefix = 'mod_';
			// 	break;
			// 	default:
			// 	    $typePrefix = 'ce_';
   //      	}

        	$objStoredData = $this->Database->prepare("SELECT `data` FROM `tl_rms` WHERE `ref_id`=? AND `ref_table`=?")
									->execute($objElement->id, 'tl_content');

			if ($objStoredData->numRows  == 1)
			{
				$objRow =  $this->overwriteDbObj($objElement, deserialize($objStoredData->data));
				// $objRow->typePrefix = $typePrefix;

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
			    if(!$lastEditorObj->email) return;

	            $text =  $dc->Input->post('rms_notice');
			    $text .= "\nPfad: ".$this->Environment->url.$this->Environment->requestUri;

			    $email = new \Email();
			    $email->from = $this->BackendUser->email;
			    $email->charset = 'utf-8';
			    $email->subject = 'Freigabe-Aufforderung (Antwort)';
			    $email->text = $text;
			    $email->sendTo($this->getMemberData(\Input::get('author'), 'email'));
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


		    //correct any fields
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

	 	if($id == '') $id = \Input::get('id');
	 	if($table == '') $table = \Input::get('table');
	 	if(!$this->settings) $this->settings = $this->getSettings();

	 	switch($table)
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

	 /**
	 * testet auf diverse Module welche die tl_content-Tabelle verwenden
	 * @param string
	 * @return bool
	 */
	 protected function rmsIsContentProtected($strTable)
	 {
		$return = false;

		switch(\Input::get('do'))
		{
			case 'article':
			
				$protectedRootPages = $this->settings['whitelist_domains'] ? deserialize($this->settings['whitelist_domains']) : array();

				$curObj = $this->Database->prepare('SELECT `p`.* FROM `tl_page` `p`
				LEFT JOIN `tl_article` `a` ON `p`.`id`=`a`.`pid`
				LEFT JOIN `tl_content` `c` ON `a`.`id` = `c`.`pid`
				WHERE `c`.`id`=?')
						->limit(1)
						->execute(\Input::get('id'));

				$rootPageObj = $this->getRootPage($curObj->pid);

				// old and new filter
				if(in_array($rootPageObj->id,$protectedRootPages) || $rootPageObj->rms_protected == 1) 
				{
					$this->settings['sender_email'] = $this->getMemberData($curObj->rms_master_member,'email');
					$return = true;
				}
			break;
			case 'news':

				$curObj = $this->Database->prepare('SELECT `na`.* FROM `tl_news_archive` `na`
				LEFT JOIN `tl_news` `n` ON `na`.`id`=`n`.`pid`
				LEFT JOIN `tl_content` `c` ON `n`.`id` = `c`.`pid`
				WHERE `c`.`id`=?')
						->limit(1)
						->execute(\Input::get('id'));

				if($curObj->rms_protected == 1) 
				{
					$this->settings['sender_email'] = $this->getMemberData($curObj->rms_master_member,'email');				
					$return = true;						
				}
			break;
			case 'calendar':

				$curObj = $this->Database->prepare('SELECT `cal`.* FROM `tl_calendar` `cal`
				LEFT JOIN `tl_calendar_events` `calev` ON `cal`.`id`=`calev`.`pid`
				LEFT JOIN `tl_content` `c` ON `calev`.`id` = `c`.`pid`
				WHERE `c`.`id`=?')
						->limit(1)
						->execute(\Input::get('id'));

				if($curObj->rms_protected == 1) 
				{
					$this->settings['sender_email'] = $this->getMemberData($curObj->rms_master_member,'email');				
					$return = true;						
				}		

			break;
			default:

				// HOOK: add custom logic
				if (isset($GLOBALS['TL_HOOKS']['rmsIsContentProtected']) && is_array($GLOBALS['TL_HOOKS']['rmsIsContentProtected']))
				{
					foreach ($GLOBALS['TL_HOOKS']['rmsIsContentProtected'] as $callback)
					{
						if (is_array($callback))
						{
							$this->import($callback[0]);
							$status = $this->$callback[0]->$callback[1]($strTable);
						}
						elseif (is_callable($callback))
						{
							$status = $callback($strTable);
						}

						if (is_bool($status))
						{
							return $status;
						}
					}
				}

		}
		return $return;
	 }

	 /**
	 * get the RootPage from PageId
	 * @param int
	 * @return obj
	 */
	 protected function getRootPage($pid = 0)
	 {
	    if(intval($pid) > 0)
	    {
			$pageObj = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
					->limit(1)
					->execute($pid);

			// return obj or recursive this method 
			return ($pageObj->type == 'root') ? $pageObj : $this->getRootPage($pageObj->pid);
	    }
	}

	/**
	* get root-Parent-Table for the rms-settings and jumpto for the preview-Link
	* @param string
	* @return string
	*/
	public function getRootParentTable($table)
	{
		$this->loadDataContainer($table);
		$pTable = $table;
		if( strlen($GLOBALS['TL_DCA'][$pTable]['config']['ptable']) > 0 )
		{
			$pTable = $this->getRootParentTable($GLOBALS['TL_DCA'][$pTable]['config']['ptable']);
		}
		return $pTable;
	}

	/**
	* get root-Parent-DB-Object for the rms-settings and jumpto for the preview-Link
	* @param string
	* @return string
	*/
	public function getRootParentDBObj($id, $table, $ptable, $rtable)
	{
		$this->loadDataContainer($table);

		$orig_id = $id;
		$orig_table = $table;
		$orig_ptable = $ptable;

		$dbObj = $this->Database->prepare("SELECT pt.* FROM ".$ptable." pt  LEFT JOIN ".$table." t ON pt.id = t.pid WHERE t.`id`=?")
					->limit(1)
					->execute($id);

		if( strlen($GLOBALS['TL_DCA'][$ptable]['config']['ptable']) > 0 )
		{
			$dbObj = $this->getRootParentDBObj($dbObj->id, $ptable, $GLOBALS['TL_DCA'][$ptable]['config']['ptable'], $rtable);
		}
		return $dbObj;
	}

	/**
	* get rms_section_settings
	* @param int
	* @param string
	* @param string
	* @return array 
	*/
	public function getRmsSectionSettings($id, $table, $ptable)
	{
		// if($id=='' || $table=='' || $ptable=='') return false;

		$root_table = $this->getRootParentTable($table);

		$dbObj = $this->getRootParentDBObj($id, $table, $ptable, $root_table);
		
		$this->loadDataContainer($ptable);
		
		//preview-Url erstellen
		$jumpToUrl = '';

		if($ptable == 'tl_page')
		{
			$jumpToUrl = $this->generateFrontendUrl($dbObj->row(),'/do/preview');
		}
		else
		{
			$jumpToID = (strlen($dbObj->rms_preview_jumpTo) > 0 ) ? $dbObj->rms_preview_jumpTo : $dbObj->jumpTo;
			
			if($jumpToID > 0)
			{	         	
            	$pageObj = $this->Database->prepare('SELECT `id`,`alias` FROM `tl_page` WHERE `id`=?')->execute($jumpToID);
            	$jumpToUrl = $this->generateFrontendUrl($pageObj->row(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ?  '/%s/do/preview' : '/items/%s/do/preview'));

            	if(strlen($ptable) > 0 )
            	{
					if($table == 'tl_content')
					{
						$moduleObj = $this->Database->prepare("SELECT pt.* FROM ".$ptable." `pt` LEFT JOIN `tl_content` `c` ON `pt`.`id` = `c`.`pid` WHERE `c`.`id`=?")
	                    		                    ->limit(1)
	                            		            ->execute($id);
                    }
                    else
                    {
						$moduleObj = $this->Database->prepare("SELECT * FROM ".$table."  WHERE `id`=?")
		                    ->limit(1)
        		            ->execute($id);

                    }
                    // print_r($moduleObj);
					$jumpToUrl = sprintf($jumpToUrl, $moduleObj->alias);
            	}
			}
			$rmsSectionSettings = array(
				'master_email' => (strlen($dbObj->rms_master_member) > 0 ) ? $this->getMemberData($dbObj->rms_master_member,'email') : $this->settings['sender_email'],
				'preview_jumpTo' => $jumpToUrl
			);

			return $rmsSectionSettings;
		}	
	}

	public function test()
	{
		$rmsSectionSettings = array('master_email'=>'kservice@sr-tag.de',);
		return $rmsSectionSettings;
	}

	/**
	* get any field from given user-id 
	* @param int
	* @param string
	* @return mixed
	*/
	protected function getMemberData($id, $field = '')
	{
		if((int)$id > 0) 
		{
			$uObj = $this->Database->prepare('SELECT * FROM `tl_user` WHERE `id` = ?')->limit(1)->execute($id);

			if(strlen($uObj->{$field}) > 0)
			{
				return $uObj->{$field};
			}
		}
				
	}

	 /**
	 * testet auf diverse Tabellen ob diese als geschützt markiert wurden
	 * @param string
	 * @return bool
	 */	
	protected function rmsIsTableProtected($strTable)
	{
		$return = false;

		switch($strTable)
		{
			case 'tl_newsletter':

				$curObj = $this->Database->prepare('SELECT `nlc`.* FROM `tl_newsletter_channel` `nlc`
				LEFT JOIN `tl_newsletter` `nl` ON `nlc`.`id` = `nl`.`pid`
				WHERE `nl`.`id`=?')
						->limit(1)
						->execute(\Input::get('id'));

				if($curObj->rms_protected == 1) 
				{
					$this->settings['sender_email'] = $this->getMemberData($curObj->rms_master_member,'email');				
					$return = true;						
				}	

			break;
			case 'tl_newsletter_channel':

				$curObj = $this->Database->prepare('SELECT `nlc`.* FROM `tl_newsletter_channel` `nlc` WHERE `nlc`.`id`=?')
						->limit(1)
						->execute(\Input::get('id'));

				if($curObj->rms_protected == 1) 
				{
					$this->settings['sender_email'] = $this->getMemberData($curObj->rms_master_member,'email');				
					$return = true;						
				}	

			break;			
			case 'tl_faq':

				$curObj = $this->Database->prepare('SELECT `faqc`.* FROM `tl_faq_category` `faqc`
				LEFT JOIN `tl_faq` `faq` ON `faqc`.`id` = `faq`.`pid`
				WHERE `faq`.`id`=?')
						->limit(1)
						->execute(\Input::get('id'));

				if($curObj->rms_protected == 1) 
				{
					$this->settings['sender_email'] = $this->getMemberData($curObj->rms_master_member,'email');				
					$return = true;						
				}	

			break;
			case 'tl_faq_category':

				$curObj = $this->Database->prepare('SELECT `faqc`.* FROM `tl_faq_category` `faqc` WHERE `faqc`.`id`=?')
						->limit(1)
						->execute(\Input::get('id'));

				if($curObj->rms_protected == 1) 
				{
					$this->settings['sender_email'] = $this->getMemberData($curObj->rms_master_member,'email');				
					$return = true;						
				}	

			break;
			case 'tl_news':

				$curObj = $this->Database->prepare('SELECT `na`.* FROM `tl_news_archive` `na`
				LEFT JOIN `tl_news` `n` ON `na`.`id` = `n`.`pid`
				WHERE `n`.`id`=?')
						->limit(1)
						->execute(\Input::get('id'));

				if($curObj->rms_protected == 1) 
				{
					$this->settings['sender_email'] = $this->getMemberData($curObj->rms_master_member,'email');				
					$return = true;						
				}	

			break;	
			case 'tl_news_archive':

				$curObj = $this->Database->prepare('SELECT `na`.* FROM `tl_news_archive` `na` WHERE `na`.`id`=?')
						->limit(1)
						->execute(\Input::get('id'));

				if($curObj->rms_protected == 1) 
				{
					$this->settings['sender_email'] = $this->getMemberData($curObj->rms_master_member,'email');				
					$return = true;						
				}	

			break;
			case 'tl_calendar_events':

				$curObj = $this->Database->prepare('SELECT `cal`.* FROM `tl_calendar` `cal`
				LEFT JOIN `tl_calendar_events` `calev` ON `cal`.`id` = `calev`.`pid`
				WHERE `calev`.`id`=?')
						->limit(1)
						->execute(\Input::get('id'));

				if($curObj->rms_protected == 1) 
				{
					$this->settings['sender_email'] = $this->getMemberData($curObj->rms_master_member,'email');				
					$return = true;						
				}	

			break;	
			case 'tl_calendar':

				$curObj = $this->Database->prepare('SELECT `cal`.* FROM `tl_calendar` `cal` WHERE `cal`.`id`=?')
						->limit(1)
						->execute(\Input::get('id'));

				if($curObj->rms_protected == 1) 
				{
					$this->settings['sender_email'] = $this->getMemberData($curObj->rms_master_member,'email');				
					$return = true;						
				}	

			break;								
			default:
				
				// HOOK: add custom logic
				if (isset($GLOBALS['TL_HOOKS']['rmsIsTableProtected']) && is_array($GLOBALS['TL_HOOKS']['rmsIsTableProtected']))
				{
					foreach ($GLOBALS['TL_HOOKS']['rmsIsTableProtected'] as $callback)
					{
						if (is_array($callback))
						{
							$this->import($callback[0]);
							$status = $this->$callback[0]->$callback[1]($strTable);
						}
						elseif (is_callable($callback))
						{
							$status = $callback($strTable);
						}

						if (is_bool($status))
						{
							return $status;
						}
					}
				}			
		}
		return $return;
	}
}
