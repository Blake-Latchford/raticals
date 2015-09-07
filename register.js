/*jslint browser: true*/
/*global  $, console*/

$(document).ready(function () {
    "use strict";
    $("input[name='height']").keyup(function () {
        var height = parseFloat(this.value, 10);
        if (!isNaN(height)) {
            if (height <= 13) {
                $("input[name='height_class']").val("small");
            } else if (height <= 18) {
                $("input[name='height_class']").val("medium");
            } else {
                $("input[name='height_class']").val("large");
            }
        }
    });
    $("input[name='sex']").change(function () {
        var checked = $("input[name='sex'][value='female']").is(":checked");
        $("#bitch_in_season").toggle(checked);
        $("[for='bitch_in_season']").toggle(checked);
    });
});