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
	    
	    // deaktiviere Freigabefunktion wenn der Schalter nicht existiert
	    if(!$GLOBALS['TL_CONFIG']['rms_active']) 
	    {
	    	$this->Config->update('rms_active', false);
	    	$GLOBALS['TL_CONFIG']['rms_active'] = false;
	    }        
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

	    // verhindert Fehlermeldungen beim re-installieren wenn 'rms_active' noch aktiv gesetzt ist (#1)
	    if(!$Database->tableExists('tl_rms_settings'))
	    {
	    	$GLOBALS['TL_CONFIG']['rms_active'] = false;
	    	$this->Config->update('rms_active', false);
	    	return array();
	    }
	    
	    $resObj = $Database->prepare('SELECT * FROM `tl_rms_settings`')
				   ->limit(1)
				   ->execute();

	    return (!$resObj->numRows) ? array() : $resObj->row();
	 }

	 /**
	 * ueberprueft ob der Benutzer ohne Freigaberechte ist
	 */
	 public function isMemberOfSlaves()
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

	    if(TL_MODE != 'BE' || \Environment::get('isAjaxRequest'))	return;
		
		$protected = false;

		switch($strTable)
		{
			case 'tl_content':
				$protected = $this->rmsIsContentProtected($strTable);
			break;
			default:
				$protected = $this->rmsIsTableProtected($strTable);				
		}                  
             
	    if ($this->isMemberOfSlaves() && $protected && ($GLOBALS['TL_CONFIG']['rms_active'])  || \Input::get("author"))
	    {

			$GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'] = 'rmsTable';

			//falls keine separaten callbacks existieren die Standart-Callbacks aufrufen
			$GLOBALS['TL_DCA'][$strTable]['config']['ondelete_callback'][] = (method_exists($strTable.'_rms', 'onDeleteCallback')) ? array($strTable.'_rms','onDeleteCallback') : array('SvenRhinow\rms\rmsDefaultCallbacks','onDeleteCallback');
			$GLOBALS['TL_DCA'][$strTable]['config']['onrestore_callback'][] = (method_exists($strTable.'_rms', 'onRestoreCallback')) ? array($strTable.'_rms','onRestoreCallback') : array('SvenRhinow\rms\rmsDefaultCallbacks','onRestoreCallback');
			$GLOBALS['TL_DCA'][$strTable]['config']['oncut_callback'][] = (method_exists($strTable.'_rms', 'onCutCallback')) ? array($strTable.'_rms','onCutCallback') : array('SvenRhinow\rms\rmsDefaultCallbacks','onCutCallback');

			if (\Input::get("act") == "edit" || \Input::get("act") == "show")
			{			    
				$GLOBALS['TL_DCA'][$strTable]['config']['getrms_callback'][] = (method_exists($strTable.'_rms', 'onEditCallback')) ? array($strTable.'_rms','onEditCallback') : array('SvenRhinow\rms\rmsDefaultCallbacks','onEditCallback');
				$GLOBALS['TL_DCA'][$strTable]['config']['onsubmit_callback'][] = (method_exists($strTable.'_rms', 'onSubmitCallback')) ?  array($strTable.'_rms','onSubmitCallback') : array('SvenRhinow\rms\rmsDefaultCallbacks','onSubmitCallback');			
			}
			else
			{
				$GLOBALS['TL_DCA'][$strTable]['config']['getrms_listview_callback'][] = (method_exists($strTable.'_rms', 'onListCallback')) ? array($strTable.'_rms','onListCallback') : array('SvenRhinow\rms\rmsDefaultCallbacks','onListCallback');
				
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
	    $ignoreTypedArr = array_map('trim',explode(',',$this->settings['ignore_content_types']));

	    if(is_array($ignoreTypedArr) && in_array($objElement->type, $ignoreTypedArr) ) return $strBuffer;

       	//wenn es keine tl_content-Tabelle ist nicht weiter, da diese von 'modifyForPreview' verarbeitet werden
    	if($objElement->rms_ref_table != 'tl_content') return $strBuffer;

	    if(\Input::get('do') == 'preview')
	    {
			$id = false;

        	$objStoredData = $this->Database->prepare("SELECT `data` FROM `tl_rms` WHERE `ref_id`=? AND `ref_table`=?")
									->execute($objElement->id, 'tl_content');

			if ($objStoredData->numRows  == 1)
			{
				$objRow =  $this->overwriteDbObj($objElement, deserialize($objStoredData->data));

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
	* parseTemplate-HOOK
	* ersetzt die Inhalte mit rms-Datensatz, wenn rms_new_edit=1 und get-Parameter do=preview gesetzt ist.
	*/
	public function modifyForPreview($objTemplate)
	{
	    if(\Input::get('do') == 'preview' && $objTemplate->rms_new_edit == 1 && strlen($objTemplate->rms_ref_table) > 0)
	    {
        	//wenn es eine tl_content-Tabelle ist, nicht weiter da diese Inhalte von 'previewContentElement' verarbeitet werden
	    	if($objTemplate->rms_ref_table == 'tl_content') return;

        	$objStoredData = $this->Database->prepare("SELECT `data` FROM `tl_rms` WHERE `ref_id`=? AND `ref_table`=?")
									->execute($objTemplate->id, $objTemplate->rms_ref_table);

			if ($objStoredData->numRows  == 1)
			{
				// custom edit for modul template in the bottom class of dca-file
				$sectionClass = $objTemplate->rms_ref_table.'_rms';
				$sectionMethod = 'modifyForPreview';
				$rmsDataArr = deserialize($objStoredData->data);

				if(method_exists($sectionClass, $sectionMethod))
				{ 	
					$this->import($sectionClass);
					$objTemplate = 	$this->$sectionClass->$sectionMethod($objTemplate, $rmsDataArr);
				}
				else
				{
					$objTemplate =  $this->overwriteDbObj($objTemplate, $rmsDataArr);
				}

				// force view
				$objTemplate->published = 1; // news,newsletter
				$objTemplate->invisible = 0; // content
			}		
		}
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
		$this->loadLanguageFile('tl_default');	
		$this->import("BackendUser");

		$strTable = \Input::get("table") ? \Input::get("table") : 'tl_'.$this->Input->get("do");
		$this->settings = $this->getSettings();      

		$RmsSectionSettings = $this->getRmsSectionSettings($dc->id,	$strTable, $dc->activeRecord->ptable);

		$fallbackEmail = $this->getMemberData($this->settings['fallback_master_member'], 'email');
		$sendToEmail = ($RmsSectionSettings['master_email']) ? $RmsSectionSettings['master_email'] : $fallbackEmail;

		if($varValue == 1 && !empty($strTable) && $RmsSectionSettings['rms_protected'])
		{	
			//mail from editor to Super-Editor (question)
			if(!$this->isMemberOfMasters())
			{
				$text =  $dc->Input->post('rms_notice');
				$text .= "\nPfad: ".$this->Environment->url.$this->Environment->requestUri;

				$sendToEmailsArr = (strlen(trim($this->settings['extent_emailto'])) > 0) ? array_map('trim',explode(',',$this->settings['extent_emailto'])) : array();
				$sendToEmailsArr[] = $sendToEmail;
				$sendToEmailsArr = array_unique($sendToEmailsArr);

				$sendToEmails = implode(',',$sendToEmailsArr);

				$email = new \Email();
				$email->from = $this->BackendUser->email;
				$email->charset = 'utf-8';
				$email->subject = $GLOBALS['TL_LANG']['MSC']['rms_email_subject_question'];
				$email->text = $text;
				$email->sendTo($sendToEmails);			    
			}
			else
			//send Email from Super-Editor to editor  (answer)
			{
				$text =  $dc->Input->post('rms_notice');
				$text .= "\nPfad: ".$this->Environment->url.$this->Environment->requestUri;

				$email = new \Email();
				$email->from = $this->BackendUser->email;
				$email->charset = 'utf-8';
				$email->subject = $GLOBALS['TL_LANG']['MSC']['rms_email_subject_answer'];
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

			if (is_array($GLOBALS['TL_HOOKS']['rmsPublish']))
			{
				foreach ($GLOBALS['TL_HOOKS']['rmsPublish'] as $callback)
				{
					$this->import($callback[0]);
					$label = $this->$callback[0]->$callback[1]($objData->ref_table, unserialize($objData->data);
				}
			}

		    $this->Database->prepare("UPDATE ".$objData->ref_table." %s WHERE id=?")->set($arrData)->execute($objData->ref_id);

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

	 	if($id == '') $id = \Input::get('id');
	 	if($table == '') $table = \Input::get('table');
	 	if(!$this->settings) $this->settings = $this->getSettings();

	 	$rmsObj = $this->Database->prepare('SELECT * FROM `tl_rms` WHERE `ref_id`=? AND `ref_table`=?')
	 							->limit(1)
	 							->execute($id, $table);

		return $rmsObj->preview_jumpTo;

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
					// print_r($pageObj);
			// return obj or recursive this method 
			return ($pageObj->type == 'root') ? $pageObj : $this->getRootPage($pageObj->pid);
	    }
	}

	/**
	* get root-Parent-Table for the rms-settings and jumpto for the preview-Link
	* @param string
	* @return string
	*/
	public function getRootParentTable($table,$ptable='')
	{	
		if($ptable == 'tl_article') return 'tl_page';

		$this->loadDataContainer($table);
		$pTable = $table;
		
		if( strlen($GLOBALS['TL_DCA'][$pTable]['config']['ptable']) > 0 )
		{
			$pTable = $this->getRootParentTable($GLOBALS['TL_DCA'][$pTable]['config']['ptable'], '');
		}
		return $pTable;
	}

	/**
	* get root-Parent-DB-Object for the rms-settings
	* @param string
	* @return string
	*/
	public function getRootParentDBObj($id, $table, $ptable, $rtable)
	{
		$mode = \Input::get('mode');
		if( !empty($mode) && $table == 'tl_article') $ptable = 'tl_page';

		if($ptable == '')
		{
			$dbObj = $this->Database->prepare( "SELECT * FROM ".$table." WHERE `id`=?")
						->limit(1)
						->execute($id);
		}
		else
		{
			$this->loadDataContainer($ptable);

			$dbObj = $this->Database->prepare("SELECT pt.* FROM ".$ptable." pt  LEFT JOIN ".$table." t ON pt.id = t.pid WHERE t.`id`=?")
						->limit(1)
						->execute($id);

			if( strlen($GLOBALS['TL_DCA'][$ptable]['config']['ptable']) > 0 )
			{
				$dbObj = $this->getRootParentDBObj($dbObj->id, $ptable, $GLOBALS['TL_DCA'][$ptable]['config']['ptable'], $rtable);
			}
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
	public function getRmsSectionSettings($id,$table,$ptable)
	{

		// um zu testen ob es tl_page ist oder eine andere, da tl_content auch von Modulen wie News verwendet wird
		$root_table = $this->getRootParentTable($table, $ptable);

		// die Einstellungen zu dem Bereich holen
		$dbObj = $this->getRootParentDBObj($id, $table, $ptable, $root_table);

		// den dca der Eltern-Tabelle holen
		if (!is_null($ptable)) {
			$this->loadDataContainer($ptable);
		}

		$jumpToUrl = '';

		// wenn es ein normaler Inhalt einer Seite ist
		if($root_table == 'tl_page')
		{

			$rootPage = $this->getRootPage($dbObj->id);

			$dbObj->rms_master_member = $rootPage->rms_master_member;
			$dbObj->rms_protected = $rootPage->rms_protected;
			$jumpToUrl = $this->generateFrontendUrl($dbObj->row(),'/do/preview');

		}
		// wenn es ein Modul wie z.B. News ist
		else
		{
			// Weiterleitungsseite (ID) ermitteln
			$jumpToID = ((int) $dbObj->rms_preview_jumpTo > 0 ) ? $dbObj->rms_preview_jumpTo : $dbObj->jumpTo;

			if($jumpToID > 0)
			{	         	
            	$pageObj = $this->Database->prepare('SELECT `id`,`alias` FROM `tl_page` WHERE `id`=?')->execute($jumpToID);
            	$jumpToUrl = $this->generateFrontendUrl($pageObj->row(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ?  '/%s/do/preview' : '/items/%s/do/preview'));

            	if(strlen($ptable) > 0 )
            	{
					// wenn es die Content-Ebene ist, dann den Alias der Eltern-Tabelle für den Vorschaulink zuholen
					if($table == 'tl_content')
					{
						$moduleObj = $this->Database->prepare("SELECT pt.* FROM ".$ptable." `pt` LEFT JOIN `tl_content` `c` ON `pt`.`id` = `c`.`pid` WHERE `c`.`id`=?")
	                    		                    ->limit(1)
	                            		            ->execute($id);
                    }
                    // wenn es die Modulebene ist, den Alias direkt aus der aktuellen Tabelle für den Vorschaulink holen
                    else
                    {
						$moduleObj = $this->Database->prepare("SELECT * FROM ".$table."  WHERE `id`=?")
		                    ->limit(1)
        		            ->execute($id);

                    }
                    // komplette Vorschau-Url erstellen
					$jumpToUrl = sprintf($jumpToUrl, $moduleObj->alias);
            	}
			}
		}	
		// Bereich-Einstellungen als Array zusammenstellen
		$rmsSectionSettings = array(
			'rms_protected' =>	$dbObj->rms_protected,
			'master_id'	=> $dbObj->rms_master_member,
			'master_email' => ((int) $dbObj->rms_master_member > 0 ) ? $this->getMemberData($dbObj->rms_master_member,'email') : $this->settings['sender_email'],			
			'preview_jumpTo' => $jumpToUrl
		);

		return $rmsSectionSettings;
	}

	/**
	* get any field from given user-id 
	* @param int
	* @param string
	* @return mixed
	*/
	public function getMemberData($id, $field = '')
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
	 * testet auf diverse Tabellen ob diese als geschützt markiert wurden
	 * @param string
	 * @return bool
	 */	
	protected function rmsIsTableProtected($strTable)
	{
		$return = false;

		switch($strTable)
		{
			case 'tl_article':
				
				$curObj = $this->Database->prepare('SELECT `p`.* FROM `tl_article` `a`
				LEFT JOIN `tl_page` `p` ON `p`.`id` = `a`.`pid`
				WHERE `a`.`id`=?')
						->limit(1)
						->execute(\Input::get('id'));
				
				$rootPageObj = $this->getRootPage($curObj->pid);	
				
				if($rootPageObj->rms_protected == 1) 
				{
					$this->settings['sender_email'] = $this->getMemberData($rootPageObj->rms_master_member,'email');				
					$return = true;						
				}

			break;
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
