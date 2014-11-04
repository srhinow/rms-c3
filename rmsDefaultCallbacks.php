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
 * Class rmsDefaultCallbacks
 *
 * @copyright  Sven Rhinow 2004-2014
 * @author     Sven Rhinow <kservice@sr-tag.de>
 * @package    rms
 */
class rmsDefaultCallbacks extends \Backend
{
    /**
    * overwrite table-data if current BackendUser a low-level-redakteur
    * @param object
    * @param object
    * @return string or object
    */
    public function onListCallback(\DataContainer $dc, $rowArr)
    {
        $this->import("BackendUser");
        $this->import('SvenRhinow\rms\rmsHelper', 'rmsHelper');
        $settings = $this->rmsHelper->getSettings(); 

        //nötige Paramter ermitteln
        $userID =  (\Input::get("author")) ? \Input::get("author") :  $this->BackendUser->id;
        $strTable = \Input::get("table");
        $contentId = $rowArr['id'];        

        //wenn eins der nötigen Parameter fehlt -> hier abbrechen
        if(!$userID || !$strTable || !$contentId) return;
        
        //hole nicht freigegebene Daten von dem Redakteur fuer diesen Content
        $objStoredData = $this->Database->prepare("SELECT data FROM tl_rms WHERE ref_id=? AND ref_table=? AND ref_author=?")
                                        ->limit(1)
                                        ->execute
                                        (
                                            $contentId,
                                            $strTable,
                                            $userID
                                        );


        //wenn bereits eine nicht freigegebene Bearbeitung vorliegt
        if ($objStoredData->numRows > 0)
        {
            $rmsArr = unserialize($objStoredData->data);
            return $rmsArr;
        }
    }

    /**
    * overwrite table-data and backup in tmp-table if current BackendUser a low-level-redakteur
    * @param object
    * @param object
    * @return string or object
    */
    public function onEditCallback(\DataContainer $dc, $liveDataObj)
    {
        
        $this->import("BackendUser");
        $this->import('SvenRhinow\rms\rmsHelper', 'rmsHelper');
        $settings = $this->rmsHelper->getSettings();
        $liveDataArr = $liveDataObj->fetchAssoc();

        //nötige Paramter ermitteln
        $userID =  (\Input::get("author")) ? \Input::get("author") :  $this->BackendUser->id;
        $strTable = \Input::get("table");
        $contentId = \Input::get("id");

        //wenn eins der nötigen Parameter fehlt -> hier abbrechen
        if(!$userID || !$strTable || !$contentId) return;

        // falls es eine tl_content ist und der Datentyp ignoriert werden soll -> hier abbrechen
        $ignoreTypedArr = array_map('trim',explode(',',$settings['ignore_content_types']));             
        if($strTable == 'tl_content' && is_array($ignoreTypedArr) && in_array($liveDataArr['type'], $ignoreTypedArr)) return;

        //loesche evtl alte Datensätze zu diesem Element aus der tl_rms_tmp 
        $this->Database->prepare('DELETE FROM tl_rms_tmp WHERE ref_id=? AND ref_table=? AND ref_author=?')
                        ->execute
                        (
                            $contentId,
                            $strTable,
                            $userID
                        );

        //loesche alle Datensätze die älter als einen Tag sind aus der tl_rms_tmp
        $this->Database->prepare('DELETE FROM tl_rms_tmp WHERE tstamp <= ?')->execute( strtotime('-1 Month') );

        //sichere live-daten
        $set = array
        (
            'data' => serialize($liveDataArr),
            'ref_id' => $contentId,
            'ref_table' => $strTable,
            'ref_author' => $userID,
            'tstamp' => time()
        );

        $this->Database->prepare("INSERT INTO tl_rms_tmp %s")
            ->set($set)
            ->execute();

        //hole nicht freigegebene Daten von dem Redakteur fuer diesen Content
        $objStoredData = $this->Database->prepare("SELECT data FROM tl_rms WHERE ref_id=? AND ref_table=? AND ref_author=?")
                                        ->limit(1)
                                        ->execute
                                        (
                                            $contentId,
                                            $strTable,
                                            $userID
                                        );


        //wenn bereits eine nicht freigegebene Bearbeitung vorliegt
        if ($objStoredData->numRows > 0)
        {
            $rmsArr = unserialize($objStoredData->data);
            return $rmsArr;
        }

        return '';
    }

