<?php

namespace Stanford\HeartTransplant;
/*** @var \Stanford\HeartTransplant\HeartTransplant $module */

$UserObj = $module->getUser();
$user = $user->getUsername(); // USERID;
$sunet_id = $_SERVER['WEBAUTH_USER'];

$module->emDebug("Starting Heart Transplant : Annual Form Entry by user: " . $user . " / sunet_id:  " . $sunet_id);

$api_url = $module->getUrl("src/UpdatedAnnualFormEntry.php", true, true);
$no_api_url = $module->getUrl("src/UpdatedAnnualFormEntry.php", true, false);
//$module->emDebug($api_url, $no_api_url);
$latest_update = "";

if (isset($_POST['search_mrn'])) {
    $status = $module->determineDateOfTransplant($_POST['stanford_mrn'], $_POST['last_name']);
    $latest_update = $status['latest_update'];


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
                'latest_update'        => $status['latest_update'],
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

    $status = $module->saveAnnualUpdateMultiYear($_POST['codedValues'], $_POST['numberValues'], $_POST['textValues'], $_POST['dateValues'],
                                        $_POST['dateMonthValues'], $_POST['checkedValues'], $_POST['transplant_num'], $_POST['annual_year']);

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
    <link href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.0/css/bootstrap-datepicker.css' rel='stylesheet' media='screen'>

    <script
            src="https://code.jquery.com/jquery-3.4.1.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
            crossorigin="anonymous"></script>

    <script type="text/javascript"
            src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>


    <script type='text/javascript' src="<?php echo $module->getUrl('js/annual_form.js', false, true) ?>"></script>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $module->getUrl("css/UpdatedAnnualFormEntry.css", true, true) ?>" />


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
        <br>
        <div class="hidden-till-found" style="display:none">
        <div class="form-row" id="found-row">
            <div class="form-group col-md-4">
                <label for="dot_year">Year of transplant </label>
                <input type="text" class="form-control" id="dot_year" readonly>
            </div>
            <div class="form-group col-md-4">
                <label>Date of followup</label>
                <div class='input-group date' >
                    <input name="fup_date_" id='fup_date_' type='text' class="form-control"
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
                    <option value='mo3'>3 months</option>
                    <option value='mo6'>6 months</option>
                    <option value='yr1'>1 year</option>
                    <option value='yr2'>2 years</option>
                    <option value='yr3'>3 years</option>
                    <option value='yr4'>4 years</option>
                    <option value='yr5'>5 years</option>
                    <option value='yr6'>6 years</option>
                    <option value='yr7'>7 years</option>
                    <option value='yr8'>8 years</option>
                    <option value='yr9'>9 years</option>
                    <option value='yr10'>10 years</option>
                    <option value='yr11'>11 years</option>
                    <option value='yr12'>12 years</option>
                    <option value='yr13'>13 years</option>
                    <option value='yr14'>14 years</option>
                    <option value='yr15'>15 years</option>
                    <option value='yr16'>16 years</option>
                    <option value='yr17'>17 years</option>
                    <option value='yr18'>18 years</option>
                    <option value='yr19'>19 years</option>
                    <option value='yr20'>20 years</option>
                    <option value='yr21'>21 years</option>
                    <option value='yr22'>22 years</option>
                    <option value='yr23'>23 years</option>
                    <option value='yr24'>24 years</option>
                    <option value='yr25'>25 years</option>
                    <option value='yr26'>26 years</option>
                    <option value='yr27'>27 years</option>
                    <option value='yr28'>28 years</option>
                    <option value='yr29'>29 years</option>
                    <option value='yr30'>30 years</option>
                    <option value='yr31'>More than 31 years (overwrites 31+ year visit)</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <input type="hidden" class="form-control" id="transplant_num">
            </div>
        </div>

            <br>
        <hr>

        <div id="annual_hide" style="display:none">
            <div id="update_info">
                Date of last update was <p id="latest_update"></p>.
                Please enter any updates since that date.
            </div>


            <div class="form-row">
                <div class="form-group col-md-3"><p>Did dialysis start?</p></div>
                <div class="form-group col-md-1" id="r_dialysis" >
                    <input name="dialysis" id="dialysis" type="radio" value="1"> Yes</><br>
                    <input name="dialysis" id="dialysis" type="radio" value="0"> No</>
                </div>
                <div class="form-group col-md-4" id ="yes-dialysis" style="display:none">
                    <label>Dialysis Start Date (month/year)</label>
                    <div class='input-group date'>
                        <input name="dialysisstartdate" type='text' id='dialysisstartdate' class="form-control"
                               autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3"><p>ICD?</p></div>
                <div class="form-group col-md-1" id="r_icd">
                    <label><input name="icd" id="icd" type="radio" value="1"> Yes</label><br>
                    <label><input name="icd" id="icd" type="radio" value="0"> No</label>
                </div>
                <div class="form-group col-md-4" id ="yes-icd" style="display:none">
                    <label>ICD implant date</label>
                    <div class='input-group date'>
                        <input name="icddate" type='text' id='icddate' class="form-control" autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3"><p>Permanent Pacemaker?</p></div>
                <div class="form-group col-md-1"  id="r_ppace">
                    <label><input name="ppace" id="ppace" type="radio" value="1"> Yes</label><br>
                    <label><input name="ppace" id="ppace" type="radio" value="0"> No</label>
                </div>
                <div class="form-group col-md-4" id ="yes-ppace" style="display:none">
                    <label>Permanent pacemaker date</label>
                    <div class='input-group date'>
                        <input name="ppacedate" type='text' id='ppacedate' class="form-control" autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3"><p>PTLD Diagnosis?</p></div>
                <div class="form-group col-md-1" id="r_ptld">
                    <label><input name="ptld" id="ptld" type="radio" value="1"> Yes</label><br>
                    <label><input name="ptld" id="ptld" type="radio" value="0"> No</label>
                </div>
                <div class="form-group col-md-4" id ="yes-ptld" style="display:none">
                    <label>PTLD diagnosis date</label>
                    <div class='input-group date'>
                        <input name="ptlddate" type='text' id='ptlddate' class="form-control" autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>
            </div>


            <div class="form-row">
                <div class="form-group col-md-3"><p>Solid organ tumor diagnosis?</p></div>
                <div class="form-group col-md-1" id="r_solidtumor">
                    <label><input name="solidtumor" id="solidtumor" type="radio" value="1">Yes</label><br>
                    <label><input name="solidtumor" id="solidtumor" type="radio" value="0">No</label>
                </div>
                <div class="form-group col-md-3 yes-solidtumor" style="display:none">
                    <label>Solid organ tumor date</label>
                    <div class='input-group date'>
                        <input name="solidtumordate" id='solidtumordate' type='text' class="form-control"
                               autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>
                <div class="form-group col-md-1"></div>

                <div class="form-group col-md-3 yes-solidtumor" style="display:none">
                    <label for="solidtumortype">Solid organ tumor type</label>
                    <div class="form-check">
                        <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                               value="solidtumortype___1">GI/Colon</label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                               value="solidtumortype___2">Breast</label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                               value="solidtumortype___3">Lung</label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                               value="solidtumortype___4">Pancreatic</label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                               value="solidtumortype___5">Prostate</label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                               value="solidtumortype___6">Sarcoma</label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                               value="solidtumortype___7">Renal cell/Genitourinary</label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                               value="solidtumortype___99">Other</label>
                    </div>
                </div>

            </div>


            <div class="form-row">
                <div class="form-group col-md-3"><p>Melanoma diagnosis?</p></div>
                <div class="form-group col-md-1" id="r_melanoma">
                    <label><input name="melanoma" id="melanoma" type="radio" value="1">Yes</label><br>
                    <label></label><input name="melanoma" id="melanoma" type="radio" value="0">No</label>
                </div>
                <div class="form-group col-md-4" id ="yes-melanoma" style="display:none">
                    <label>Melanoma date</label>
                    <div class='input-group date'>
                        <input name="melanomadate" type='text' id='melanomadate' class="form-control" autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3"><p>Was LVEF assessed?</p></div>
                <div class="form-group col-md-1" id="r_lvefassess">
                    <label><input name="lvefassess" id="lvefassess" type="radio" value="1">Yes</label><br>
                    <label><input name="lvefassess" id="lvefassess" type="radio" value="0">No</label>
                </div>
                <div class="form-group col-md-4 yes-lvefassess" id ="yes-lvefassess" style="display:none">
                    <label>LVEF (%)</label>
                    <div class='input-group'>
                        <input name="lvefval" type="number" min="0" max="100" id='lvefval' class="form-control" autocomplete="off"/>
                    </div>
                </div>
            </div>


            <div class="form-row yes-lvefassess" style="display:none">
                <div class="form-group col-md-4"> <label>Which of the following were performed?</label></div>
                <div class="form-group col-md-3">
                    <label class="form-check-label"><input type="checkbox" class="form-check-input" id="diagnostic___1"
                                                           value="diagnostic___1">Transthoracic Echo</label>
                </div>

            </div>
            <div class="form-row yes-lvefassess" style="display:none">
                <div class="form-group col-md-4"></div>
                <div class="form-group col-md-3">
                    <label class="form-check-label"><input type="checkbox" class="form-check-input" id="diagnostic___2"
                                                           value="diagnostic___2">Dobutamine Stress Echo</label>
                </div>

                <div class="form-group col-md-1  show-2" style="display:none"><p>Result: </p></div>
                <div class="form-group col-md-3  show-2" style="display:none">
                    <label><input name="dobutresponse" id="dobutresponse" type="radio" value="1"> No inducible ischemia</label><br>
                    <label><input name="dobutresponse" id="dobutresponse" type="radio" value="2"> Inducible ischemia</label><br>
                    <label><input name="dobutresponse" id="dobutresponse" type="radio" value="3"> Non-diagnostic</label>
                </div>
            </div>
            <div class="form-row yes-lvefassess" style="display:none">
                <div class="form-group col-md-4"></div>
                <div class="form-group col-md-3">
                    <label class="form-check-label"><input type="checkbox" class="form-check-input" id="diagnostic___3"
                                                           value="diagnostic___3">Exercise Stress</label>
                </div>

                <div class="form-group col-md-1 show-3" style="display:none"><p>Result: </p></div>
                <div class="form-group col-md-3 show-3" style="display:none">
                    <label><input name="exerresponse" id="exerresponse" type="radio" value="1"> No inducible ischemia</label><br>
                    <label><input name="exerresponse" id="exerresponse" type="radio" value="2"> Inducible ischemia</label><br>
                    <label><input name="exerresponse" id="exerresponse" type="radio" value="3"> Non-diagnostic</label>
                    </div>
            </div>
            <div class="form-row yes-lvefassess" style="display:none">
                <div class="form-group col-md-4"></div>
                <div class="form-group col-md-3">
                    <label class="form-check-label"><input type="checkbox" class="form-check-input" id="diagnostic___4"
                                                           value="diagnostic___4">Nuclear Stress Test</label>
                </div>

                <div class="form-group col-md-1 show-4" style="display:none"><p>Result: </p></div>
                <div class="form-group col-md-3 show-4" style="display:none">
                    <label><input name="nuclearresponse" id="nuclearresponse" type="radio" value="1"> No inducible ischemia</label><br>
                    <label><input name="nuclearresponse" id="nuclearresponse" type="radio" value="2"> Inducible ischemia</label><br>
                    <label><input name="nuclearresponse" id="nuclearresponse" type="radio" value="3"> Non-diagnostic</label>
                </div>
            </div>



            <div class="form-row"><br>
                <div class="form-group col-md-3"><p>Coronary Angiogram</p></div>
                <div class="form-group col-md-1" id="r_angio">
                    <label><input name="coro_angio" id="coro_angio" type="radio" value="1"> Yes</label><br>
                    <label><input name="coro_angio" id="coro_angio" type="radio" value="0"> No</label>
                </div>
                <div class="form-group col-md-2 yes-angio" style="display:none"><p>Angiogram Results (ISHLT)</p></div>
                <div class="form-group col-md-6 yes-angio" style="display:none" >
                    <label><input name="angio_result" id="angio_result" type="radio" value="1"> CAV 0 - No detectable angiographic lesion</label><br>
                    <label><input name="angio_result" id="angio_result" type="radio" value="2"> CAV 1 - Angiographic left main (LM) <50%, or primary vessel with maximum lesion of <70%, or any branch stenosis <70% (including diffuse narrowing) without allograft dysfunction</label><br>
                    <label><input name="angio_result" id="angio_result" type="radio" value="3"> CAV 2 – Angiographic left main (LM) ≤ 50%; a single primary vessel ≥70%, or isolated branch stenosis ≥70% in branches of 2 systems, without allograft dysfunction</label><br>
                    <label><input name="angio_result" id="angio_result" type="radio" value="4"> CAV 3 – Angiographic LM ≥50%, or two or more primary vessels ≥70% stenosis or isolated branch stenosis ≥70% in all 3 systems; or ISHLT CAV1 or CAV2 with allograft dysfunction (defined as LVEF ≤45% usually in the presence of regional wall motion abnormalities) or evidence of significant restrictive physiology (which is common but not specific)</label>
                </div>

            </div>
            <div class="form-row yes-angio" style="display:none">
                <div class="col-md-4"></div>
                <div class="form-group col-md-3">
                    <label for="angiogram_mit">Angiogram MIT (mm)</label>
                    <input type="text" class="form-control" id="angiogram_mit" placeholder="">
                </div>
            </div>

            <div class="form-row"><br>
                <div class="form-group col-md-3"><p>PCI performed</p></div>
                <div class="form-group col-md-1" id="r_pci">
                    <label><input name="pci" id="pci" type="radio" value="1">Yes</label><br>
                    <label><input name="pci" id="pci" type="radio" value="0">No</label>
                </div>
                <div class="form-group col-md-6" id="yes-pci" style="display:none">
                    <label for="pci_vessel">PCI Vessels</label>
                    <div class="form-check">
                        <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                               value="pci_vessel___1">Left main (LM)</label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                               value="pci_vessel___2">Left anterior descending (LAD)</label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                               value="pci_vessel___3">Ramus intermedius (RI)</label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                               value="pci_vessel___4">Left circumflex or branches (LCx)</label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label"><input type="checkbox" class="form-check-input"
                                                               value="pci_vessel___5">Right coronary artery or branches (RCA)</label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="post_mal_type">Medications</label>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="meds___1">Tacrolimus (IR)</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="meds___2">Tacrolimus (ER)</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="meds___3">Cyclosporine</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="meds___4">Mycophenolate</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="meds___5">Azathioprine</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="meds___6">Prednisone >5 mg/day</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="meds___7">Prednisone 5 mg or less</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="meds___8">Sirolimus</label></div>
                    <div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="meds___9">Everolimus</label></div>
                </div>
            </div>

        <br>

        </div>
        <hr>


        <button type="submit" id="annual_update" class="btn btn-primary" value="true">Submit</button>
        </div>
        <button type="submit" id="find_mrn" class="btn btn-primary" value="true">Find MRN</button>

    </form>

</div>
</body>
</html>


