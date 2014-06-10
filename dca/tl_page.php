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
 * Table tl_page
 */
if($GLOBALS['TL_CONFIG']['rms_active'])
{
   
	// Palettes
	$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'rms_protected';
	$GLOBALS['TL_DCA']['tl_page']['palettes']['root'] .=  ';{rms_legend:hide},rms_protected';

	// Subpalettes
	$GLOBALS['TL_DCA']['tl_page']['subpalettes']['rms_protected'] = 'rms_master_member';
    
	// Fields
	$GLOBALS['TL_DCA']['tl_page']['fields']['rms_protected'] = array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_page']['rms_protected'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true),
			'sql'                     => "char(1) NOT NULL default ''"
	);
    $GLOBALS['TL_DCA']['tl_page']['fields']['rms_master_member'] = array
    (
		'label'                   => &$GLOBALS['TL_LANG']['tl_page']['rms_master_member'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'foreignKey'              => 'tl_user.name',
		'eval'                    => array('mandatory'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
		'sql'                     => "int(10) unsigned NOT NULL default '0'",
		'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
    );	
}
/**
 * Class tl_page_rms
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @package    Controller
 */
class tl_page_rms extends \Backend
{

}
