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
require_once("RepeatingForms.php");
//use Stanford\HeartTransplant\RepeatingForms;



class HeartTransplant extends \ExternalModules\AbstractExternalModule {

    use emLoggerTrait;


    function editDeathData($coded, $text, $date) {

        $this->emDebug($coded, $text, $date);

        //check that the MRN and DOT finds a match
        $codedArray = array();
        foreach ($coded as $field_name => $field_value) {
            $codedArray[$field_name] = $field_value;
        }

        $textArray = array();
        foreach ($text as $field_name => $field_value) {
            if (!empty($field_value)) {
                $textArray[$field_name] = $field_value;
            }
        }

        $dateArray = array();
        foreach ($date as $field_name => $field_value) {

            //$this->emDebug($field_value, isset($field_value), empty($field_value)); exit;

            if (!empty($field_value)) {
                $date = new \DateTime($field_value);
                $date_str = $date->format('Y-m-d');

                $dateArray[$field_name] = $date_str;
            }
        }

        //$this->emDebug($save_id, $codedArray, $textArray, $dateArray); exit;


        $stanford_mrn = $textArray['stanford_mrn'];
        $dot = $dateArray['dot'];

        $filter = "[stanford_mrn] = '{$stanford_mrn}'";
        if (isset($dot)) {
            $filter .= " AND [dot] = '{$dot}'";
        }

        $params = array(
            'return_format'    => 'json',
            //'records'          => $record,
            //'events'           => $filter_event,
            'fields'           => array(REDCap::getRecordIdField(), 'stanford_mrn', 'dot'),
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

        //$this->emDebug($coded, $text, $date); exit;

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

            if (!empty($field_value)) {
                $date_cand = new \DateTime($field_value);
                $date_str = $date_cand->format('Y-m-d');

                $dateArray[$field_name] = $date_str;
            }
        }

        //$this->emDebug($save_id, $codedArray, $textArray, $dateArray); exit;

        //check that the stanford_mrn and dot don't already exist
        $stanford_mrn = $textArray['stanford_mrn'];  //todo: check that it's only numbers

        if (!is_numeric($stanford_mrn)) {
            $status = array(
                'status' => false,
                'msg'    => "Please check your entry! The MRN should only contain numbers."
            );
            return $status;
        }
        $dot = $dateArray['dot'];          //todo: check that it's date. should be handled by cal widget

        $filter = "[stanford_mrn] = '{$stanford_mrn}'";
        if (isset($dot)) {
            $filter .= " AND [dot] = '{$dot}'";
        }

        $params = array(
            'return_format'    => 'json',
            //'records'          => $record,
            //'events'           => $filter_event,
            'fields'           => array(REDCap::getRecordIdField(), 'stanford_mrn', 'dot'),
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

    function determineDateOfTransplant($stanford_mrn, $last_name) {
        $this->emDebug("STANFORD MRN:". $stanford_mrn . " LASTNAME: ". $last_name);

        if (empty($stanford_mrn)) {

            $status = array(
                'status' => false,
                'msg'    => "Please check your entry! The MRN was not entered."
            );
            return $status;
        }

        if (empty($last_name)) {

            $status = array(
                'status' => false,
                'msg'    => "Please check your entry! The last name was not entered."
            );
            return $status;
        }

        //ideally would like to include last name in the filter, but since can't ignore cap in redcap filter, search after
        $filter = "[stanford_mrn] = '{$stanford_mrn}'";

        $params = array(
            'return_format'    => 'json',
            //'records'          => $record,
            //'events'           => $filter_event,
            'fields'           => array(REDCap::getRecordIdField(), 'stanford_mrn', 'dot', 'last_name'),
            'filterLogic'      => $filter
        );

        $q = REDCap::getData($params);

        //$this->emDebug($filter, $params, isset($dot));
        $records = json_decode($q, true);

        if (empty($records)) {

            $status = array(
                'status' => false,
                'msg'    => "Please check your entry! The MRN was not found."
            );
            return $status;
        }

        //check that all the options have last name that match
        foreach ($records as $key => $record) {

            $rec_lname = $record['last_name'];
            if (strtoupper(trim($rec_lname)) === strtoupper(trim($last_name))) {
                $cands[] = $record;
            }
        }
        //$this->emDebug(reset($records), $cands);

        $msg = null;

        //todo: check that there was only one MRN found. if multiple found, pick the most recent date of transplant
        $count = sizeof($cands);

        if ($count > 1) {

            list($save_id, $dot) = $this->findTransplantNumberMaxDate($cands);

            $dot_year = substr($dot, 0,4);

            //$msg[] = "Please notify your admin that $count records were found with this MRN. The more recent date of transplant $dot will be used. ".
             //"If this is the wrong Date of Transplant, do not enter data and inform your admin.";
            //$this->emDebug("MORE THAN 1 record found!");

            $msg[] = "The MRN has been located and updates will entered for the transplant that occurred in $dot_year. ".
                "If that is the wrong year, please do not enter data and inform your admin.";

            $status = array(
                'status' => true,
                'transplant_num' => $save_id,
                'dot_year'  => $dot_year,
                'msg'    => $msg
            );
            //return $status;


        } else {
            //get the record_id from the retrieved record
            $save_id = (current($cands))[REDCap::getRecordIdField()];
            $dot = (current($cands))['dot'];

            $dot_year = substr($dot, 0,4);

            $msg[] = "The MRN has been located and updates will entered for the transplant that occurred in $dot_year. ".
                "If that is the wrong year, please do not enter data and inform your admin.";

            $status = array(
                'status' => true,
                'transplant_num' => $save_id,
                'dot_year'  => $dot_year,
                'msg'    => $msg
            );
            //return $status;
        }

        //get the autopopulate data
        $autopopulate_data = array(
            REDCap::getRecordIdField(),
            'r_followup_dialysis',   //for autopopulate
            'r_post_dialysis_date',  //for autopopulate
            'post_icd',              //for autopopulate
            'post_icd_date'          //for autopopulate
        );

        $params = array(
            'return_format'    => 'json',
            'records'          => $save_id,
            'fields'           => $autopopulate_data
        );

        $q = REDCap::getData($params);

        $records = json_decode($q, true);

        $status['r_followup_dialysis']  = $cands[0]['r_followup_dialysis'];
        $status['r_post_dialysis_date'] = $cands[0]['r_post_dialysis_date'];
        $status['post_icd']             = $cands[0]['post_icd'];
        $status['post_icd_date']        = $cands[0]['post_icd_date'];

        return $status;


        //$this->emDebug($save_id, $codedArray, $textArray, $dateArray, $dateMonthArray,$data);
        //$this->emDebug("TRANSPLANT NUMBER: ".$save_id,$data);
    }


    function saveAnnualUpdate($coded, $text, $date, $date_month, $checked, $transplant_num) {
        $msg = array();

        //$this->emDebug($coded, $text, $date, $date_month, $checked, $transplant_num);

        //double check taht the transplant number and dot didn't go missing in the return trip
        if (empty($transplant_num)) {
            $msg[] = "Transplant number is missing. Please notify your admin.";
            $status = array(
                'status' => false,
                'msg'    => implode("\n", $msg)
            );
            return $status;
        }


        /**
         * TODO: can't get repeatingforms to work
        $rf = new \RepeatingForms($this->getProjectId(), 'left_heart_cath_data');
        $rf->loadData($transplant_num);
        //$data = $rf->getAllInstances($pid, $pmon_event_id);

        $next_instance = $rf->getNextInstanceId($transplant_num);
        //$saved = $rf->saveInstance($pid, $notify_user, $next_instance, $pmon_event_id);

        $this->emDebug($next_instance);  exit;

         */

        //handle the repeating forms first


        //1. Save Left_heart_cath_data
        //iterate through the checkbox and pull all the rt_cth_vessel_angiogram
        $data_array = array();
        foreach ($checked as $key => $field) {
            //pull out the field which belongs in left_heart_cath_data
            if (stristr($field, 'rt_cth_vessel_angiogram')) {
                $data_array[$field] = 1;
                unset($checked[$key]);
            }
        }
        $data_array['rt_cth_date_angiogram'] = $date['rt_cth_date_angiogram'];
        unset($date['rt_cth_date_angiogram']);
        $data_array['rt_cth_mit'] = $text['rt_cth_mit'];
        unset($text['rt_cth_mit']);

        //$this->emDebug($data_array);
        $msg_cath = $this->saveRepeatingInstance($transplant_num,'left_heart_cath_data', $this->getFirstEventId(),$data_array);

        $msg  = array_merge($msg, $msg_cath);

        //2. Save Echo instrument separately as it is repeating.
        $echo_data_array = array();
        $echo_data_array['rt_ech_date'] = $date['rt_ech_date'];
        unset($date['rt_ech_date']);
        $echo_data_array['rt_ech_dse'] = $coded['rt_ech_dse'];
        unset($coded['rt_ech_dse']);
        $echo_data_array['rt_ech_type'] = $coded['rt_ech_type'];
        unset($coded['rt_ech_type']);
        $echo_data_array['rt_ech_lvef'] = $text['rt_ech_lvef'];
        unset($text['rt_ech_lvef']);

        //$this->emDebug( $echo_data_array);
        $msg_echo = $this->saveRepeatingInstance($transplant_num,'echo', $this->getFirstEventId(),$echo_data_array);

        $msg  = array_merge($msg, $msg_echo);

        //Check for existence of  solid tumor malignancy
        $mal_prefix = $this->determineWhichMalignancyFields($transplant_num);

        $codedArray = array();
        foreach ($coded as $field_name => $field_value) {
            $codedArray[$field_name] = db_escape($field_value);
        }

        $textArray = array();
        foreach ($text as $field_name => $field_value) {

            if (!empty($field_value)) {
                $textArray[$field_name] = $field_value;
            }
        }

        $dateArray = array();
        foreach ($date as $field_name => $field_value) {
            //$this->emDebug($field_name, $field_value, empty($field_value));

            if (!empty($field_value)) {
                //handle mal_date_sot, mal_date_ptld differently. there may be 2 of them so use first available field

                //$this->emDebug($field_name, strval($field_name),'mal_date_sot', strval(trim($field_name)) ===strval('mal_date_sot'), strcmp($field_name, 'mal_date_sot'));
                //need to trim! otherwise doesn't match
                if (strval(trim($field_name)) === 'mal_date_sot') {
                    if ($mal_prefix === false) {
                        //both malignancy fields are used, so don't load
                        $msg[] = "Malignancy fields (date and type) were not entered. Please notify your admin.";
                        continue;
                    }
                    $field_name = 'mal_date_sot'.$mal_prefix;
                }


                $date_cand = new \DateTime($field_value);
                $date_str = $date_cand->format('Y-m-d');


                $dateArray[$field_name] = $date_str;
            }
        }

        $dateMonthArray = array();
        foreach ($date_month as $field_name => $field_value) {

            if (!empty($field_value)) {
                $date_cand = new \DateTime($field_value);
                $date_str = $date_cand->format('Y-m');

                $dateArray[$field_name] = $date_str;
            }
        }

        $checkedArray = array();
        foreach ($checked as $key => $checked_field ) {
            //fields are like 'post_mal_type___1' vs 'post_mal_type_2___1'
            //$this->emDebug($checked_field, strval(trim($checked_field)),'post_mal_type', stristr(strval(trim($checked_field)),'post_mal_type'));
            if (stristr(strval(trim($checked_field)),'post_mal_type')) {
                //found mal_date_sot checked fields, so add suffix

                if ($mal_prefix === false) {
                    //both malignancy fields are used, so don't load
                    //$msg[] = "Malignancy fields (date and type) were not entered. Please notify your admin.";
                    continue;
                }

                $re = '/(?<fieldname>.*)(?<code>___\d*)/m';

                preg_match_all($re, trim($checked_field), $matches, PREG_SET_ORDER, 0);

                // Print the entire match result
                $checked_field = 'post_mal_type'.$mal_prefix.$matches[0]['code'];
                $this->emDebug($checked_field);
            }

            $checkedArray[$checked_field] = 1;
        }

        //$this->emDebug($save_id, $codedArray, $textArray, $dateArray,$checkedArray); exit;
/*
        //check that the stanford_mrn and dot don't already exist
        $stanford_mrn = $textArray['stanford_mrn'];

        $this->emDebug("STANFORD MRN:". $stanford_mrn);

        if (empty($stanford_mrn)) {

            $status = array(
                'status' => false,
                'msg'    => "Please check your entry! The MRN was not entered."
            );
            return $status;
        }

        $filter = "[stanford_mrn] = '{$stanford_mrn}'";

        $params = array(
            'return_format'    => 'json',
            //'records'          => $record,
            //'events'           => $filter_event,
            'fields'           => array(REDCap::getRecordIdField(), 'stanford_mrn', 'dot'),
            'filterLogic'      => $filter
        );

        $q = REDCap::getData($params);

        $this->emDebug($filter, $params, isset($dot));
        $records = json_decode($q, true);

        $this->emDebug($records);
        if (empty($records)) {

            $status = array(
                'status' => false,
                'msg'    => "Please check your entry! The MRN was not found."
            );
            return $status;
        }

        $msg = null;

        //todo: check that there was only one MRN found. if multiple found, pick the most recent date of transplant
        $count = sizeof($records);
        $this->emDebug("count is $count.");
        if ($count > 1) {

            list($save_id, $dot) = $this->findTransplantNumberMaxDate($records);


            $msg[] = "Please notify your admin that $count records were found with this MRN  and date of transplant  $dot will used.";
            $this->emDebug("MORE THAN 1 record found!");
        } else {
            //get the record_id from the retrieved record
            $save_id = (current($records))[REDCap::getRecordIdField()];
            $dot = (current($records))['dot'];
        }
 */


        $data = array_merge(
            array(REDCap::getRecordIdField() => $transplant_num),
            $codedArray,
            $textArray,
            $dateArray,
            $dateMonthArray,
            $checkedArray
        );

        //$this->emDebug($save_id, $codedArray, $textArray, $dateArray, $dateMonthArray,$data);
        //$this->emDebug("TRANSPLANT NUMBER: ".$transplant_num,$data);
        $q = REDCap::saveData('json', json_encode(array($data)));

        if (empty($q['errors'])) {
            $msg[] = "Successfully saved in record $transplant_num";
            $status = array(
                'status' => true,
                'msg'    => implode("\n", $msg)
            );
        } else {
            $msg[] = "There was an issue saving this record $transplant_num. Please notify your admin.";
            $status = array(
                'status' => false,
                'msg'    => implode("\n", $msg)
            );
        }
        $this->emDebug($q);
        //return true;
        return $status;

}


/**
 * @param $transplant_num

 */

    /**
     * There are two sets of malignancy fields
     * For ex:  mal_date_sot vs mla_date_sot_2
     * Use in sequence, so check if first set is populated. If used, check if second set is populated.
     * Return null if both are used, '_2' if first is used and '' if none are entered.
     *
     * @param $transplant_num
     * @return string|null
     */
    function determineWhichMalignancyFields($transplant_num) {

        $params = array(
            'return_format'    => 'json',
            'records'          => $transplant_num,
            'fields'           => array('mal_date_sot', 'post_mal_type', 'mal_date_sot_2', 'post_mal_type_2')
        );

        //$this->emDebug($params);
        $q = REDCap::getData($params);

        $records = json_decode($q, true);

        //none found so use the first one
        if (empty($records)) {
            $mal_date_suffix = '';
        } else if (empty($records[0]['mal_date_sot']) and empty(array_filter(array($this->getFields(array('post_mal_type')))))) {
            $mal_date_suffix = '';
        } else if (empty($records[0]['mal_date_sot_2']) and empty(array_filter(array($this->getFields(array('post_mal_type_2')))))) {
            $mal_date_suffix = '_2';
        } else {
            $mal_date_suffix = false; //both slots are taken, don't enter
        }

        //$this->emDebug("USING THIS MAL_DATE SUFFIX: ". $mal_date_suffix, $mal_date_suffix === '', $mal_date_suffix === false);
        return $mal_date_suffix;

    }


    function findTransplantNumberMaxDate($records) {
        $transplant_number = null;
        $max_date = null;

        $begin = new DateTime($_POST["start_date"]);
        $end = new DateTime($_POST["end_date"]);
        $today = new DateTime();
        if ($end > $today) {
            $end = $today;
        }
        $begin_str = $begin->format('Y-m-d');
        $end_str = $end->format('Y-m-d');

        foreach ($records as $k => $transplant) {
            //$cand_date = new DateTime($transplant['dot']);  no need since string is yyyy-mm-dd
            if ($transplant['dot'] > $max_date) {
                $max_date = $transplant['dot'];

                $transplant_number = $transplant[REDCap::getRecordIdField()];
                $this->emDebug("New max date is ".$max_date. " with t_num: ".$transplant_number);
            } else {
                //$reject_date =  new DateTime($transplant['dot']);
                $reject_date = $transplant['dot'];
                //$transplant_number = $transplant[REDCap::getRecordIdField()];
                $this->emDebug("Rejected date is $reject_date. Date is still the previous : ".$transplant_number);
            }
        }

        return array($transplant_number, $max_date);
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

    /**
     * Return the next instance id for this survey instrument
     *
     * Using the getDAta with return_format = 'array'
     * the returned nested array :
     *  $record
     *    'repeat_instances'
     *       $event
     *          $instrument
     *
     *
     * @return int|mixed
     */
    public function getNextRepeatingInstanceID($record, $instrument, $event) {

        //$module->emDebug($record, $instrument);
        //getData for all surveys for this reocrd
        //get the survey for this day_number and survey_data
        //TODO: return_format of 'array' returns nothing if using repeatint events???
        //$get_data = array('redcap_repeat_instance');
        $params = array(
            'return_format'       => 'array',
            'fields'              => array('redcap_repeat_instance','rt_cth_date_angiogram',$instrument."_complete"),
            'records'             => $record
            //'events'              => $this->portalConfig->surveyEventID
        );
        $q = REDCap::getData($params);
        //$results = json_decode($q, true);

        $instances = $q[$record]['repeat_instances'][$event][$instrument];
        //$this->emDebug($params, $results, $q, $instances);


        ///this one is for standard using array
        $max_id = max(array_keys($instances));

        //this one is for longitudinal using json
        //$max_id = max(array_column($results, 'redcap_repeat_instance'));

        return $max_id + 1;
    }


    function saveRepeatingInstance($record_id, $instrument, $event_id= null, $data_array) {

        //check that the $data_array is not blank
        if(!array_filter($data_array)) {
            $this->emDebug("No fields to save in $instrument");
            return;
        }

        $next_instance_id = $this->getNextRepeatingInstanceID($record_id, $instrument, $event_id);
        $this->emDebug($next_instance_id);

        $params = array(
            REDCap::getRecordIdField()                => $record_id,
            //'events'                            => $event_id,
            'redcap_repeat_instrument'                => $instrument,
            "redcap_repeat_instance"                  => $next_instance_id
        );

        $data = array_merge($params, $data_array);

        //$this->emDebug($params, $data);

        $result = REDCap::saveData('json', json_encode(array($data)));
        if ($result['errors']) {
            $this->emError($result['errors'], $params);
            $msg[] = "Error while trying to add angio form.";
            //return false;
        } else {
            $msg[] = "Successfully saved data to $instrument.";
        }

        return $msg;

    }

    function getFields($field_array) {
        $params = array(
            'return_format'    => 'json',
            //'records'          => $record,
            //'events'           => $filter_event,
            'fields'           => $field_array
        );

        $q = REDCap::getData($params);

        //$this->emDebug($filter, $params, isset($dot));
        $records = json_decode($q, true);
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

