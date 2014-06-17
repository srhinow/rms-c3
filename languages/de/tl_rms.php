<?php

/**
 * Contao Open Source CMS
 *
 * PHP version 5
 * @copyright  Sven Rhinow Webentwicklung 2012 <http://www.sr-tag.de>
 * @author     Stefan Lindecke  <stefan@ktrion.de>
 * @author     Sven Rhinow <kservice@sr-tag.de> 
 * @package    rms (Release Management System)
 * @license    LGPL 
 */

/**
* global Operation
*/
$GLOBALS['TL_LANG']['tl_rms']['show_preview'] = array('Export','Rechnungen und deren Posten in CSV-Dateien exportieren.');
  
/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_rms']['tstamp']     = array('Freigabe-Datum', 'Datum der Freigabe-Anfrage.');
$GLOBALS['TL_LANG']['tl_rms']['ref_table']     = array('geänderte Tabelle', 'referenzierte Tabelle der Änderung.');
$GLOBALS['TL_LANG']['tl_rms']['ref_author']     = array('Autor der Änderung', '');
$GLOBALS['TL_LANG']['tl_rms']['ref_id']     = array('ID', 'Eindeutige Datenbank-ID des geänderten Datensatzes');
$GLOBALS['TL_LANG']['tl_rms']['ref_notice']     = array('Freigabe-Anmerkung', 'Die Anmerkung dient dem Verantwortlichen als Hilfestellung der Änderungen.');
$GLOBALS['TL_LANG']['tl_rms']['edit_url']     = array('Pfad zur Bearbeitungsansicht', '');
$GLOBALS['TL_LANG']['tl_rms']['data']     = array('geänderte Daten', 'serialisierte Daten des Datensatzes');
$GLOBALS['TL_LANG']['tl_rms']['status']     = array('Status', 'zeigt an ob diese Freigabe bereits beantwortet wurde.');
$GLOBALS['TL_LANG']['tl_rms']['region'][0]   =  'Bereich';
$GLOBALS['TL_LANG']['tl_rms']['preview_link'][0]   =  'Vorschau-Link';
$GLOBALS['TL_LANG']['tl_rms']['last_edit'][0]   =  'letzte Bearbeitung';

$GLOBALS['TL_LANG']['tl_rms']['status_options'] = array('0'=>'unbearbeitet','1'=>'bearbeitet');
/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_rms']['settings']     = array('Freigabe-Einstellungen', '');
$GLOBALS['TL_LANG']['tl_rms']['acknowledge']     = array('Änderung freigeben', 'diese Änderung freigeben.');
$GLOBALS['TL_LANG']['tl_rms']['delete']     = array('Änderung löschen', 'diese Änderung löschen.');
$GLOBALS['TL_LANG']['tl_rms']['edit']       = array('Inhalt bearbeiten', 'Inhalt bearbeiten');
$GLOBALS['TL_LANG']['tl_rms']['show_diff']       = array('Unterschied anzeigen', 'Den bearbeiteten Unterschied anzeigen.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_content']['rm_legend']      = 'Freigabe';

/**
* Bereiche
*/
$GLOBALS['TL_LANG']['tl_rms']['sessions']['article_tl_article'] = 'Artikel';
$GLOBALS['TL_LANG']['tl_rms']['sessions']['article_tl_content'] = 'Artikel :: Inhaltselement';
$GLOBALS['TL_LANG']['tl_rms']['sessions']['newsletter_tl_newsletter'] = 'Newsletter :: Element';
$GLOBALS['TL_LANG']['tl_rms']['sessions']['newsletter_tl_content'] = 'Newsletter :: Element';
$GLOBALS['TL_LANG']['tl_rms']['sessions']['calendar_events'] = 'Event :: Element';
$GLOBALS['TL_LANG']['tl_rms']['sessions']['news_tl_news'] = 'Nachrichten :: Beitrag';
$GLOBALS['TL_LANG']['tl_rms']['sessions']['news_tl_content'] = 'Nachrichten :: Inhaltselement';

/**
* anderer Text
*/
$GLOBALS['TL_LANG']['tl_rms']['diff_new_content'] = '<h5>Der Inhalt wurde neu erstellt.</h5> <p>Es gibt daher keine Version zum Vergleich.</p>';
$GLOBALS['TL_LANG']['tl_rms']['info_new_edit'] = '*neu erstellt*';

?>
