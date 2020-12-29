<?php

namespace Stanford\HeartTransplant;
/*** @var \Stanford\HeartTransplant\HeartTransplant $module */


$user = USERID;
$sunet_id = $_SERVER['WEBAUTH_USER'];

$module->emDebug("Starting Heart Transplant : Annual Form Entry by user: " . $user . " / sunet_id:  " . $sunet_id);

$api_url = $module->getUrl("src/UpdatedAnnualFormEntry.php", true, true);
$no_api_url = $module->getUrl("src/UpdatedAnnualFormEntry.php", true, false);
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

    $status = $module->saveAnnualUpdate2($_POST['codedValues'], $_POST['textValues'], $_POST['dateValues'],
                                        $_POST['dateMonthValues'], $_POST['checkedValues'], $_POST['transplant_num'],
    $_POST['annual_year']);

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
                    <option value='no6'>6 months</option>
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

            <div class="form-row">
                <div class="form-group col-md-3"><p>Did dialysis start in past year?</p></div>
                <div class="form-group col-md-1">
                    <label><input name="dialysis" id="dialysis" type="radio" value="1">Yes</label><br>
                    <label><input name="dialysis" id="dialysis" type="radio" value="0">No</label>
                </div>
                <div class="form-group col-md-4">
                    <label>Dialysis Start Date (month/year)</label>
                    <div class='input-group date'>
                        <input name="dialysisstart" type='text' id='dialysisstart' class="form-control"
                               autocomplete="off"/>
                        <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3"><p>ICD?</p></div>
                <div class="form-group col-md-1">
                    <label><input name="icd" id="icd" type="radio" value="1">Yes</label><br>
                    <label><input name="icd" id="icd" type="radio" value="0">No</label>
                </div>
                <div class="form-group col-md-4">
                    <label>ICD implant date</label>
                    <div class='input-group date'>
                        <input name="icddate" type='text' id='icddate' class="form-control" autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3"><p>Permanent Pacemaker past year?</p></div>
                <div class="form-group col-md-1">
                    <label><input name="ppace" id="ppace" type="radio" value="1">Yes</label><br>
                    <label><input name="ppace" id="ppace" type="radio" value="0">No</label>
                </div>
                <div class="form-group col-md-4">
                    <label>Permanent pacemaker date</label>
                    <div class='input-group date'>
                        <input name="ppacedate" type='text' id='ppacedate' class="form-control" autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3"><p>PTLD Diagnosis in past year?</p></div>
                <div class="form-group col-md-1">
                    <label><input name="ptld" id="ptld" type="radio" value="1">Yes</label><br>
                    <label><input name="ptld" id="ptld" type="radio" value="0">No</label>
                </div>
                <div class="form-group col-md-4">
                    <label>PTLD diagnosis date</label>
                    <div class='input-group date'>
                        <input name="ptlddate" type='text' id='ptlddate' class="form-control" autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>
            </div>


            <div class="form-row">
                <div class="form-group col-md-3"><p>Solid organ tumor diagnosis in past year?</p></div>
                <div class="form-group col-md-1">
                    <label><input name="solidtumor" id="solidtumor" type="radio" value="1">Yes</label><br>
                    <label><input name="solidtumor" id="solidtumor" type="radio" value="0">No</label>
                </div>
                <div class="form-group col-md-3">
                    <label>Solid organ tumor date</label>
                    <div class='input-group date'>
                        <input name="solidtumordate" id='solidtumordate' type='text' class="form-control"
                               autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>
                <div class="form-group col-md-1"></div>

                <div class="form-group col-md-3">
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
                <div class="form-group col-md-3"><p>Melanoma diagnosis in past year?</p></div>
                <div class="form-group col-md-1">
                    <label><input name="melanoma" id="melanoma" type="radio" value="1">Yes</label><br>
                    <label></label><input name="melanoma" id="melanoma" type="radio" value="0">No</label>
                </div>
                <div class="form-group col-md-4">
                    <label>Melanoma in past year date</label>
                    <div class='input-group date'>
                        <input name="melanomadate" type='text' id='melanomadate' class="form-control" autocomplete="off"/>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3"><p>Transthoracic Echo?</p></div>
                <div class="form-group col-md-1">
                    <label><input name="tte" id="tte" type="radio" value="1">Yes</label><br>
                    <label><input name="tte" id="tte" type="radio" value="0">No</label>
                </div>
                <div class="form-group col-md-2"><p>TTE type</p></div>
                <div class="form-group col-md-4">
                    <label></label><input name="ttetype" id="ttetype" type="radio" value="1"> Dobutamine stress</label><br>
                    <label></label><input name="ttetype" id="ttetype" type="radio" value="2"> Exercise stress</label><br>
                    <label></label><input name="ttetype" id="ttetype" type="radio" value="3"> Nuclear imaging</label>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Dobutamine Stress Baseline LVEF</label>
                    <div class='input-group date'>
                        <input name="dobutbllvef" type="number" min="1" max="100" id='dobutbllvef' class="form-control" autocomplete="off"/>
                    </div>
                </div>
                <div class="col-md-1"></div>

                <div class="form-group col-md-2"><p>Response to dobutamine stress</p></div>
                <div class="form-group col-md-2">
                    <label><input name="dobutresponse" id="dobutresponse" type="radio" value="1"> No inducible ischemia</label><br>
                    <label><input name="dobutresponse" id="dobutresponse" type="radio" value="2"> Inducible ischemia</label><br>
                    <label><input name="dobutresponse" id="dobutresponse" type="radio" value="3"> Non-diagnostic</label>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Exercises Baseline LVEF</label>
                    <div class='input-group date'>
                        <input name="exerbllvef" type="number" min="1" max="100" id='exerbllvef' class="form-control" autocomplete="off"/>
                    </div>
                </div>
                <div class="col-md-1"></div>

                <div class="form-group col-md-2"><p>Response to exercise stress</p></div>
                <div class="form-group col-md-2">
                    <label><input name="exerresponse" id="exerresponse" type="radio" value="1"> No inducible ischemia</label><br>
                    <label><input name="exerresponse" id="exerresponse" type="radio" value="2"> Inducible ischemia</label><br>
                    <label><input name="exerresponse" id="exerresponse" type="radio" value="3"> Non-diagnostic</label>
                </div>
            </div>

            <div class="form-row"><br>
                <div class="form-group col-md-3">
                    <label>Nuclear baseline LVEF </label>
                    <div class='input-group date'>
                        <input name="nuclearbllvef" type="number" min="1" max="100" id='nuclearbllvef' class="form-control" autocomplete="off"/>
                    </div>
                </div>

                <div class="col-md-1"></div>

                <div class="form-group col-md-2"><p>Response to nuclear</p></div>
                <div class="form-group col-md-3">
                    <label><input name="nuclearresponse" id="nuclearresponse" type="radio" value="1"> No inducible ischemia</label><br>
                    <label><input name="nuclearresponse" id="nuclearresponse" type="radio" value="2"> Inducible ischemia</label><br>
                    <label><input name="nuclearresponse" id="nuclearresponse" type="radio" value="3"> Non-diagnostic</label>
                </div>
            </div>

            <div class="form-row"><br>
                <div class="form-group col-md-3"><p>Coronary Angiogram</p></div>
                <div class="form-group col-md-1">
                    <label><input name="coro_angio" id="coro_angio" type="radio" value="1"> Yes</label><br>
                    <label><input name="coro_angio" id="coro_angio" type="radio" value="0"> No</label>
                </div>
                <div class="form-group col-md-2"><p>Angiogram Results (ISHLT)</p></div>
                <div class="form-group col-md-2">
                    <label><input name="angio_result" id="angio_result" type="radio" value="1"> CAV 0</label><br>
                    <label><input name="angio_result" id="angio_result" type="radio" value="2"> CAV 1</label><br>
                    <label><input name="angio_result" id="angio_result" type="radio" value="3"> CAV 2</label><br>
                    <label><input name="angio_result" id="angio_result" type="radio" value="4"> CAV 4</label>
                </div>
                <div class="form-group col-md-3">
                    <label for="angiogram_mit">Angiogram MIT (mm)</label>
                    <input type="text" class="form-control" id="angiogram_mit" placeholder="">
                </div>
            </div>

            <div class="form-row"><br>
                <div class="form-group col-md-3"><p>PCI performed</p></div>
                <div class="form-group col-md-1">
                    <label><input name="pci" id="pci" type="radio" value="1">Yes</label><br>
                    <label><input name="pci" id="pci" type="radio" value="0">No</label>
                </div>
                <div class="form-group col-md-4">
                    <label>PCI vessel(s)</label>
                    <div class='input-group'>
                        <input name="pci_vessel" type='text' id='pci_vessel' class="form-control" autocomplete="off"/>
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

