<?php
namespace Stanford\HeartTransplant;
/*** @var \Stanford\HeartTransplant\HeartTransplant $module */

require_once APP_PATH_DOCROOT . "ProjectGeneral/header.php";

//if not in record, throw up warning that they should call this from a record
echo "Record ID:". $_POST['id'];

$id = 1;

$module->emDebug("ID IS $id.");

if (empty($id)) {
    ?>
    <div class="jumbotron text-center">
        <h3><span class="glyphicon glyphicon-exclamation-sign"></span> Please open this link from a participant record.</h3>
    </div>
    <?php
    exit();
}

//$id is set so open homepage
?>
<div class="card">
    <div class="card-header">
        <h4><?php echo $module->getModuleName() ." : Record ID ". $id?></h4>
    </div>
    <div class="card-body demog">
            <div class="container-fluid">
                <?php print $module->getDemographicsBlurb($id); ?>
            </div>
    </div>

    <div class="card-body tests">
            <div class="container-fluid">
                <?php print $module->getTestsBlurb($id); ?>
            </div>

</div>

<style>
    .user_title .pid_label { font-size: smaller; vertical-align: text-bottom;}
    .d_label {
        font-weight:bold;
        padding-right: 5px;
    }
    .d.value {}

</style>

<script type="application/javascript">

    $(document).ready( function() {


    }
    </script>