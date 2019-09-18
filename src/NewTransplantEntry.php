<?php

namespace Stanford\HeartTransplant;
/*** @var \Stanford\HeartTransplant\HeartTransplant $module */


$user = USERID;
$sunet_id = $_SERVER['WEBAUTH_USER'];

$module->emDebug("Starting Heart Transplant : New Transplant Entry by " . $user . " : " . $sunet_id);

$api_url = $module->getUrl("src/NewTransplantEntry.php", true, true);
$no_api_url = $module->getUrl("src/NewTransplantEntry.php", true, false);
$module->emDebug($api_url, $no_api_url);

if(isset($_POST['new_entry'])) {
    //$module->emDebug($_REQUEST);

    $status = $module->saveNewEntry($_POST['codedValues'],$_POST['textValues'],$_POST['dateValues']);

    if ($status['status'] === true) {
        $result = array(
            'result' => 'success',
            'msg'    => $status['msg']
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


//$module->emDebug($header, "HEADER");
//$module->emDebug($candidate, "CANDIDATE"); exit;


//list($header, $candidate) = $module->loadCandidateFile($file);
//$module->emDebug("EXAMPLE ROWS TO LOAD", $header, $candidate[0], $candidate[1]);

//do a getData from the database and get the
//$module->emDebug("EXISTING",  $existing[0], $existing[1]);
//$module->emDebug($existing);

// TRY 1: compare MRN and Names : FAIL
//$matches = $module->compareNamesMRN($candidate, $existing);

// TRY 2: compare UNOS ID, then MRN, then last name : FAIL (some MRNs are different. i.e. Kaiser MRN
//$matches = $module->compareNamesMRN($candidate, $existing);

// TRY 3: search on UNOS ID, string compare last name and present comparison percentage for visual verification
//list($header, $matched, $unmatched_header,  $unmatched) = $module->compareUnosOnly($candidate, $existing);

// TRY 4; search on config selected fields
//$module->emDebug($matches);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Adult Heart Transplant Intake Form</title>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>


    <!-- Favicon -->
    <link rel="icon" type="image/png"
          href="<?php print $module->getUrl("favicon/stanford_favicon.ico", false, true) ?>">


    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jq-3.3.1/dt-1.10.18/b-1.5.6/b-flash-1.5.6/b-html5-1.5.6/datatables.min.css"/>

    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jq-3.3.1/dt-1.10.18/b-1.5.6/b-flash-1.5.6/b-html5-1.5.6/datatables.min.js"></script>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.0/js/bootstrap-datepicker.min.js'></script>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.0/css/bootstrap-datepicker.css' rel='stylesheet' media='screen'></link>

    <script
            src="https://code.jquery.com/jquery-3.4.1.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
            crossorigin="anonymous"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>









</head>
<body>

<div class="container">
    <h2>Adult Heart Transplant Intake Form</h2>

    <form method="POST" id="new_entry_form" action="">

        <div class="form-row">
            <div class="form-group col-md-12">
          <hr>
          <h3>RECIPIENT DATA</h3>
          <hr>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="stanford_mrn">Stanford Medical Record Number </label>
                <input type="text" class="form-control" id="stanford_mrn" placeholder="Do not include hyphens or spaces">
            </div>
            <div class="form-group col-md-4">
                <label>Date of Transplant</label>
                <div class='input-group date' id='dot'>
                    <input name="dot" id='dot' type='text' class="form-control" autocomplete="off"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <hr>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" placeholder="First name">
            </div>
            <div class="form-group col-md-6">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" placeholder="Last name">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="inputState">State</label>
                <select id="address_state" class="form-control">
                    <option selected></option>
                    <option value="CA">California</option>
                    <option value="AL">Alabama</option>
                    <option value="AK">Alaska</option>
                    <option value="AZ">Arizona</option>
                    <option value="AR">Arkansas</option>
                    <option value="CO">Colorado</option>
                    <option value="CT">Connecticut</option>
                    <option value="DE">Delaware</option>
                    <option value="DC">District Of Columbia</option>
                    <option value="FL">Florida</option>
                    <option value="GA">Georgia</option>
                    <option value="HI">Hawaii</option>
                    <option value="ID">Idaho</option>
                    <option value="IL">Illinois</option>
                    <option value="IN">Indiana</option>
                    <option value="IA">Iowa</option>
                    <option value="KS">Kansas</option>
                    <option value="KY">Kentucky</option>
                    <option value="LA">Louisiana</option>
                    <option value="ME">Maine</option>
                    <option value="MD">Maryland</option>
                    <option value="MA">Massachusetts</option>
                    <option value="MI">Michigan</option>
                    <option value="MN">Minnesota</option>
                    <option value="MS">Mississippi</option>
                    <option value="MO">Missouri</option>
                    <option value="MT">Montana</option>
                    <option value="NE">Nebraska</option>
                    <option value="NV">Nevada</option>
                    <option value="NH">New Hampshire</option>
                    <option value="NJ">New Jersey</option>
                    <option value="NM">New Mexico</option>
                    <option value="NY">New York</option>
                    <option value="NC">North Carolina</option>
                    <option value="ND">North Dakota</option>
                    <option value="OH">Ohio</option>
                    <option value="OK">Oklahoma</option>
                    <option value="OR">Oregon</option>
                    <option value="PA">Pennsylvania</option>
                    <option value="RI">Rhode Island</option>
                    <option value="SC">South Carolina</option>
                    <option value="SD">South Dakota</option>
                    <option value="TN">Tennessee</option>
                    <option value="TX">Texas</option>
                    <option value="UT">Utah</option>
                    <option value="VT">Vermont</option>
                    <option value="VA">Virginia</option>
                    <option value="WA">Washington</option>
                    <option value="WV">West Virginia</option>
                    <option value="WI">Wisconsin</option>
                    <option value="WY">Wyoming</option>
                </select>
            </div>
            <div class="form-group col-md-2">
                <label for="inputZip">Zip</label>
                <input type="text" class="form-control" id="address_zip">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="sex_r"> Gender of Recipient</label>
            </div>
            <div class="form-group col-md-3">
                <label><input name="sex_r" id="sex_r" type="radio" value="0"> Female</label><br>
                <label><input name="sex_r" id="sex_r" type="radio" value="1"> Male</label>
            </div>
            <div class="form-group col-md-4">
            <label>Date of Birth</label>
                <div class='input-group date'>
                    <input name="dob" type='text'  id='dob' class="form-control" autocomplete="off"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <hr>
        <div class="form-row">
            <div class="form-group col-md-4"><p>Did the patient receive a kidney transplant?</p>
            </div>
            <div class="form-group col-md-2">
                <label><input name="dem_kidney_tx" id="dem_kidney_tx" type="radio" value="1"> Yes</label><br>
                <label><input name="dem_kidney_tx" id="dem_kidney_tx" type="radio" value="0"> No</label>
            </div>
            <div class="form-group col-md-4">
                <label>Date of Kidney Transplant</label>
                <div class='input-group date'>
                    <input name="dem_kidney_tx_date" type='text'  id='dem_kidney_tx_date' class="form-control" autocomplete="off"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                Did the patient receive a liver transplant?
            </div>
            <div class="form-group col-md-2">
                <label><input name="dem_liver_tx" id="dem_liver_tx" type="radio" value="1"> Yes</label><br>
                <label><input name="dem_liver_tx" id="dem_liver_tx" type="radio" value="0"> No</label>
            </div>
            <div class="form-group col-md-4">
                <label>Date of Liver Transplant</label>
                <div class='input-group date'>
                    <input name="dem_liver_tx_date" type='text'  id='dem_liver_tx_date' class="form-control" autocomplete="off"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>

        <hr>
        <div class="form-group">
          <hr>
      <h3>DONOR DATA</h3>
          <hr>
      </div>
        <div class="form-row">
    <div class="form-group col-md-6">
      <label for="unos_id">UNOS ID</label>
        <input type="text" class="form-control" id="unos_id" placeholder="For example: ABCD123">
    </div>
    <div class="form-group col-md-6">
        <label for="match_id">MATCH ID</label>
        <input type="text" class="form-control" id="match_id" placeholder="For example: 1234567">
    </div>
    </div>
        <div class="form-row">
        <div class="form-group col-md-6">
            <label for="age_d">Donor Age</label>
            <input type="text" class="form-control" id="age_d">
        </div>
        </div>
        <div class="form-row">
        <div class="form-group col-md-3">
            <label for="sex_d"> Gender of Donor</label>
        </div>
        <div class="form-group col-md-3">
            <label><input name="sex_d" id="sex_d" type="radio" value="0"> Female</label><br>
            <label><input name="sex_d" id="sex_d" type="radio" value="1"> Male</label>
        </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="donor_high_risk">Meets CDC Guidelines for High Risk</label>
                <select id="donor_high_risk" class="form-control">
                    <option></option>
                    <option value='1'>Yes</option>
                    <option value='0'>No</option>
                    <option value='98'>Unknown</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="dnr_cause_death">Cause of Death</label>
                <select id="dnr_cause_death" class="form-control">
                    <option></option>
                    <option value='1'>MVA</option>
                    <option value='2'>GSW</option>
                    <option value='3'>Child Abuse (shaking)</option>
                    <option value='4'>Drowning</option>
                    <option value='5'>Blunt Head Trauma (Other)</option>
                    <option value='6'>Intracranial Hemmorhage</option>
                    <option value='8'>Infection</option>
                    <option value='10'>Ischemia (other, like seizure)</option>
                    <option value='98'>Unknown</option>
                    <option value='99'>Other (describe below)</option>
                </select>
            </div>

        </div>
        <div class="form-row">
            <div class="form-group col-md-6"><p>Was donor supported by transmedics organ care system (OCS)?</p>
            </div>
            <div class="form-group col-md-2">
                <label><input name="dnr_ocs_yn" id="dnr_ocs_yn" type="radio" value="1"> Yes</label><br>
                <label><input name="dnr_ocs_yn" id="dnr_ocs_yn" type="radio" value="0"> No</label>
            </div>
        </div>

        <button type="submit" id="new_entry" class="btn btn-primary" value="true">Submit</button>

    </form>

</div>
</body>
</html>

<script type = "text/javascript">

    $(document).ready(function(){

        // turn off cached entries
        $("form :input").attr("autocomplete", "off");

        $('#dob').datepicker({
            format: 'yyyy-mm-dd'
        });
        $('#dot').datepicker({
            format: 'yyyy-mm-dd'
        });
        $('#dem_kidney_tx_date').datepicker({
            format: 'yyyy-mm-dd'
        });
        $('#dem_liver_tx_date').datepicker({
            format: 'yyyy-mm-dd'
        });


        //bind button for create new user
        $('#new_entry').on('click', function() {
            console.log("clicked create_new_user");

           /**  cannot seem to serialize form
            $.each($('#new_entry_form').serializeArray(), function(i, field) {
                formValues2[field.name] = field.value;
                console.log(field.name, field.value);
            });
            console.log(formValues2);
            */

           let codedValues = {
               "sex_r" :  $("input[name='sex_r']:checked").val(),
               "sex_d" :  $("input[name='sex_d']:checked").val(),
               "dem_referral_center" :  $("input[name='dem_referral_center']:checked").val(),
               "dem_kidney_tx"   :  $("input[name='dem_kidney_tx']:checked").val(),
               "dem_liver_tx"   :  $("input[name='dem_liver_tx']:checked").val(),
               "donor_high_risk" : $("#donor_high_risk").val(),
               "dnr_cause_death"     : $("#dnr_cause_death").val(),
               "dnr_ocs_yn"   :  $("input[name='dnr_ocs_yn']:checked").val()
           };

           let dateValues = {
               "dot" : $("input#dot.form-control").val(),
               "dob" : $("input#dob.form-control").val(),
               "dem_kidney_tx_date" : $("input#dem_kidney_tx_date.form-control").val(),
               "dem_liver_tx_date" : $("input#dem_liver_tx_date.form-control").val()
           };

           let textValues = {
               "stanford_mrn" : $("input#stanford_mrn.form-control").val(),
               "first_name" : $("input#first_name.form-control").val(),
               "last_name" : $("input#last_name.form-control").val(),
               "address_state" : $("#address_state").val(),
               "address_zip" : $("input#address_zip.form-control").val(),
               "unos_id" : $("input#unos_id.form-control").val(),
               "match_id" : $("input#match_id.form-control").val(),
               "age_d" : $("input#age_d.form-control").val()
           };

           let formValues = {
               "codedValues" : codedValues,
               "dateValues"  : dateValues,
               "textValues"  : textValues,
                "new_entry" : true
            };

            $.ajax({ // create an AJAX call...
                data: formValues, // get the form data
                method: "POST"
            })
                .done(function (data) {
                    console.log("DONE CREATE_NEW_USER", data);

                    if (data.result === 'success') {
                        alert(data.msg);
                        location.reload();
                    } else {
                        alert(data.msg);
                    }

                })
                .fail(function (data) {
                    console.log("DATA: ", data);
                    alert("error:", data);
                })
                .always(function () {
                });

            return false;

        });
    });


</script>

