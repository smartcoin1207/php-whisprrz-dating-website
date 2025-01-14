// This file is built by cobra. --- 20200209

$(document).ready( function() {
    // START setting donation amount
    $("input.btn25").click(function() {
        initAmountSet();
        $("input.btn25").addClass("active_btn");
        $("#amount").val('25');
    });

    $("input.btn50").click(function() {
        initAmountSet();
        $("input.btn50").addClass("active_btn");
        $("#amount").val('50');
    });

    $("input.btn100").click(function() {
        initAmountSet();
        $("input.btn100").addClass("active_btn");
        $("#amount").val('100');
    });

    $("input.btn250").click(function() {
        initAmountSet();
        $("input.btn250").addClass("active_btn");
        $("#amount").val('250');
    });

    $("input.amount").change(function() {
        initAmountButtons();
        var val = $("input.amount").val();
        $("#amount").val(val);
    });

    $("input.btn-mon").click(function() {
        $("input.btn-one").removeClass("active_btn");
        $("input.btn-mon").addClass("active_btn");
        $("#item").val('1');
    });

    $("input.btn-one").click(function() {
        $("input.btn-mon").removeClass("active_btn");
        $("input.btn-one").addClass("active_btn");
        $("#item").val('0');
    });
    // END setting donation amount

    // continue
    $("input.btn-continue").click(function() {
        if ($("#amount").val() == '0' || $("#amount").val() == '') {
            alert('You have to input valid amount of donation!');
        }
        else {
            $("#donationform").submit();
        }
    });
});

function initAmountButtons() {
    var ams = [25, 50, 100, 250];
    for(var i=0;i<4;i++) {
        $("input.btn"+ams[i]).removeClass("active_btn");
    }
}

function initAmountSet() {
    initAmountButtons();
    $("input.amount").val('');
}