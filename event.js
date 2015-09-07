/*jslint browser: true*/
/*global  $, console*/

$(document).ready(function () {
    "use strict";
    $("#trials input[type='checkbox']").change(function () {
        $(this).siblings("div").children().toggle(this.checked);
        $(this).siblings().find("input[type='numeric']").val(0);
        console.log("chekced!");
    });
    
    $("#trials input[type='number']").bind("keyup mouseup", function () {
        var required_num_trials = parseInt(this.value, 10),
            current_num_trials = 0,
            i = 0;
        
        if (isNaN(required_num_trials)) {
            required_num_trials = 0;
        }
        
        current_num_trials = $(this).nextAll("input").size();

        if (required_num_trials < current_num_trials) {
            $(this).nextAll("input").eq(required_num_trials).nextAll().addBack().remove();
        } else {
            for (i = current_num_trials; i < required_num_trials; i += 1) {
                $(this).parent().append(
                    "<input type='text' name='" + this.name + i.toString() + "' />"
                );
            }
        }
    });
    
    $("#trials input[type='checkbox']").change();
});