    /**
    * set or update a entry in rms-table
    * @param object
    */
    public function onSubmitCallback(\DataContainer $dc)
    {
        $this->import("BackendUser");
        $this->import('SvenRhinow\rms\rmsHelper', 'rmsHelper');
        $settings = $this->rmsHelper->getSettings();

        $userID =  (\Input::get("author")) ? \Input::get("author") :  $this->BackendUser->id;
        $strTable = \Input::get("table");
        $intId = \Input::get("id");

        if(!$userID || !$strTable || !$intId) return;

        // falls es eine tl_content ist und der Datentyp ignoriert werden soll -> hier abbrechen
        $ignoreTypedArr = array_map('trim',explode(',',$settings['ignore_content_types']));
        if($strTable == 'tl_content' && is_array($ignoreTypedArr) && in_array($dc->activeRecord->type, $ignoreTypedArr)) return;

        // Get the currently available fields
        $arrFields = array_flip($this->Database->getFieldnames($strTable));

        //create db-field-array with new data
        foreach($arrFields as $fieldName => $colNum)
        {
            if(in_array($fieldName, array('PRIMARY','INDEX'))) continue;
            $newData[$fieldName] = $dc->activeRecord->{$fieldName};
        }

        //hole gesicherte und freigegebene Daten von dem Redakteur für diesen Content
        $tmpDataObj = $this->Database->prepare("SELECT data FROM tl_rms_tmp WHERE ref_id=? AND ref_table=? AND ref_author=?")
            ->limit(1)
            ->execute
            (
                $intId,
                $strTable,
                $userID
            );

        //wenn z.B. der Datensatz neu angelegt wurde
        if($tmpDataObj->numRows > 0) $data = unserialize($tmpDataObj->data);
        else $data = $newData;


        // create / first-save
        $isNewEntryObj = $this->Database->prepare('SELECT count(*) c FROM `'.$strTable.'` WHERE `id`=? AND `tstamp`=?')
                        ->limit(1)
                        ->execute($intId,0);

        if ((int) $isNewEntryObj->c == 1)
        {
            $data['tstamp'] = time();
            $data['rms_first_save'] = 1;
        }

        //overwrite with live-data
        $data['rms_new_edit'] = 1;
        $data['rms_ref_table'] = $strTable;
        $data['rms_notice'] = $newData['rms_notice'];

        $objUpdate = $this->Database->prepare("UPDATE ".$strTable." %s WHERE id=?")->set($data)->execute($intId);

        //status
        $status = $this->rmsHelper->isMemberOfMasters() ?  1 : 0;

        //overwrite with new-data
        $newRmsData = ($data['type'] == $newData['type']) ? array_merge($data, $newData) : $newData;

        // create an BE-URL-String to edit
        $getParamArr = array('do','table','id','act');
        $urlParams = array();
        foreach($getParamArr as $param)
        {
            if( strlen(\Input::get($param)) > 0 ) $urlParams[] = $param.'='.\Input::get($param);
        }

        // get root-parent-table
        $pTable = ($strTable == 'tl_content') ? $dc->activeRecord->ptable : $GLOBALS['TL_DCA'][$strTable]['config']['ptable'];
        $rootPTable = $this->rmsHelper->getRootParentTable($strTable,$pTable);

        // hole die email und vorschau-url
        $sectionSettings = $this->rmsHelper->getRmsSectionSettings($intId, $strTable, $pTable);

        $arrSubmitData = array
        (
            'tstamp' => time(),
            'ref_id' => $intId,
            'ref_table' =>  $strTable,
            'ref_author' => $userID,
            'ref_notice' => $newRmsData['rms_notice'],
            'do' =>  \Input::get('do'),
            'edit_url' =>  implode('&',$urlParams),
            'root_ptable' => $rootPTable,
            'master_id' => $sectionSettings['master_id'],
            'master_email' => $sectionSettings['master_email'],
            'preview_jumpTo' => $sectionSettings['preview_jumpTo'],
            'status' => $status,
            'data'=> $newRmsData
        );

        //existiert schon eine Bearbeitung
        $objData = $this->Database->prepare("SELECT id FROM tl_rms WHERE ref_id=? AND ref_table=? AND ref_author=?")
                                    ->execute(
                                        $this->Input->get("id"),
                                        $this->Input->get("table"),
                                        $userID );

        if ($objData->numRows == 1)
        {
             $this->Database->prepare("UPDATE tl_rms %s WHERE id=?")
                ->set($arrSubmitData)
                ->execute($objData->id);
        }
        else
        {
            $this->Database->prepare("INSERT INTO tl_rms %s")->set($arrSubmitData)->execute();
        }

    }   

    /**
    * oncut 
    */
    public function onCutCallback(\DataContainer $dc)
    {
        // ToDo: der der Inhalt ja evtnetuell schon freigeeben wurde und auch plötzlich nicht verschwinden darf nur weil es an einer anderen Stelle stehen soll
        // ist der Umgang als Freigabe-Schutz noch unklar.
    }

    /**
    * delete from rms-table when item delete
    * @var object
    */
    public function onDeleteCallback(\DataContainer $dc)
    {
        $userID =  (\Input::get("author")) ? \Input::get("author") :  $this->BackendUser->id;
        $strTable = \Input::get("table");
        $intId = \Input::get("id");

        $objStoredData = $this->Database->prepare("DELETE FROM tl_rms WHERE ref_id=? AND ref_table=?")                                        
                                        ->execute
                                        (
                                            $intId,
                                            $strTable
                                        );

        //bei neu angelegte Inhalte die origninalen Inhalte auch löschen. #8)                                  
        $this->Database->prepare("DELETE FROM ".$strTable." WHERE `id` = ? AND `rms_first_save`=?")
        ->limit(1)
        ->execute($intId,1);
    }

    /**
    * overwrite only rms-date und reset live-data
    * @param integer
    * @param string
    * @param array
    * @param integer
    */
    public function onRestoreCallback($intPid, $strTable, $data, $intVersion)
    {
        $this->import('SvenRhinow\rms\rmsHelper', 'rmsHelper');

        if($this->rmsHelper->isMemberOfMasters() && \Input::get('author'))
        {

            //hole letzte Livedaten
            $tmpDataObj = $this->Database->prepare("SELECT `data` FROM `tl_rms_tmp` WHERE  ref_id=? AND ref_table=? AND ref_author=?")
                                        ->limit(1)
                                        ->execute
                                        (
                                            $intPid,
                                            $strTable,
                                            \Input::get('author')
                                        );

            $liveData = ($tmpDataObj->numRows > 0) ? unserialize($tmpDataObj->data) : $data;

            //ueberschreibe wieder die Livedaten
            $this->Database->prepare("UPDATE ".$strTable." %s  WHERE id=?")
                            ->set($liveData)
                            ->execute($intPid);

            //ersetze frei-zugebenedes Release mit Versions-Daten
            $set = array('data' => $data);
            $this->Database->prepare("UPDATE tl_rms %s WHERE ref_id=? AND ref_table=? AND ref_author=?")
                ->set($set)
                ->execute($intPid, $strTable, \Input::get('author'));
        }
    }
}
