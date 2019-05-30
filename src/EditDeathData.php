<?php

namespace Stanford\HeartTransplant;
/*** @var \Stanford\HeartTransplant\HeartTransplant $module */

$user = USERID;
$sunet_id = $_SERVER['WEBAUTH_USER'];
$module->emDebug("Starting Heart Transplant : Edit Death Data by ". $user . " : " . $sunet_id);

$api_url = $module->getUrl("src/EditDeathData.php", true, true);
$no_api_url = $module->getUrl("src/EditDeathData.php", true, false);
//$module->emDebug($api_url, $no_api_url);

if(isset($_POST['edit_entry'])) {
    //$module->emDebug($_REQUEST);

    $status = $module->editDeathData($_POST['codedValues'],$_POST['textValues'],$_POST['dateValues']);

    if ($status['status'] === true) {
        $result = array(
            'result' => 'success',
            'msg'    => $status['msg']);


    } else {
        //there was an error, just return string in error
        $result = array(
            'result' => 'warn',
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
    <title>New Tx Entry</title>
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


    <!--
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>

    -->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" />


    <!-- Include Bootstrap Datepicker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>











</head>
<body>

<div class="container">
    <h2>New Transplant Entry</h2>

    <form method="POST" id="edit_entry_form" action="">

        <div class="form-row">
            <div class="form-group col-md-12">
          <hr>
          <h3>RECIPIENT DATA</h3>
          <hr>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="mrn">Stanford Medical Record Number </label>
                <input type="text" class="form-control" id="mrn_fix" placeholder="Do not include hyphens or spaces">
            </div>
            <div class="form-group col-md-4">
                <label>Date of Transplant</label>
                <div class='input-group date' id='dot'>
                    <input name="dot" id="dot" type="text" class="form-control" autocomplete="off"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>

        <hr>
        <div class="form-row">
            <div class="form-group col-md-6">
        <label for="out_mode_death">Cause of Death</label>
        <select id="out_mode_death" class="form-control">
            <option></option>
            <option value='1'>Cellular Rejection</option>
<option value='2'>AMR</option>
<option value='3'>PGD</option>
<option value='4'>Infection</option>
<option value='5'>Non- Cardiac/Transplant Related</option>
<option value='6'>CAV</option>
<option value='7'>Unknown sudden cardiac death</option>
<option value='8'>Complications of malignancy</option>
<option value='9'>Surgical Complications</option>
<option value='10'>Pulmonary Disease/Respiratory Failure</option>
<option value='11'>Pulmonary Embolus</option>
<option value='99'>Other</option>
        </select>
             </div>
            <div class="form-group col-md-4">
                <label>Date of Death</label>
                <div class='input-group date'>
                    <input name="dem_date_of_death" type='text'  id='dem_date_of_death' class="form-control" autocomplete="off"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>

        <button type="submit" id="edit_entry" class="btn btn-primary" value="true">Submit</button>

    </form>

</div>
</body>
</html>

<script type = "text/javascript">

    $(document).ready(function(){

        // turn off cached entries
        $("form :input").attr("autocomplete", "off");


        $('#dot').datepicker({
            format: 'yyyy-mm-dd'
        });

        $('#dem_date_of_death').datepicker({
            format: 'yyyy-mm-dd'
        });

        //bind button for death data edit
        $('#edit_entry').on('click', function() {
            console.log("clicked edit_entry");

           /**  cannot seem to serialize form
            $.each($('#new_entry_form').serializeArray(), function(i, field) {
                formValues2[field.name] = field.value;
                console.log(field.name, field.value);
            });
            console.log(formValues2);
            */

           let codedValues = {
               "out_mode_death" : $("#out_mode_death").val()
           };

           let dateValues = {
               "dot" : $("input#dot.form-control").val(),
               "dem_date_of_death" : $("input#dem_date_of_death.form-control").val()
           };

           let textValues = {
               "mrn_fix" : $("input#mrn_fix.form-control").val()
           };

           let formValues = {
               "codedValues" : codedValues,
               "dateValues"  : dateValues,
               "textValues"  : textValues,
               "edit_entry"  : true
            };

            console.log(formValues);

            $.ajax({ // create an AJAX call...
                data: formValues, // get the form data
                method: "POST"
            })
                .done(function (data) {

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

