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
		'onload_callback' => array
		(
			array('tl_rms', 'filterMemberEntries')

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
				'attributes'		  => 'onclick="Backend.openModalIframe({\'width\':765,\'title\':\'' . ($GLOBALS['TL_LANG']['tl_rms']['show_diff'][0]) . '\',\'url\':this.href});return false"',
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
		'do' => array
		(
			'sql'					  => "varchar(55) NOT NULL default ''"
		),		
		'edit_url' => array
		(
			'sql'					  => "longtext NULL"
		),
		'master_id' => array
		(
			'sql'					  => "int(10) unsigned NOT NULL default '0'"
		),
		'master_email' => array
		(
			'sql'					  => "varchar(255) NOT NULL default ''"
		),		
		'preview_jumpTo' => array
		(
			'sql'					  => "longtext NULL"
		),
		'root_ptable' => array
		(
			'sql'					  => "varchar(255) NOT NULL default ''"
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
	* list only Entries for the cureent master-member
	*/
	public function filterMemberEntries()
	{
		$this->import('BackendUser');
		if(!$this->BackendUser->isAdmin)
		{
			$GLOBALS['TL_DCA']['tl_rms']['list']['sorting']['filter'] = array(
				array('master_id=?', $this->BackendUser->id)
			);
		}
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


		//get referenz
		$refObj = $this->Database->prepare('SELECT * FROM '.$row['ref_table'].' WHERE `id`=? ')->limit(1)->execute($row['ref_id']);

		$ifFirstSave = ($refObj->rms_first_save == 1) ? '<span style="padding:0 10px; font-weight:bold;">'.$GLOBALS['TL_LANG']['tl_rms']['info_new_edit'].'</span>': '';

		$label  = '<strong>'.$GLOBALS['TL_LANG']['tl_rms']['status'][0].':</strong><span class="status_'.$row['status'].'"> '.$GLOBALS['TL_LANG']['tl_rms']['status_options'][$row['status']].'</span>'.$ifFirstSave.'<br>';
		$label .= '<strong>'.$GLOBALS['TL_LANG']['tl_rms']['region'][0].':</strong> '.$GLOBALS['TL_LANG']['tl_rms']['sessions'][$row['do'].'_'.$row['ref_table']].'<br>';
		$label .= '<strong>'.$GLOBALS['TL_LANG']['tl_rms']['preview_link'][0].': </strong><a href="'.$row['preview_jumpTo'].'" target="_blank">'.$row['preview_jumpTo'].'</a><br>';
		$label .= '<strong>'.$GLOBALS['TL_LANG']['tl_rms']['ref_author'][0].':</strong> '.$userObj->name.' (<a href="mailto:' . $userObj->email . '">'.$userObj->email.'</a>)<br>';
		$label .= '<strong>'.$GLOBALS['TL_LANG']['tl_rms']['last_edit'][0].':</strong> '.date($GLOBALS['TL_CONFIG']['datimFormat'],$row['tstamp']).'<br>';
		$label .= '<strong>'.$GLOBALS['TL_LANG']['tl_rms']['ref_notice'][0].':</strong> '.nl2br($row['ref_notice']);



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
		return  '<a href="'.$this->addToUrl($row['edit_url'].'&author='.$row['ref_author']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
		// return  '<a href="'.$this->Environment->scriptName.'?'.$row['edit_url'].'&amp;author='.$row['ref_author'].'&amp;rt='.\Input::get('rt').'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}

	public function setUnEditData(DataContainer $dc)
	{

        $ref_Table = $dc->activeRecord->ref_table;
        $ref_id = $dc->activeRecord->ref_id;

		//wait other edits for release
		$rmsObj = $this->Database->prepare("SELECT count(*) c FROM tl_rms WHERE ref_id=?")
                                 ->execute($dc->id);

		
        if((int)$rmsObj->c == 0)
    	{
			//reset rms_new_edit
			$this->Database->prepare("UPDATE ".$ref_Table." SET `rms_new_edit`='' WHERE id=?")
							->limit(1)
							->execute($ref_id);
			
			//delete new empty elements
			$this->Database->prepare('DELETE FROM '.$ref_Table.' WHERE `id`=? AND `rms_first_save`=?')
							->execute($ref_id, 1);
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
       	if ($this->Input->get('key') == 'show_diff' && $row['ref_id'] == \Input::get('ref_id'))
		{
	        $objVersions = new \SvenRhinow\rms\rmsVersions($row);
	        $objVersions->compare();
    	}

    	$href .= "&ref_id=".$row['ref_id'];
    	return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
    }
}
