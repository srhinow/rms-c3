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
$GLOBALS['TL_DCA']['tl_rms_tmp'] = array
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
		)
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
			'fields'                  => array('tstamp', 'ref_author','ref_table','ref_notice'),
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
				'label'               => &$GLOBALS['TL_LANG']['tl_article']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
				'button_callback'     => array('tl_rms', 'editArticle'),
				'attributes'          => 'class="contextmenu"'
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
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'ref_table' => array
		(
			'sql'					  => "varchar(255) NOT NULL default ''"
		),
		'ref_author' => array
		(
			'sql'					  => "int(10) unsigned NOT NULL default '0'"
		),
		'ref_id' => array
		(
			'sql'					  => "int(10) unsigned NOT NULL default '0'"
		),
		'status' => array
		(
			'sql'					  => "int(10) unsigned NOT NULL default '0'"
		),
		'data' => array
		(
			'sql'					  => "mediumblob NULL"
		),

	)
);
