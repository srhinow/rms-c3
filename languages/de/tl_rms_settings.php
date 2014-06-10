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
$GLOBALS['TL_LANG']['tl_rms_settings']['control_group'] = array('Gruppe mit Freigabeberechtigung', 'Geben Sie die Benutzergruppe an welche eine Freigabeberechtigung haben soll.');
$GLOBALS['TL_LANG']['tl_rms_settings']['sender_email']         = array('Empfangs-E-Mail-Adresse (Fallback)', 'Hier können Sie ein oder mehrere Email-Adressen durch ein Komma getrennt eingeben. Diese Email-Adresse wird zur Änderungsbenachrichtigung genommen falls keine zugewiesen wurde.');
$GLOBALS['TL_LANG']['tl_rms_settings']['ignore_fields']         = array('zu ignorierende Felder', 'Tragen Sie hier, durch ein Komma getrennt die Feldnamen ein die vom rms-Modul ignoriert werden sollen. Der Feldname muss der aus der Datenbank sein (z.B. ignore_fields).');

?>
