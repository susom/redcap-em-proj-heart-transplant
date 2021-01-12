
var annual_form = annual_form || {};

annual_form.init = function () {
    console.log("Starting the updated annual form");
};


$(document).ready( function() {
    annual_form.init();

    // turn off cached entries
    $("form :input").attr("autocomplete", "off");

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

    //bind change on annual_year update
    $('#annual_year').on('change', function(){
        var selection = $(this).val();
        console.log("Selection"+selection);

        if (selection == '') {
            $('#annual_hide').hide();
        } else {
            $('#annual_hide').show();
        }
    });


    //turn on date picker
    $('#dialysisstartdate, #icddate, #ppacedate, #solidtumordate, #melanomadate').datepicker({
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



    //collect data
    $('#r_dialysis').on('change', function() {

        if ($("input[name='dialysis']:checked").val() == '1') {
            $('#yes-dialysis').show();
        } else {
            $('#yes-dialysis').hide();
        }
    });

    $('#r_icd').on('change', function() {

        if ($("input[name='icd']:checked").val() == '1') {
            $('#yes-icd').show();
        } else {
            $('#yes-icd').hide();
        }
    });

    $('#r_ppace').on('change', function() {

        if ($("input[name='ppace']:checked").val() == '1') {
            $('#yes-ppace').show();
        } else {
            $('#yes-ppace').hide();
        }
    });


    $('#r_ptld').on('change', function() {

        if ($("input[name='ptld']:checked").val() == '1') {
            $('#yes-ptld').show();
        } else {
            $('#yes-ptld').hide();
        }
    });

    $('#r_solidtumor').on('change', function() {

        if ($("input[name='solidtumor']:checked").val() == '1') {
            $('.yes-solidtumor').show();
        } else {
            $('.yes-solidtumor').hide();
        }
    });

    $('#r_melanoma').on('change', function() {

        if ($("input[name='melanoma']:checked").val() == '1') {
            $('#yes-melanoma').show();
        } else {
            $('#yes-melanoma').hide();
        }
    });

    $('#r_tte').on('change', function() {

        if ($("input[name='tte']:checked").val() == '1') {
            $('.yes-tte').show();
        } else {
            $('.yes-tte').hide();
        }
    });

    $('#r_angio').on('change', function() {

        if ($("input[name='coro_angio']:checked").val() == '1') {
            $('.yes-angio').show();
        } else {
            $('.yes-angio').hide();
        }
    });

    $('#r_pci').on('change', function() {
        var yd = $("input[name='pci']:checked").val();

        if ($("input[name='pci']:checked").val() == '1') {
            $('#yes-pci').show();
        } else {
            $('#yes-pci').hide();
        }
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

        //get the radio fields
        codedValues = new Object();
        $("input[type='radio']:checked").each(function () {

            codedValues[this.id] = $(this).val();
          //  codedValues.push({rname : rval});

        })

        //get the mumber fields
        numberValues = new Object();
        $("input[type='number']").each(function () {
            numberValues[this.id] = $(this).val();
        })

        let dateValues = {
            "fup_date_"            : $("input#fup_date_.form-control").val(),
            "ptlddate"             : $("input#ptlddate.form-control").val()

        };

        let dateMonthValues = {
            "dialysisstartdate"   : $("input#dialysisstartdate.form-control").val(),
            "icddate"             : $("input#icddate.form-control").val(),
            "ppacedate"           : $("input#ppacedate.form-control").val(),
            "solidtumordate"       : $("input#solidtumordate.form-control").val(),
            "melanomadate"         : $("input#melanomadate.form-control").val()
        };

        let textValues = {
            "stanford_mrn": $("input#stanford_mrn.form-control").val(),
            "angiogram_mit" : $("input#angiogram_mit.form-control").val(),
            "pci_vessel"    : $("input#pci_vessel.form-control").val()
        };

        var checkedValues = new Array();
        $('.form-check-input:checked').each(function() {
            checkedValues.push($(this).val());
            // checkedValues["'"+$(this).val()+"'"]=1;
        });

        let formValues = {
            "codedValues"     : codedValues,
            "numberValues"    : numberValues,
            "dateValues"      : dateValues,
            "textValues"      : textValues,
            "dateMonthValues" : dateMonthValues,
            "checkedValues"   : checkedValues,
            "transplant_num"  : $("input#transplant_num.form-control").val(),
            "dot"             : $("input#dot").val(),
            "annual_year"     : $("#annual_year").val(),
            "annual_update"   : true
        };

        console.log(formValues);


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