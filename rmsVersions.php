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
 * Class rmsVersions (fork from Contao-Core-Versions-Class)
 */
class rmsVersions extends \Backend
{
	/**
	 * Table
	 * @var string
	 */
	protected $rmsArr;



	/**
	 * Initialize the object
	 * @param string
	 * @param integer
	 */
	public function __construct($rmsArr)
	{
		parent::__construct();
		$this->rmsArr = $rmsArr;
	}

	/**
	 * Compare versions
	 */
	public function compare()
	{
		$strBuffer = '';
		$arrFrom = array();
		$arrTo = array();
		$intTo = 0;
		$intFrom = 0;
		$firstSave = false;

        $this->import('SvenRhinow\rms\rmsHelper', 'rmsHelper');
        $settings = $this->rmsHelper->getSettings();

		$objReference = $this->Database->prepare("SELECT * FROM " . $this->rmsArr['ref_table'] . " WHERE id=?")
										->limit(1)
									  	->execute( \Input::get('ref_id') );

		if ($objReference->numRows < 1)
		{
			$strBuffer = '<p>There are no reference of ' . $this->rmsArr['ref_table'] . '.id=' . \Input::get('ref_id') . '</p>';
		}
		else
		{
			// From			
			$from = $objReference->row();
			$intFrom = \Input::get('ref_id');

			// To
			$to = deserialize($this->rmsArr['data']);
			$intTo = $this->rmsArr['id'];


			// Only continue if both version numbers are set
			if ($intTo > 0 && $intFrom > 0)
			{
				\System::loadLanguageFile($this->rmsArr['ref_table']);
				$this->loadDataContainer($this->rmsArr['ref_table']);

				// Include the PhpDiff library
				require_once TL_ROOT . '/system/modules/core/vendor/phpdiff/Diff.php';
				require_once TL_ROOT . '/system/modules/core/vendor/phpdiff/Diff/Renderer/Html/Contao.php';

				$arrOrder = array();

				$arrFields = $GLOBALS['TL_DCA'][$this->rmsArr['ref_table']]['fields'];
				
				// if first save show text rather then different list				
				$firstSave = ($from['rms_first_save'] == 1) ? true : false;

				// Get the order fields
				foreach ($arrFields as $i => $arrField)
				{					

					if (isset($arrField['eval']['orderField']))
					{
						$arrOrder[] = $arrField['eval']['orderField'];
					}
				}

				// Find the changed fields and highlight the changes
				foreach ($to as $k=>$v)
				{

					if ($from[$k] != $to[$k])
					{

						if ($arrFields[$k]['inputType'] == 'password' || $arrFields[$k]['eval']['doNotShow'] || $arrFields[$k]['eval']['hideInput'] || $arrFields[$k]['ignoreDiff'] === TRUE)
						{
							continue;
						}

						// weitere in der Anzeige, zuignorierende Felder aus den rms-Einstellungen prüfen
				        $ignoreFieldArr = array_map('trim',explode(',',$settings['ignore_fields']));
				        if(is_array($ignoreFieldArr) && in_array($k, $ignoreFieldArr)) continue;

						$blnIsBinary = ($arrFields[$k]['inputType'] == 'fileTree' || in_array($k, $arrOrder));

						// Convert serialized arrays into strings
						if (is_array(($tmp = deserialize($to[$k]))) && !is_array($to[$k]))
						{
							$to[$k] = $this->implodeRecursive($tmp, $blnIsBinary);
						}
						if (is_array(($tmp = deserialize($from[$k]))) && !is_array($from[$k]))
						{
							$from[$k] = $this->implodeRecursive($tmp, $blnIsBinary);
						}
						unset($tmp);

						// Convert already deserialized arrays to strings - TODO Why are they already deserialzed?
						if (is_array($to[$k]))
						{
							$to[$k] = $this->implodeRecursive($to[$k], $blnIsBinary);
						}
						if (is_array($from[$k])) {
							$from[$k] = $this->implodeRecursive($from[$k], $blnIsBinary);
						}

						// Convert binary UUIDs to their hex equivalents (see #6365)
						if ($blnIsBinary && \Validator::isUuid($to[$k]))
						{
							$to[$k] = \String::binToUuid($to[$k]);
						}
						if ($blnIsBinary && \Validator::isUuid($from[$k]))
						{
							$to[$k] = \String::binToUuid($from[$k]);
						}

						// Convert date fields
						if ($arrFields[$k]['eval']['rgxp'] == 'date')
						{
							$to[$k] = \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $to[$k] ?: '');
							$from[$k] = \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $from[$k] ?: '');
						}
						elseif ($arrFields[$k]['eval']['rgxp'] == 'time')
						{
							$to[$k] = \Date::parse($GLOBALS['TL_CONFIG']['timeFormat'], $to[$k] ?: '');
							$from[$k] = \Date::parse($GLOBALS['TL_CONFIG']['timeFormat'], $from[$k] ?: '');
						}
						elseif ($arrFields[$k]['eval']['rgxp'] == 'datim' || $k == 'tstamp')
						{
							$to[$k] = \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $to[$k] ?: '');
							$from[$k] = \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $from[$k] ?: '');
						}

						// Convert strings into arrays
						if (!is_array($to[$k]))
						{
							$to[$k] = explode("\n", $to[$k]);
						}
						//auf multicolumnfelder testen (value als Array) und dann serialisieren damit es als string versioniert werden kann
						else
						{
							foreach($to[$k] as $tk => $tv)
							{
								if(is_array($tv)) $to[$k][$tk] = serialize($tv);
							}
						}

						if (!is_array($from[$k]))
						{
							$from[$k] = explode("\n", $from[$k]);
						}
						else
						{
							foreach($from[$k] as $fk => $fv)
							{
								if(is_array($fv)) $from[$k][$fk] = serialize($fv);
							}					
						}	

						$objDiff = new \Diff($from[$k], $to[$k]);											
						$strBuffer .= $objDiff->Render(new \Diff_Renderer_Html_Contao(array('field'=>($arrFields[$k]['label'][0] ?: (isset($GLOBALS['TL_LANG']['MSC'][$k]) ? (is_array($GLOBALS['TL_LANG']['MSC'][$k]) ? $GLOBALS['TL_LANG']['MSC'][$k][0] : $GLOBALS['TL_LANG']['MSC'][$k]) : $k)))));
					}
				}
			}
		}

		// Identical versions
		if ($strBuffer == '')
		{
			$strBuffer = '<p>'.$GLOBALS['TL_LANG']['MSC']['identicalVersions'].'</p>';
		}

		$objTemplate = new \BackendTemplate('be_rmsdiff');

		// Template variables
		$objTemplate->content = $strBuffer;
		$objTemplate->theme = \Backend::getTheme();
		$objTemplate->base = \Environment::get('base');
		$objTemplate->language = $GLOBALS['TL_LANGUAGE'];
		$objTemplate->title = specialchars($GLOBALS['TL_LANG']['MSC']['showDifferences']);
		$objTemplate->charset = $GLOBALS['TL_CONFIG']['characterSet'];
		$objTemplate->action = ampersand(\Environment::get('request'));
		$objTemplate->firstSave = $firstSave;
		$objTemplate->diffNewContent = $GLOBALS['TL_LANG']['tl_rms']['diff_new_content'];


		$GLOBALS['TL_CONFIG']['debugMode'] = false;
		$objTemplate->output();

		exit;
	}


	/**
	 * Implode a multi-dimensional array recursively
	 * @param mixed
	 * @param boolean
	 * @return string
	 */
	protected function implodeRecursive($var, $binary=false)
	{
		if (!is_array($var))
		{
			return $binary ? \String::binToUuid($var) : $var;
		}
		elseif (!is_array(current($var)))
		{
			return implode(', ', ($binary ? array_map('String::binToUuid', $var) : $var));
		}
		else
		{
			$buffer = '';

			foreach ($var as $k=>$v)
			{
				$buffer .= $k . ": " . $this->implodeRecursive($v) . "\n";
			}

			return trim($buffer);
		}
	}
}
