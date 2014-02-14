<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Sven Rhinow Webentwicklung 2012 <http://www.sr-tag.de>
 * @author     Stefan Lindecke  <stefan@ktrion.de>
 * @author     Sven Rhinow <kservice@sr-tag.de> 
 * @package    rms (Release Management System)
 * @license    LGPL 
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_rms_settings']['active']     = array('Freigabe-Modus aktivieren', 'Erst wenn der Haken gesetzt ist, ist die Freigabe-Verwaltung aktiv.');
$GLOBALS['TL_LANG']['tl_rms_settings']['control_group'] = array('Gruppe mit Freigabeberechtigung', 'Geben Sie die Benutzergruppe an welche eine Freigabeberechtigung haben soll.');
$GLOBALS['TL_LANG']['tl_rms_settings']['whitelist_domains'] = array('für Freigabe berücksichtigen', 'alle Unterseiten der ausgewählten Startseiten werden bei dem Freigaben berücksichtigt.');
$GLOBALS['TL_LANG']['tl_rms_settings']['sender_email']         = array('Empfangs-E-Mail', 'Hier können Sie ein oder mehrere EmailAdressen kommo-separiert eingeben.');
$GLOBALS['TL_LANG']['tl_rms_settings']['prevjump_newsletter']         = array('Newsletter Vorschauseite', 'Wählen Sie hier die Seite mit dem eingebundenen Newsletter-Detail-Modul aus');
$GLOBALS['TL_LANG']['tl_rms_settings']['prevjump_news']         = array('News Vorschauseite', 'Wählen Sie hier die Seite mit dem eingebundenen News-Detail-Modul aus');
$GLOBALS['TL_LANG']['tl_rms_settings']['prevjump_calendar_events'] = array('Veranstaltungs Vorschauseite', 'Wählen Sie hier die Seite mit dem eingebundenen Veranstaltungs-Detail-Modul aus');

?>