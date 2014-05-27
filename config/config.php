<?php
/**
 * PHP version 5
 * @copyright  Sven Rhinow Webentwicklung 2014 <http://www.sr-tag.de>
 * @author     Stefan Lindecke  <stefan@ktrion.de>
 * @author     Sven Rhinow <kservice@sr-tag.de>
 * @package    rms for Contao 3 (Release Management System)
 * @license    LGPL
 * @filesource
 */

require_once(TL_ROOT.'/system/config/localconfig.php');

if($GLOBALS['TL_CONFIG']['rms_active'])
{
    array_insert($GLOBALS['BE_MOD']['content'], 1, array
    (
    'rms' => array (
	    'tables' => array('tl_rms', 'tl_rms_settings'),
	    'icon'  => 'system/modules/rms/assets/icons/promotion.png',
	    'stylesheet' => 'system/modules/rms/assets/css/be.css',
	    'acknowledge' => array('\SvenRhinow\rms\rmsHelper','acknowdlgeEntry'),
	    )
    ));

    $GLOBALS['BE_MOD']['content']['article']['showPreview'] = array('\SvenRhinow\rms\rmsHelper', 'showPreviewInBrowser');
    $GLOBALS['BE_MOD']['content']['article']['stylesheet'] = 'system/modules/rms/assets/css/be.css';
    $GLOBALS['BE_MOD']['content']['news']['showPreview'] = array('\SvenRhinow\rms\rmsHelper', 'showPreviewInBrowser');
    $GLOBALS['BE_MOD']['content']['news']['stylesheet'] = 'system/modules/rms/assets/css/be.css';
    $GLOBALS['BE_MOD']['content']['calendar']['showPreview'] = array('\SvenRhinow\rms\rmsHelper', 'showPreviewInBrowser');
    $GLOBALS['BE_MOD']['content']['calendar']['stylesheet'] = 'system/modules/rms/assets/css/be.css';
    $GLOBALS['BE_MOD']['content']['newsletter']['showPreview'] = array('\SvenRhinow\rms\rmsHelper', 'showPreviewInBrowser');
    $GLOBALS['BE_MOD']['content']['newsletter']['stylesheet'] = 'system/modules/rms/assets/css/be.css';


    $GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('\SvenRhinow\rms\rmsHelper', 'handleBackendUserAccessControlls');
    $GLOBALS['TL_HOOKS']['getContentElement'][] = array('\SvenRhinow\rms\rmsHelper', 'previewContentElement');
    $GLOBALS['TL_HOOKS']['executePostActions'][] = array('\SvenRhinow\rms\rmsAjax', 'executePostActions');
    
}

$GLOBALS['FE_MOD']['news']['newsreader_rms'] = 'ModuleNewsReaderRMS';
$GLOBALS['FE_MOD']['newsletter']['nl_reader_rms'] = 'ModuleNewsletterReaderRMS';
$GLOBALS['FE_MOD']['events']['eventreader_rms'] = 'ModuleEventReaderRMS';

/**
* source tables that have rms enabled
*/
$GLOBALS['rms_extension']['tables'][] = 'tl_content';
$GLOBALS['rms_extension']['tables'][] = 'tl_newsletter';
$GLOBALS['rms_extension']['tables'][] = 'tl_calendar_events';
$GLOBALS['rms_extension']['tables'][] = 'tl_news';

/**
* einige Felder wie Spaltensets können ignoriert werden
*/
$GLOBALS['rms_extension']['ignoredFields'] = array('colsetStart','colsetPart','colsetEnd');
