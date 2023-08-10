<?php

namespace Stanford\HeartTransplant;
/*** @var \Stanford\HeartTransplant\HeartTransplant $module */


//$user = USERID;
$sunet_id = $_SERVER['WEBAUTH_USER'];

$module->emDebug("Starting Heart Transplant : Annual Form Entry by " .  $sunet_id);

//$api_url = $module->getUrl("src/AnnualFormEntry.php", true, true);
//$no_api_url = $module->getUrl("src/AnnualFormEntry.php", true, false);
//$module->emDebug($api_url, $no_api_url);

if (isset($_POST['search_mrn'])) {
    $status = $module->determineDateOfTransplant($_POST['stanford_mrn'], $_POST['last_name']);


    if ($status['status'] === true) {
        if (empty($status['transplant_num'])) {
            $result = array(
                'result' => 'fail',
                'msg' => 'There was no transplant number found for this MRN. Please notify your admin.'
            );
        }  else if (empty($status['dot_year'])) {
            $result = array(
                'result' => 'fail',
                'msg' => 'There was no date of transplant found for this MRN. Please notify your admin.'
            );
        } else {

            $result = array(
                'result'               => 'success',
                'dot_year'             => $status['dot_year'],
                'transplant_num'       => $status['transplant_num'],
                'r_followup_dialysis'  => $status['r_followup_dialysis'],
                'r_post_dialysis_date' => $status['r_post_dialysis_date'],
                'post_icd'             => $status['post_icd'],
                'post_icd_date'        => $status['post_icd_date'],
                'msg'                  => $status['msg']
            );
        }

    } else {
        //there was an error, just return string in error
        $result = array(
            'result' => 'warn',
            'status' => 'Did not complete',
            'msg' => $status['msg']
        );
    }

    header('Content-Type: application/json');
    print json_encode($result);
    exit();

}


if (isset($_POST['annual_update'])) {
    //$module->emDebug($_REQUEST);

    $status = $module->saveAnnualUpdate($_POST['codedValues'], $_POST['textValues'], $_POST['dateValues'],
                                        $_POST['dateMonthValues'], $_POST['checkedValues'], $_POST['transplant_num']);

    if ($status['status'] === true) {
        $result = array(
            'result' => 'success',
            'msg' => $status['msg']
        );

    } else {
        //there was an error, just return string in error
        $result = array(
            'result' => 'warn',
            'status' => 'Did not complete',
            'msg' => $status['msg']
        );
    }

    header('Content-Type: application/json');
    print json_encode($result);
    exit();

}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Adult Heart Transplant Form</title>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>


    <!-- Favicon -->
    <link rel="icon" type="image/png"
          href="<?php print $module->getUrl("favicon/stanford_favicon.ico", false, true) ?>">


    <link rel="stylesheet" type="text/css"
          href="https://cdn.datatables.net/v/dt/jq-3.3.1/dt-1.10.18/b-1.5.6/b-flash-1.5.6/b-html5-1.5.6/datatables.min.css"/>

    <script type="text/javascript"
            src="https://cdn.datatables.net/v/dt/jq-3.3.1/dt-1.10.18/b-1.5.6/b-flash-1.5.6/b-html5-1.5.6/datatables.min.js"></script>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
            integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
            crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
            integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
            crossorigin="anonymous"></script>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.0/js/bootstrap-datepicker.min.js'></script>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.0/css/bootstrap-datepicker.css'
          rel='stylesheet' media='screen'></link>

    <script
            src="https://code.jquery.com/jquery-3.4.1.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
            crossorigin="anonymous"></script>

    <script type="text/javascript"
            src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $module->getUrl("css/AnnualFormEntry.css") ?>" />


</head>
<body>

