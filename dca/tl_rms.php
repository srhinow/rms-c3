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
 * Table tl_rms
 */
$GLOBALS['TL_DCA']['tl_rms'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'closed'                      => true,
		'notEditable'                 => true,
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary'
			)
		),
		'ondelete_callback' => array
		(
			array('tl_rms', 'setUnEditData')
		),
	),

	// List
	'list'  => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('tstamp DESC', 'id DESC'),
			'panelLayout'             => 'filter;sort,search,limit',
		),
		'label' => array
		(
			'fields'                  => array('tstamp', 'ref_author','ref_table','ref_notice','rms_new_edit'),
			'label_callback'   		  => array('tl_rms', 'listRecipient'),

		),
		'global_operations' => array
		(
			'settings' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_rms']['settings'],
				'href'                => 'table=tl_rms_settings&act=edit&id=1',
				'class'               => 'navigation settings',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_rms']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
				'button_callback'     => array('tl_rms', 'editArticle'),
				'attributes'          => 'class="contextmenu"'
			),
			'show_diff' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_rms']['show_diff'],
				'href'                => 'key=show_diff&popup=1',
				'attributes'		  => 'onclick="Backend.openModalIframe({\'width\':765,\'title\':\'Unterschiede anzeigen\',\'url\':this.href});return false"',
				'icon'                => 'diff.gif',
				'button_callback'     => array('tl_rms', 'showDiff')
			),			
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_rms']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'acknowledge' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_rms']['acknowledge'],
				'href'                => 'key=acknowledge',
				'icon'                => 'ok.gif',
			)
		)
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'pid' => array
		(
			'foreignKey'              => 'tl_news_archive.title',
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => array('type'=>'belongsTo', 'load'=>'eager')
		),
		'tstamp' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_rms']['tstamp'],
			'filter'                  => true,
			'sorting'                 => true,
			'flag'                    => 6,
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'ref_table' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_rms']['ref_table'],
			'filter'                  => false,
			'sorting'                 => true,
			'reference'               => &$GLOBALS['TL_LANG']['tl_rms'],
			'sql'					  => "varchar(255) NOT NULL default ''"
		),
		'ref_author' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_rms']['ref_author'],
			'filter'                  => true,
			'foreignKey'		  => 'tl_user.username',
			'sorting'                 => true,
			'sql'					  => "int(10) unsigned NOT NULL default '0'"
		),
		'ref_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_rms']['ref_id'],
			'search'                  => false,
			'filter'                  => false,
			'sorting'                 => true,
			'sql'					  => "int(10) unsigned NOT NULL default '0'"
		),
		'ref_notice' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_rms']['ref_notice'],
			'search'                  => true,
			'sql'					  => "longtext NULL"
		),
		'status' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_rms']['status'],
			'filter'                  => true,
			'sorting'                 => true,
			'reference'               => &$GLOBALS['TL_LANG']['tl_rms']['status_options'],
			'sql'					  => "int(10) unsigned NOT NULL default '0'"
		),
		'data' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_rms']['data'],
			'search'                  => false,
			'sql'					  => "mediumblob NULL"
		),

	)
);