<script type="text/javascript">

    $(document).ready(function () {

        // turn off cached entries
        $("form :input").attr("autocomplete", "off");

        $('#dialysisstart, #icddate, #ppacedate, #solidtumordate, #melanomadate').datepicker({
            format: "yyyy-mm",
            startView: "months",
            minViewMode: "months"
        });

        $('#fup_date_').datepicker({
            format: 'yyyy-mm-dd',
            orientation: "top"
        });

        $('#ptlddate').datepicker({
            format: 'yyyy-mm-dd'
        });


        //bind change on annual_year update
        //$("input[name='dse_angio']:checked").on('change', function(){
        $('#annual_year').on('change', function(){

            var selection = $(this).val();
            console.log("Selection"+selection);

            if (selection == '') {
                $('#annual_hide').hide();
            } else {
                $('#annual_hide').show();
            }
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
             */

            let annual_year = $("#annual_year").val();

            let codedValues = {
                "dialysis" : $("input[name='dialysis']:checked").val(),
                "icd"      : $("input[name='icd']:checked").val(),
                "ppace"    : $("input[name='ppace']:checked").val(),
                "ptld"     : $("input[name='ptld']:checked").val(),
                "melanoma": $("input[name='melanoma']:checked").val(),
                "solidtumor" : $("input[name='solidtumor']:checked").val(),
                "tte"        : $("input[name='tte']:checked").val(),
                "ttetype"    : $("input[name='ttetype']:checked").val(),
                "dobutresponse"   : $("input[name='dobutresponse']:checked").val(),
                "exerresponse"    : $("input[name='exerresponse']:checked").val(),
                "nuclearresponse" : $("input[name='nuclearresponse']:checked").val(),
                "coro_angio"      : $("input[name='coro_angio']:checked").val(),
                "angio_result"    : $("input[name='angio_result']:checked").val(),
                "pci"             : $("input[name='pci']:checked").val(),
                "coro_angio"      : $("input[name='coro_angio']:checked").val(),
            };

            let dateValues = {
                "fup_date_"            : $("input#fup_date_.form-control").val(),
                "ptlddate"             : $("input#ptlddate.form-control").val()

            };

            let dateMonthValues = {
                "dialysisstart"       : $("input#dialysisstart.form-control").val(),
                "icddate"             : $("input#icddate.form-control").val(),
                "ppacedate"           : $("input#ppacedate.form-control").val(),
                "solidtumordate"       : $("input#solidtumordate.form-control").val(),
                "melanomadate"         : $("input#melanomadate.form-control").val()
            };

            let textValues = {
                "stanford_mrn": $("input#stanford_mrn.form-control").val(),
                "dobutbllvef" : $("input#dobutbllvef.form-control").val(),
                "exerbllvef"    : $("input#exerbllvef.form-control").val(),
                "nuclearbllvef" : $("input#nuclearbllvef.form-control").val(),
                "angiogram_mit" : $("input#angiogram_mit.form-control").val(),
                "pci_vessel"    : $("input#pci_vessel.form-control").val()
            };

            var checkedValues = new Array();
             $('.form-check-input:checked').each(function() {
                 checkedValues.push($(this).val());
                 // checkedValues["'"+$(this).val()+"'"]=1;
             });

             //(checkedValues);


            let formValues = {
                "codedValues"     : codedValues,
                "dateValues"      : dateValues,
                "textValues"      : textValues,
                "dateMonthValues" : dateMonthValues,
                "checkedValues"   : checkedValues,
                "transplant_num"  : $("input#transplant_num.form-control").val(),
                "dot"             : $("input#dot").val(),
                "annual_year"     : $("#annual_year").val(),
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