<div class="container">
    <h2>Annual Form</h2>

    <form method="POST" id="annual_update_form" action="">
        <hr>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="stanford_mrn">Stanford Medical Record Number </label>
                <input type="text" class="form-control" id="stanford_mrn" placeholder="Do not include hyphens or spaces">
            </div>
            <div class="form-group col-md-6">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" placeholder="Last name">
            </div>

        </div>

        <div class="hidden-till-found" style="display:none">
        <div class="form-row" id="found-row">
            <div class="form-group col-md-4">
                <label for="dot_year">Year of transplant </label>
                <input type="text" class="form-control" id="dot_year" readonly>
            </div>
            <div class="form-group col-md-4">
                <label>Date of followup</label>
                <div class='input-group date' >
                    <input name="last_followup_date" id='last_followup_date' type='text' class="form-control"
                           autocomplete="off"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
            <div class="form-group col-md-4">
                <label for="annual_year">Annual year</label>
                <select id="annual_year" class="form-control">
                    <option></option>
                    <option value='3'>3 months</option>
                    <option value='6'>6 months</option>
                    <option value='12'>1 year</option>
                    <option value='24'>2 years</option>
                    <option value='36'>3 years</option>
                    <option value='48'>4 years</option>
                    <option value='60'>5 years</option>
                    <option value='72'>6 years</option>
                    <option value='84'>7 years</option>
                    <option value='96'>8 years</option>
                    <option value='108'>9 years</option>
                    <option value='120'>10 years</option>
                    <option value='morethan120'>More than 10 years</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <input type="hidden" class="form-control" id="transplant_num">
            </div>
        </div>
        <hr>

        <div class="form-row">
            <div class="form-group col-md-4"><p>Was the patient started on dialysis THIS PAST YEAR?</p>
            </div>
            <div class="form-group col-md-2">
                <label><input name="r_followup_dialysis" id="r_followup_dialysis" type="radio" value="1">
                    Yes</label><br>
                <label><input name="r_followup_dialysis" id="r_followup_dialysis" type="radio" value="0"> No</label>
            </div>
            <div class="form-group col-md-4">
                <label>Date of Dialysis Initiation</label>
                <div class='input-group date'>
                    <input name="r_post_dialysis_date" type='text' id='r_post_dialysis_date' class="form-control"
                           autocomplete="off"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4"><p>Did the patient undergo ICD implantation THIS PAST YEAR?</p>
            </div>
            <div class="form-group col-md-2">
                <label><input name="post_icd" id="post_icd" type="radio" value="1"> Yes</label><br>
                <label><input name="post_icd" id="post_icd" type="radio" value="0"> No</label>
            </div>
            <div class="form-group col-md-4">
                <label>Date of ICD Implantation</label>
                <div class='input-group date'>
                    <input name="post_icd_date" type='text' id='post_icd_date' class="form-control" autocomplete="off"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4"><p>Did the patient undergo PPM implantation THIS PAST YEAR?</p></div>
            <div class="form-group col-md-2">
                <label><input name="post_ppm" id="post_ppm" type="radio" value="1"> Yes</label><br>
                <label><input name="post_ppm" id="post_ppm" type="radio" value="0"> No</label>
            </div>
            <div class="form-group col-md-4">
                <label>Date of PPM Implantation</label>
                <div class='input-group date'>
                    <input name="post_ppm_date" type='text' id='post_ppm_date' class="form-control" autocomplete="off"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4"><p>Has the patient had a PTLD diagnosis THIS PAST YEAR?</p>
            </div>
            <div class="form-group col-md-2">
                <label><input name="mal_ptld_yn" id="mal_ptld_yn" type="radio" value="1"> Yes</label><br>
                <label><input name="mal_ptld_yn" id="mal_ptld_yn" type="radio" value="0"> No</label>
            </div>
            <div class="form-group col-md-4">
                <label>Date of PTLD</label>
                <div class='input-group date'>
                    <input name="mal_date_ptld" type='text' id='mal_date_ptld' class="form-control" autocomplete="off"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4"><p>Has the patient had a solid organ tumor diagnosis THIS PAST YEAR?</p>
            </div>
            <div class="form-group col-md-2">
                <label><input name="sot_type" id="sot_type" type="radio" value="1"> Yes</label><br>
                <label><input name="sot_type" id="sot_type" type="radio" value="0"> No</label>
            </div>
            <div class="form-group col-md-4">
                <label>Date of Malignancy</label>
                <div class='input-group date'>
                    <input name="mal_date_sot" id='mal_date_sot' type='text' class="form-control" autocomplete="off"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
            <div class="form-group col-md-2">
                <label for="post_mal_type">Type of Malignancy</label>
                <div class="form-check">
                    <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                           value="post_mal_type___1">GI/Colon Ca</label>
                </div>
                <div class="form-check">
                    <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                           value="post_mal_type___2">Breast Ca</label>
                </div>
                <div class="form-check">
                    <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                           value="post_mal_type___3">Lung Ca</label>
                </div>
                <div class="form-check">
                    <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                           value="post_mal_type___4">Pancreatic Ca</label>
                </div>
                <div class="form-check">
                    <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                           value="post_mal_type___5">Prostate Ca</label>
                </div>
                <div class="form-check">
                    <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                           value="post_mal_type___6">Sarcoma Ca</label>
                </div>
                <div class="form-check">
                    <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                           value="post_mal_type___7">RCC/GU</label>
                </div>
                <div class="form-check">
                    <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                           value="post_mal_type___99">Other</label>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4"><p>Has the patient had a MELANOMA diagnosis THIS PAST YEAR?</p>
            </div>
            <div class="form-group col-md-2">
                <label><input name="mal_melanoma" id="mal_melanoma" type="radio" value="1"> Yes</label><br>
                <label><input name="mal_melanoma" id="mal_melanoma" type="radio" value="0"> No</label>
            </div>
            <div class="form-group col-md-4">
                <label>Date of Melanoma Diagnosis</label>
                <div class='input-group date'>
                    <input name="mal_mel_date" type='text' id='mal_mel_date' class="form-control"
                           autocomplete="off"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="dse_angio">Will the patient undergo DSE or Angiogram as part of ischemia</label>
                <select id="dse_angio" class="form-control">
                    <option></option>
                    <option value='angio'>Coronary Angiogram</option>
                    <option value='dse'>Dobutamine Stress Echo</option>
                    <option value='none'>No Testing</option>
                </select>
            </div>
        </div>

        <div class="form-row" id="angio" style="display:none">
            <div class="form-group col-md-4">
                <label>Date of Angiogram</label>
                <div class='input-group date'>
                    <input name="rt_cth_date_angiogram" id='rt_cth_date_angiogram' type='text' class="form-control"
                           autocomplete="off"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
            <div class="form-group col-md-4">
                <label for="rt_cth_vessel_angiogram">Any vessel stenosis present</label>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="rt_cth_vessel_angiogram___0">None</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="rt_cth_vessel_angiogram___1">LAD (Ramus)</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="rt_cth_vessel_angiogram___2">LCx (OM)</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="rt_cth_vessel_angiogram___3">RCA</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="rt_cth_vessel_angiogram___99">Other</label></div>
            </div>
            <div class="form-group col-md-4">
                <label for="rt_cth_mit">MIT if known</label>
                <input type="text" class="form-control" id="rt_cth_mit" placeholder="">
            </div>
        </div>
        <div id="dse" style="display:none">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Date of DSE</label>
                    <div class='input-group date'>
                        <input name="rt_ech_date" id='rt_ech_date' type='text' class="form-control"
                               autocomplete="off"/>
                        <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                    </div>
                </div>
                <div class="form-group col-md-2"><p>Type of Echo</p></div>
                <div class="form-group col-md-4">
                    <label><input name="rt_ech_type" id="rt_ech_type" type="radio" value="1"> TTE</label><br>
                    <label><input name="rt_ech_type" id="rt_ech_type" type="radio" value="2"> TEE</label><br>
                    <label><input name="rt_ech_type" id="rt_ech_type" type="radio" value="3"> DSE</label>
                </div>

            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="rt_ech_lvef">LVEF on DSE</label>
                    <input type="text" class="form-control" id="rt_ech_lvef" placeholder="">
                </div>

                <div class="form-group col-md-2"><p>Results of DSE</p></div>
                <div class="form-group col-md-4">
                    <label><input name="rt_ech_dse" id="rt_ech_dse" type="radio" value="0"> Normal - no ischemia</label><br>
                    <label><input name="rt_ech_dse" id="rt_ech_dse" type="radio" value="1"> Abnormal - ischemia present</label>
                    <label><input name="rt_ech_dse" id="rt_ech_dse" type="radio" value="2"> Inconclusive - Target HR not achieved</label>
                </div>

            </div>
        </div>

        <div class="form-row" id="row_3_mo" style="display:none">
            <div class="form-group col-md-6">
                <label for="post_mal_type">What immunosuppressants is patient taking? (3 month update)</label>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_3_mo___1">Tacrolimus (IR)</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_3_mo___2">Envarsus</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_3_mo___3">Astagraf</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_3_mo___4">Cellcept</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_3_mo___5">Myfortic</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_3_mo___6">Pred > 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_3_mo___7">Pred <= 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_3_mo___8">Siro</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_3_mo___9">Evero</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_3_mo___10">AZA</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_3_mo___12">CSA</label></div>
            </div>
        </div>

        <div class="form-row" id="row_6_mo" style="display:none">
            <div class="form-group col-md-6">
                <label for="post_mal_type">What immunosuppressants is patient taking? (6 month update)</label>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_6_mo___1">tacrolimus (IR)</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_6_mo___2">envarsus</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_6_mo___3">astagraf</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_6_mo___4">cellcept</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_6_mo___5">myfortic</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_6_mo___6">Pred > 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_6_mo___7">Pred <= 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_6_mo___8">Siro</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_6_mo___9">Evero</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_6_mo___10">AZA</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_6_mo___12">CSA</label></div>
            </div>
        </div>

        <div class="form-row" id="row_12_mo" style="display:none">
            <div class="form-group col-md-6">
                <label for="post_mal_type">What immunosuppressants is patient taking? (1 year update)</label>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_12_mo___1">tacrolimus (IR)</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_12_mo___2">envarsus</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_12_mo___3">astagraf</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_12_mo___4">cellcept</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_12_mo___5">myfortic</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_12_mo___6">Pred > 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_12_mo___7">Pred <= 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_12_mo___8">Siro</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_12_mo___9">Evero</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_12_mo___10">AZA</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_12_mo___12">CSA</label></div>
            </div>
        </div>

        <div class="form-row" id="row_24_mo" style="display:none">
            <div class="form-group col-md-6">
                <label for="post_mal_type">What immunosuppressants is patient taking? (2 year update)</label>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_24_mo___1">tacrolimus (IR)</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_24_mo___2">envarsus</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_24_mo___3">astagraf</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_24_mo___4">cellcept</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_24_mo___5">myfortic</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_24_mo___6">Pred > 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_24_mo___7">Pred <= 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_24_mo___8">Siro</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_24_mo___9">Evero</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_24_mo___10">AZA</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_24_mo___12">CSA</label></div>
            </div>
        </div>

        <div class="form-row" id="row_36_mo" style="display:none">
            <div class="form-group col-md-6">
                <label for="post_mal_type">What immunosuppressants is patient taking? (3 year update)</label>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_36_mo___1">tacrolimus (IR)</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_36_mo___2">envarsus</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_36_mo___3">astagraf</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_36_mo___4">cellcept</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_36_mo___5">myfortic</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_36_mo___6">Pred > 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_36_mo___7">Pred <= 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_36_mo___8">Siro</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_36_mo___9">Evero</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_36_mo___10">AZA</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_36_mo___12">CSA</label></div>
            </div>
        </div>

        <div class="form-row" id="row_48_mo" style="display:none">
            <div class="form-group col-md-6">
                <label for="post_mal_type">What immunosuppressants is patient taking? (4 year update)</label>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_48_mo___1">tacrolimus (IR)</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_48_mo___2">envarsus</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_48_mo___3">astagraf</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_48_mo___4">cellcept</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_48_mo___5">myfortic</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_48_mo___6">Pred > 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_48_mo___7">Pred <= 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_48_mo___8">Siro</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_48_mo___9">Evero</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_48_mo___10">AZA</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_48_mo___12">CSA</label></div>
            </div>
        </div>

        <div class="form-row" id="row_60_mo" style="display:none">
            <div class="form-group col-md-6">
                <label for="post_mal_type">What immunosuppressants is patient taking? (5 year update)</label>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_60_mo___1">tacrolimus (IR)</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_60_mo___2">envarsus</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_60_mo___3">astagraf</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_60_mo___4">cellcept</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_60_mo___5">myfortic</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_60_mo___6">Pred > 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_60_mo___7">Pred <= 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_60_mo___8">Siro</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_60_mo___9">Evero</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_60_mo___10">AZA</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_60_mo___12">CSA</label></div>
            </div>
        </div>

        <div class="form-row" id="row_72_mo" style="display:none">
            <div class="form-group col-md-6">
                <label for="post_mal_type">What immunosuppressants is patient taking? (6 year update)</label>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_72_mo___1">tacrolimus (IR)</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_72_mo___2">envarsus</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_72_mo___3">astagraf</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_72_mo___4">cellcept</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_72_mo___5">myfortic</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_72_mo___6">Pred > 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_72_mo___7">Pred <= 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_72_mo___8">Siro</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_72_mo___9">Evero</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_72_mo___10">AZA</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_72_mo___12">CSA</label></div>
            </div>
        </div>

        <div class="form-row" id="row_84_mo" style="display:none">
            <div class="form-group col-md-6">
                <label for="post_mal_type">What immunosuppressants is patient taking? (7 year update)</label>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_84_mo___1">tacrolimus (IR)</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_84_mo___2">envarsus</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_84_mo___3">astagraf</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_84_mo___4">cellcept</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_84_mo___5">myfortic</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_84_mo___6">Pred > 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_84_mo___7">Pred <= 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_84_mo___8">Siro</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_84_mo___9">Evero</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_84_mo___10">AZA</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_84_mo___12">CSA</label></div>
            </div>
        </div>

        <div class="form-row" id="row_96_mo" style="display:none">
            <div class="form-group col-md-6">
                <label for="post_mal_type">What immunosuppressants is patient taking? (8 year update)</label>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_96_mo___1">tacrolimus (IR)</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_96_mo___2">envarsus</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_96_mo___3">astagraf</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_96_mo___4">cellcept</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_96_mo___5">myfortic</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_96_mo___6">Pred > 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_96_mo___7">Pred <= 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_96_mo___8">Siro</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_96_mo___9">Evero</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_96_mo___10">AZA</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_96_mo___12">CSA</label></div>
            </div>
        </div>

        <div class="form-row" id="row_108_mo" style="display:none">
            <div class="form-group col-md-6">
                <label for="post_mal_type">What immunosuppressants is patient taking? (9 year update)</label>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_108_mo___1">tacrolimus (IR)</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_108_mo___2">envarsus</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_108_mo___3">astagraf</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_108_mo___4">cellcept</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_108_mo___5">myfortic</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_108_mo___6">Pred > 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_108_mo___7">Pred <= 5 mg/day</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_108_mo___8">Siro</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_108_mo___9">Evero</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_108_mo___10">AZA</label></div>
                <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_108_mo___12">CSA</label></div>
            </div>
        </div>

            <div class="form-row" id="row_120_mo" style="display:none">
                <div class="form-group col-md-6">
                    <label for="post_mal_type">What immunosuppressants is patient taking? (10 year update)</label>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_120_mo___1">tacrolimus (IR)</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_120_mo___2">envarsus</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_120_mo___3">astagraf</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_120_mo___4">cellcept</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_120_mo___5">myfortic</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_120_mo___6">Pred > 5 mg/day</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_120_mo___7">Pred <= 5 mg/day</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_120_mo___8">Siro</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_120_mo___9">Evero</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_120_mo___10">AZA</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_120_mo___12">CSA</label></div>
                </div>
            </div>


            <div class="form-row" id="row_morethan120_mo" style="display:none">
                <div class="form-group col-md-6">
                    <label for="post_mal_type">What immunosuppressants is patient taking? (More than 10 years update)</label>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_morethan120_mo___1">tacrolimus (IR)</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_morethan120_mo___2">envarsus</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_morethan120_mo___3">astagraf</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_morethan120_mo___4">cellcept</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_morethan120_mo___5">myfortic</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_morethan120_mo___6">Pred > 5 mg/day</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_morethan120_mo___7">Pred <= 5 mg/day</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_morethan120_mo___8">Siro</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_morethan120_mo___9">Evero</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_morethan120_mo___10">AZA</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="immuno_morethan120_mo___12">CSA</label></div>
                </div>
            </div>

        <hr>


        <button type="submit" id="annual_update" class="btn btn-primary" value="true">Submit</button>
        </div>
        <button type="submit" id="find_mrn" class="btn btn-primary" value="true">Find MRN</button>

    </form>