class tl_rms extends Backend
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
	 * List a recipient
	 * @param array
	 * @return string
	 */
	public function listRecipient($row)
	{
		$this->import('Database');

		//get settings
		$resObj = $this->Database->prepare('SELECT * FROM `tl_rms_settings`')
				       ->limit(1)
				       ->execute();

		$settings = (!$resObj->numRows) ? array() : $resObj->row();

		//get user
		$userObj = $this->Database->prepare('SELECT * FROM `tl_user` WHERE `id`=?')
		               ->limit(1)
			       ->execute($row['ref_author']);

		$strUrl = false;

		//get referenz
		$refObj = $this->Database->prepare('SELECT * FROM '.$row['ref_table'].' WHERE `id`=? ')->limit(1)->execute($row['ref_id']);

		$rowDataArr = unserialize($row['data']);

		// get the correct section for the preview
		if($row['ref_table'] == 'tl_content' && $rowDataArr['ptable'] != '') $section = $rowDataArr['ptable'];
		elseif($row['ref_table'] == 'tl_content' && $rowDataArr['ptable'] == '') $section = 'tl_content';
		else $section = $row['ref_table'];

		$sectionName = $GLOBALS['TL_LANG']['tl_rms']['sessions'][$section];

		switch($section)
		{
			case 'tl_article':
			case 'tl_content':
			    $pageObj = $this->Database->prepare('SELECT `p`.* FROM `tl_page` `p`
			    LEFT JOIN `tl_article` `a` ON `p`.`id`=`a`.`pid`
			    LEFT JOIN `tl_content` `c` ON `a`.`id`=`c`.`pid`
			    WHERE `c`.`id`=?')
					    ->limit(1)
					    ->execute($row['ref_id']);

			    if($pageObj->numRows > 0) $strUrl = $this->generateFrontendUrl($pageObj->row(),'/do/preview');

			    $strPreviewLink = '<a href="'.$this->Environment->base.$strUrl.'" target="_blank">'.$pageObj->title.'</a>';

			break;
			case 'tl_newsletter':

			    //get Preview-Link
			    if($settings['prevjump_newsletter'])
			    {
					$objJumpTo = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?" . (!BE_USER_LOGGED_IN ? " AND published=1" : ""))
												->limit(1)
												->execute($settings['prevjump_newsletter']);

					if ($objJumpTo->numRows)
					{
						$strUrl = $this->generateFrontendUrl($objJumpTo->fetchAssoc(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ?  '/%s/do/preview' : '/items/%s/do/preview'));
					}
			    }

			    //if parent or chield-text
			    $nlId = ($row['ref_table'] == 'tl_content') ? $rowDataArr['pid'] : $rowDataArr['id'];

			    //get Link-Title
			    $pageObj = $this->Database->prepare('SELECT * FROM `tl_newsletter` WHERE `id`=?')
						      ->limit(1)
						      ->execute($nlId);

			    $strPreviewLink = '<a href="'.sprintf($strUrl, $pageObj->alias).'" target="_blank">'.$pageObj->subject.'</a>';
			break;
			case 'tl_calendar_events':

			    if($settings['prevjump_calendar_events'])
			    {
					$objJumpTo = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?" . (!BE_USER_LOGGED_IN ? " AND published=1" : ""))
												->limit(1)
												->execute($settings['prevjump_calendar_events']);

					if ($objJumpTo->numRows)
					{
						$strUrl = $this->generateFrontendUrl($objJumpTo->fetchAssoc(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ?  '/%s/do/preview' : '/events/%s/do/preview'));
					}
			    }

			    //if parent or chield-text
			    $eventId = ($row['ref_table'] == 'tl_content') ? $rowDataArr['pid'] : $rowDataArr['id'];

			    //get Link-Title
			    $pageObj = $this->Database->prepare('SELECT * FROM `tl_calendar_events` WHERE `id`=?')
						      ->limit(1)
						      ->execute($eventId);

			    $strPreviewLink = '<a href="'.sprintf($strUrl, $pageObj->alias).'" target="_blank">'.$pageObj->title.'</a>';
			break;
			case 'tl_news':

			    if($settings['prevjump_news'])
			    {
					$objJumpTo = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?" . (!BE_USER_LOGGED_IN ? " AND published=1" : ""))
												->limit(1)
												->execute($settings['prevjump_news']);

					if ($objJumpTo->numRows)
					{
						$strUrl = $this->generateFrontendUrl($objJumpTo->fetchAssoc(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ?  '/%s/do/preview' : '/items/%s/do/preview'));
					}
			    }

			    //if parent or chield-text
			    $newsId = ($row['ref_table'] == 'tl_content') ? $rowDataArr['pid'] : $rowDataArr['id'];

			    //get Link-Title
			    $pageObj = $this->Database->prepare('SELECT * FROM `tl_news` WHERE `id`=?')
						      ->limit(1)
						      ->execute($newsId);

			    $strPreviewLink = '<a href="'.sprintf($strUrl, $pageObj->alias).'" target="_blank">'.$pageObj->headline.'</a>';

			break;
		}
		$ifFirstSave = ($refObj->rms_first_save == 1) ? '<span style="padding:0 10px; font-weight:bold;">*neu erstellt*</span>': '';

		$label  = '<strong>Status:</strong><span class="status_'.$row['status'].'"> '.$GLOBALS['TL_LANG']['tl_rms']['status_options'][$row['status']].'</span>'.$ifFirstSave.'<br>';
		$label .= '<strong>Bereich:</strong> '.$sectionName.'<br>';
		$label .= '<strong>Vorchau-Link: </strong>'.$strPreviewLink.'<br>';
		$label .= '<strong>Author:</strong> '.$userObj->name.' ('.$userObj->email.')<br>';
		$label .= '<strong>letzte Bearbeitung:</strong> '.date($GLOBALS['TL_CONFIG']['datimFormat'],$row['tstamp']).'<br>';
		$label .= '<strong>Änderungs-Notiz:</strong> '.nl2br($row['ref_notice']);



		return sprintf('<div style="float:left">%s</div>',$label) . "\n";
	}

	/**
	 * Return the edit article button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function editArticle($row, $href, $label, $title, $icon, $attributes)
	{
// 		$objPage = $this->Database->prepare("SELECT * FROM ".$row['ref_table']." WHERE id=?")
// 								  ->limit(1)
// 								  ->execute($row['ref_id']);

                switch($row['ref_table'])
                {
                case 'tl_content': $getTableStr = 'do=article&table=tl_content'; break;
                case 'tl_newsletter': $getTableStr = 'do=newsletter&table=tl_newsletter'; break;
                case 'tl_news': $getTableStr = 'do=news&table=tl_news'; break;
                case 'tl_calendar_events': $getTableStr = 'do=calendar&table=tl_calendar_events'; break;
		}
		return  '<a href="'.$this->addToUrl($getTableStr.'&amp;act=edit&amp;id='.$row['ref_id']).'&amp;author='.$row['ref_author'].'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}

	public function setUnEditData(DataContainer $dc)
	{

        $ref_Table = $dc->activeRecord->ref_table;
        $ref_id = $dc->activeRecord->ref_id;

		//wait other edits for release
		$rmsObj = $this->Database->prepare("SELECT count(*) c FROM tl_rms WHERE ref_id=?")
                                 ->execute($dc->id);

		//reset rms_new_edit
        if((int)$rmsObj->c == 0)
    	{
			$this->Database->prepare("UPDATE ".$ref_Table." SET `rms_new_edit`='' WHERE id=?")
							->limit(1)
							->execute($ref_id);
		}
	}

	 /**
     * show diff between preview and active version
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function showDiff($row, $href, $label, $title, $icon, $attributes)
    {
       	// print_r($row);

       	if ($this->Input->get('key') == 'show_diff')
		{
	        $this->import('Database');

	        $objVersions = new \SvenRhinow\rms\rmsVersions($row);

	        $previewLink = $objVersions->compare();

    	}
    	$href .= "&ref_id=".$row['ref_id'];
    	return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
    }
}
