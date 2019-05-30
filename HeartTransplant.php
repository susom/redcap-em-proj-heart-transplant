<?php
/**
 * Created by PhpStorm.
 * User: jael
 * Date: 10/24/18
 * Time: 7:11 AM
 */
namespace Stanford\HeartTransplant;


use ExternalModules\ExternalModules;
use REDCap;
use DateTime;
use Message;

require_once("src/emLoggerTrait.php");

class HeartTransplant extends \ExternalModules\AbstractExternalModule {

    use emLoggerTrait;


    function editDeathData($coded, $text, $date) {

        //check that the MRN and DOT finds a match
        $codedArray = array();
        foreach ($coded as $field_name => $field_value) {
            $codedArray[$field_name] = $field_value;
        }

        $textArray = array();
        foreach ($text as $field_name => $field_value) {
            $textArray[$field_name] = $field_value;
        }

        $dateArray = array();
        foreach ($date as $field_name => $field_value) {

            if (isset($field_value)) {
                $date = new \DateTime($field_value);
                $date_str = $date->format('Y-m-d');

                $dateArray[$field_name] = $date_str;
            }
        }

        $this->emDebug($coded, $text, $date);

        $mrn_fix = $textArray['mrn_fix'];
        $dot = $dateArray['dot'];

        $filter = "[mrn_fix] = '{$mrn_fix}'";
        if (isset($dot)) {
            $filter .= " AND [dot] = '{$dot}'";
        }

        $params = array(
            'return_format'    => 'json',
            //'records'          => $record,
            //'events'           => $filter_event,
            'fields'           => array(REDCap::getRecordIdField(), 'mrn_fix', 'dot'),
            'filterLogic'      => $filter
        );

        $q = REDCap::getData($params);

        //$this->emDebug($filter, $params, isset($dot));
        $records = json_decode($q, true);

        $this->emDebug($records);
        if (empty($records)) {

            $status = array(
                'status' => false,
                'msg'    => "Please check your entry! The MRN and Date of Transplant was not found."
            );
            return $status;
        }

        $msg = null;

        //todo: check that there was only one found. if multiple found, notify the admin
        $count = sizeof($records);
        $this->emDebug("count is $count.");
        if ($count > 1) {
            $msg[] = "Please notify your admin that $count records were found with this MRN  and date of transplant.";
            $this->emDebug("MORE THAN 1 record found!");
        }

        //get the record_id from the retrieved record
        $save_id = (current($records))[REDCap::getRecordIdField()];

        //save the record changes
        $data = array_merge(
            array(REDCap::getRecordIdField() => $save_id),
            $codedArray,
            //$textArray,
            $dateArray   //REDCap won't rewrite same value
        );

        $this->emDebug($save_id, $codedArray, $textArray, $dateArray,$data);

        $q = REDCap::saveData('json', json_encode(array($data)));

        if (empty($q['errors'])) {
            $msg[] = "Successfully saved in record $save_id";
             $status = array(
                'status' => true,
                'msg'    => implode("\n", $msg)
            );
        } else {
            $msg[] = "There was an issue editing the record. Please notify your admin.";
            $status = array(
                'status' => false,
                'msg'    => implode("\n", $msg)
            );
        }
        $this->emDebug($q);
        return $status;



    }

    function saveNewEntry($coded, $text, $date) {

        $new_id = $this->getNextHighestId();
        //$new_id = 2032;

        $codedArray = array();
        foreach ($coded as $field_name => $field_value) {
            $codedArray[$field_name] = db_escape($field_value);
        }

        $textArray = array();
        foreach ($text as $field_name => $field_value) {
            $textArray[$field_name] = $field_value;
        }

        $dateArray = array();
        foreach ($date as $field_name => $field_value) {

            if (isset($field_value)) {
                $date = new \DateTime($field_value);
                $date_str = $date->format('Y-m-d');

                $dateArray[$field_name] = $date_str;
            }
        }

        //check that the mrn_fix and dot don't already exist
        $mrn_fix = $textArray['mrn_fix'];  //todo: check that it's only numbers

        if (!is_numeric($mrn_fix)) {
            $status = array(
                'status' => false,
                'msg'    => "Please check your entry! The MRN should only contain numbers."
            );
            return $status;
        }
        $dot = $dateArray['dot'];          //todo: check that it's date. should be handled by cal widget

        $filter = "[mrn_fix] = '{$mrn_fix}'";
        if (isset($dot)) {
            $filter .= " AND [dot] = '{$dot}'";
        }

        $params = array(
            'return_format'    => 'json',
            //'records'          => $record,
            //'events'           => $filter_event,
            'fields'           => array(REDCap::getRecordIdField(), 'mrn_fix', 'dot'),
            'filterLogic'      => $filter
        );

        $q = REDCap::getData($params);

        //$this->emDebug($filter, $params, isset($dot));
        $records = json_decode($q, true);

        $this->emDebug($records);
        if (!empty($records)) {

            $status = array(
                'status' => false,
                'msg'    => "Please check your entry! The MRN and Date of Transplant already exists."
            );
            return $status;
        }


        $data = array_merge(
            array(REDCap::getRecordIdField() => $new_id),
            $codedArray,
            $textArray,
            $dateArray
        );

        //$this->emDebug($new_id, $codedArray, $textArray, $dateArray,$data);

        $q = REDCap::saveData('json', json_encode(array($data)));

        if (empty($q['errors'])) {
            $msg[] = "Successfully saved in record $new_id";
             $status = array(
                'status' => true,
                'msg'    => implode("\n", $msg)
            );
        } else {
            $msg[] = "There was an issue saving this record $new_id. Please notify your admin.";
            $status = array(
                'status' => false,
                'msg'    => implode("\n", $msg)
            );
        }
        $this->emDebug($q);
        //return true;
        return $status;

    }


    public function getNextHighestId() {

        //$this->Proj = new Project($pid);
        //$recordIdField = $thisProj->table_pk;

        $id_field = REDCap::getRecordIdField();
        //$q = REDCap::getData($pid,'array',NULL,array($id_field), $event_id);
        $q = REDCap::getData('array',NULL,array($id_field));
        //$this->emDebug($q, "DEBUG", "Found records in project $pid using $id_field");
        $maxid = max(array_keys($q));
        //$this->emDebug("MAX ID : ".$maxid);
        return $maxid + 1;
    }



    function getDemographicsBlurb($id) {
        $q = REDCap::getData(
            'json', $id,
            $this->getProjectSetting('demo-fields'));
//            NULL, TRUE, FALSE, FALSE, NULL, TRUE);
        $results = json_decode($q, true);
        $result = $results[0];

        $demo_labels = array(
            'dem_transplant_number' => 'Transplant Number',
            'dem_last_name'         => 'Last Name',
            'dem_first_name'         => 'First Name',
        );

        $full_width_fields = array('dem_transplant_number');

       $d = array();
        foreach ($result as $field => $value) {
            $width = in_array($field, $full_width_fields) ? 12 : 6;
            $d[] = "
                <div class='col-sm-$width'>
                    <span class='d_label'>" .
                        (isset($demo_labels[$field]) ? $demo_labels[$field] : ucfirst($field)) .
                    ":</span>
                    <span class='d_value'>" . htmlspecialchars($value) . "</span>
                </div>";
        }
        return implode("", $d);
    }

    function getTestsBlurb($id) {
        $q = REDCap::getData(
            'json', $id,
            $this->getProjectSetting('test-fields'));
//            NULL, TRUE, FALSE, FALSE, NULL, TRUE);
        $results = json_decode($q, true);
        $result = $results[0];
    }

}