</div>
</body>
</html>

<script type="text/javascript">

    $(document).ready(function () {

        // turn off cached entries
        $("form :input").attr("autocomplete", "off");

        $('#last_followup_date').datepicker({
            format: 'yyyy-mm-dd'
        });

        $('#r_post_dialysis_date').datepicker({
            format: "yyyy-mm",
            startView: "months",
            minViewMode: "months"
        });

        $('#post_icd_date').datepicker({
            format: "yyyy-mm",
            startView: "months",
            minViewMode: "months"
        });

        $('#post_ppm_date').datepicker({
            format: "yyyy-mm",
            startView: "months",
            minViewMode: "months"
        });

        $('#mal_date_ptld').datepicker({
            format: 'yyyy-mm-dd'
        });
        $('#mal_date_sot').datepicker({
            format: 'yyyy-mm-dd'
        });
        $('#mal_mel_date').datepicker({
            format: 'yyyy-mm-dd'
        });
        $('#rt_cth_date_angiogram').datepicker({
            format: 'yyyy-mm-dd'
        });

        $('#rt_ech_date').datepicker({
            format: 'yyyy-mm-dd'
        });

        //bind change on dse_angio update
        //$("input[name='dse_angio']:checked").on('change', function(){
        $('#dse_angio').on('change', function(){

            var selection = $(this).val();

            if (selection == 'dse') {
                $('#dse').show();
                $('#angio').hide();
            } else if (selection == 'angio') {
                $('#angio').show();
                $('#dse').hide();
            } else {
                $('#angio').hide();
                $('#dse').hide();
            }
        });

        //bind change on annual update selection
        $('#annual_year').on('change',function(){

            var selection = $(this).val();
            const update_mo = [3,6,12,24,36,48,60,72,84,96,108,120,'morethan120'];

            update_mo.forEach(function (item) {

                selected_block = 'row_'+item+'_mo';
                //console.log(item, selected_block);

                if (item == selection) {
                    //console.log('selected is '+selected_block);
                    $('#'+selected_block).show();
                } else  {
                    //console.log('hidden is '+selected_block);
                    $('#'+selected_block).hide();
                }

            });


            // unset all values
            /**
            var i;
            for (i = 0; i <= 8; i++) {
                selected_block = 'row_'+i+'_mo';

                if (i == selection) {
                    console.log('selected is '+selected_block);
                    $('#'+selected_block).show();
                } else  {
                    console.log('hiddn is '+selected_block);
                    $('#'+selected_block).hide();
                }

            }

             */

            /**
            switch(selection){
                case "3":
                    $("#row_3_mo").show();
                    break;
                case "6":
                    $("#row_6_mo").show();
                    break;
                default:
                    $("#row_3_mo").hide()
            }
             */

        });

        //bind button for MRN lookup

        $('#find_mrn').on('click', function () {
            let formValues = {
                "stanford_mrn": $("input#stanford_mrn.form-control").val(),
                "last_name" : $("input#last_name.form-control").val(),
                "search_mrn"  : true
            };

            $.ajax({
                data: formValues,
                method: "POST"
            })
                .done(function (data) {
                    //console.log("DONE Adding update", data);
                    //console.log("FFUP", parseInt(data.r_followup_dialysis));
                    //console.log("DOT YEAR", data.dot_year);
                    if (data.result === 'success') {
                        $('.hidden-till-found').show();
                        $('#found-row').show();
                        $('#find_mrn').hide();
                        $("input#dot_year").val(data.dot_year);
                        $("input#transplant_num").val(data.transplant_num);

                        $("#r_followup_dialysis").prop("checked", parseInt(data.r_followup_dialysis));
                        $("#post_icd").prop("checked", parseInt(data.post_icd));

                        $("input#r_post_dialysis_date.form-control").val(data.r_post_dialysis_date);
                        $("input#post_icd_date.form-control").val(data.post_icd_date);

                        alert(data.msg);
                    } else {
                        alert(data.msg);
                    }

                })
                .fail(function (data) {
                    //("DATA: ", data);
                    alert("error:", data);
                })
                .always(function () {
                });

            return false;
        });

        //bind button for create new user
        $('#annual_update').on('click', function () {
            console.log("clicked annual_update");

            /**  cannot seem to serialize form
             $.each($('#annual_update').serializeArray(), function(i, field) {
                formValues2[field.name] = field.value;
                console.log(field.name, field.value);
            });
             console.log(formValues2);
             */

            let codedValues = {
                "r_followup_dialysis" : $("input[name='r_followup_dialysis']:checked").val(),
                "post_icd": $("input[name='post_icd']:checked").val(),
                "post_ppm": $("input[name='post_ppm']:checked").val(),
                "mal_ptld_yn": $("input[name='mal_ptld_yn']:checked").val(),
                //"sot_type": $("input[name='sot_type']:checked").val(),  //no such field
                "mal_melanoma": $("input[name='mal_melanoma']:checked").val(),
 //               "dse_angio": $("#dse_angio").val(),
                "rt_ech_type": $("input[name='rt_ech_type']:checked").val(),
                "rt_ech_dse": $("input[name='rt_ech_dse']:checked").val()
            };

            let dateValues = {
                "last_followup_date"   : $("input#last_followup_date.form-control").val(),
                "mal_date_ptld"        : $("input#mal_date_ptld.form-control").val(),
                "mal_date_sot "        : $("input#mal_date_sot.form-control").val(),
                "mal_mel_date"         : $("input#mal_mel_date.form-control").val(),
                "rt_cth_date_angiogram": $("input#rt_cth_date_angiogram.form-control").val(),
                "rt_ech_date"          : $("input#rt_ech_date.form-control").val()

            };

            let dateMonthValues = {
                "r_post_dialysis_date": $("input#r_post_dialysis_date.form-control").val(),
                "post_icd_date"       : $("input#post_icd_date.form-control").val(),
                "post_ppm_date"       : $("input#post_ppm_date.form-control").val()
            };

            let textValues = {
                "stanford_mrn": $("input#stanford_mrn.form-control").val(),
                "rt_cth_mit": $("input#rt_cth_mit.form-control").val(),
                "rt_ech_lvef": $("input#rt_ech_lvef.form-control").val()

            };

            var checkedValues = new Array();
             $('.form-check-input:checked').each(function() {
                 checkedValues.push($(this).val());
                 // checkedValues["'"+$(this).val()+"'"]=1;
             });

             //console.log(checkedValues);


            let formValues = {
                "codedValues"     : codedValues,
                "dateValues"      : dateValues,
                "textValues"      : textValues,
                "dateMonthValues" : dateMonthValues,
                "checkedValues"   : checkedValues,
                "transplant_num"  : $("input#transplant_num.form-control").val(),
                "dot"             : $("input#dot").val(),

                "annual_update"   : true
            };

            $.ajax({ // create an AJAX call...
                data: formValues, // get the form data
                method: "POST"
            })
                .done(function (data) {
                    //console.log("DONE Adding update", data);

                    if (data.result === 'success') {
                        alert(data.msg);
                        location.reload();
                    } else {
                        alert(data.msg);
                    }

                })
                .fail(function (data) {
                    //console.log("DATA: ", data);
                    alert("error:", data);
                })
                .always(function () {
                });

            return false;

        });
    });


</script>

