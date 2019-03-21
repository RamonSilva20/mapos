$(function () {
    "use strict";
//    Start without params
    $("#range_01").ionRangeSlider();


//Set min value, max value and start point
    $("#range_02").ionRangeSlider({
        min: 100,
        max: 1000,
        from: 550
    });



//Set type to double and specify range, also showing grid and adding prefix "$"

    $("#range_03").ionRangeSlider({
        type: "double",
        grid: true,
        min: 0,
        max: 1000,
        from: 200,
        to: 800,
        prefix: "$"
    });

//Set up range with negative values
    $("#range_04").ionRangeSlider({
        type: "double",
        grid: true,
        min: -1000,
        max: 1000,
        from: -500,
        to: 500
    });


//Using step 250
    $("#range_05").ionRangeSlider({
        type: "double",
        grid: true,
        min: -1000,
        max: 1000,
        from: -500,
        to: 500,
        step: 250
    });

//Set up range with fractional values, using fractional step
    $("#range_06").ionRangeSlider({
        type: "double",
        grid: true,
        min: -12.8,
        max: 12.8,
        from: -3.2,
        to: 3.2,
        step: 0.1
    });


//Set up you own numbers
    $("#range_07").ionRangeSlider({
        type: "double",
        grid: true,
        from: 1,
        to: 5,
        values: [0, 10, 100, 1000, 10000, 100000, 1000000]
    });


// Using any strings as your values
    $("#range_08").ionRangeSlider({
        grid: true,
        from: 5,
        values: [
            "zero", "one",
            "two", "three",
            "four", "five",
            "six", "seven",
            "eight", "nine",
            "ten"
        ]
    });



//One more example with strings
    $("#range_09").ionRangeSlider({
        grid: true,
        from: 3,
        values: [
            "January", "February", "March",
            "April", "May", "June",
            "July", "August", "September",
            "October", "November", "December"
        ]
    });


//No prettify. Big numbers are ugly and unreadable
    $("#range_10").ionRangeSlider({
        grid: true,
        min: 1000,
        max: 1000000,
        from: 100000,
        step: 1000,
        prettify_enabled: false
    });


//Prettify enabled. Much better!
    $("#range_11").ionRangeSlider({
        grid: true,
        min: 1000,
        max: 1000000,
        from: 200000,
        step: 1000,
        prettify_enabled: true
    });



//Don't like space as separator? Use anything you like!
    $("#range_12").ionRangeSlider({
        grid: true,
        min: 1000,
        max: 1000000,
        from: 300000,
        step: 1000,
        prettify_enabled: true,
        prettify_separator: "."
    });


//You don't like default prettify function? Use your own!
    $("#range_13").ionRangeSlider({
        grid: true,
        min: 1000,
        max: 1000000,
        from: 400000,
        step: 1000,
        prettify_enabled: true,
        prettify: function (num) {
            return (Math.random() * num).toFixed(0);
        }
    });



//Using prefixes
    $("#range_14").ionRangeSlider({
        type: "double",
        grid: true,
        min: 0,
        max: 10000,
        from: 1000,
        step: 9000,
        prefix: "$"
    });




//Using postfixes
    $("#range_15").ionRangeSlider({
        type: "single",
        grid: true,
        min: -90,
        max: 90,
        from: 0,
        postfix: "°"
    });



//What to show that max number is not the biggest one?
    $("#range_16").ionRangeSlider({
        grid: true,
        min: 18,
        max: 70,
        from: 30,
        prefix: "Age ",
        max_postfix: "+"
    });


//Taking care about how from and to values connect? Use decorate_both option:
    $("#range_17").ionRangeSlider({
        type: "double",
        min: 100,
        max: 200,
        from: 145,
        to: 155,
        prefix: "Weight: ",
        postfix: " million pounds",
        decorate_both: true
    });



//Remove double decoration
    $("#range_18").ionRangeSlider({
        type: "double",
        min: 100,
        max: 200,
        from: 145,
        to: 155,
        prefix: "Weight: ",
        postfix: " million pounds",
        decorate_both: false
    });



//Use your own separator symbol with values_separator option. Like →

    $("#range_19").ionRangeSlider({
        type: "double",
        min: 100,
        max: 200,
        from: 148,
        to: 152,
        prefix: "Weight: ",
        postfix: " million pounds",
        values_separator: " → "
    });


//Or " to ":
    $("#range_20").ionRangeSlider({
        type: "double",
        min: 100,
        max: 200,
        from: 148,
        to: 152,
        prefix: "Range: ",
        postfix: " light years",
        decorate_both: false,
        values_separator: " to "
    });



//You can disable all the sliders visual details, if you wish. Like this:

    $("#range_21").ionRangeSlider({
        type: "double",
        min: 1000,
        max: 2000,
        from: 1200,
        to: 1800,
        hide_min_max: true,
        hide_from_to: true,
        grid: false
    });



//Or hide any part you wish
    $("#range_22").ionRangeSlider({
        type: "double",
        min: 1000,
        max: 2000,
        from: 1200,
        to: 1800,
        hide_min_max: true,
        hide_from_to: true,
        grid: true
    });



//And some more

    $("#range_23").ionRangeSlider({
        type: "double",
        min: 1000,
        max: 2000,
        from: 1200,
        to: 1800,
        hide_min_max: false,
        hide_from_to: true,
        grid: false
    });


//And some more
    $("#range_24").ionRangeSlider({
        type: "double",
        min: 1000,
        max: 2000,
        from: 1200,
        to: 1800,
        hide_min_max: true,
        hide_from_to: false,
        grid: false
    });




    $("#range_25").ionRangeSlider({
        type: "double",
        min: 1000000,
        max: 2000000,
        grid: true
    });



    $("#range_26").ionRangeSlider({
        type: "double",
        min: 1000000,
        max: 2000000,
        grid: true,
        force_edges: true
    });





    $("#range_27").ionRangeSlider({
        type: "double",
        min: 0,
        max: 10000,
        grid: true
    });





    $("#range_28").ionRangeSlider({
        type: "double",
        min: 0,
        max: 10000,
        grid: true,
        grid_num: 10
    });



    $("#range_29").ionRangeSlider({
        type: "double",
        min: 0,
        max: 10000,
        step: 500,
        grid: true,
        grid_snap: true
    });



    $("#range_30").ionRangeSlider({
        type: "single",
        min: 0,
        max: 10,
        step: 2.34,
        grid: true,
        grid_snap: true
    });



    $("#range_31").ionRangeSlider({
        type: "double",
        min: 0,
        max: 100,
        from: 30,
        to: 70,
        from_fixed: true
    });



    $("#range_32").ionRangeSlider({
        type: "double",
        min: 0,
        max: 100,
        from: 30,
        to: 70,
        from_fixed: true,
        to_fixed: true
    });




    $("#range_33").ionRangeSlider({
        min: 0,
        max: 100,
        from: 30,
        from_min: 10,
        from_max: 50
    });




    $("#range_34").ionRangeSlider({
        min: 0,
        max: 100,
        from: 30,
        from_min: 10,
        from_max: 50,
        from_shadow: true
    });




    $("#range_35").ionRangeSlider({
        type: "double",
        min: 0,
        max: 100,
        from: 20,
        from_min: 10,
        from_max: 30,
        from_shadow: true,
        to: 80,
        to_min: 70,
        to_max: 90,
        to_shadow: true,
        grid: true,
        grid_num: 10
    });




    $("#range_36").ionRangeSlider({
        min: 0,
        max: 100,
        from: 30,
        disable: true
    });




    $("#range_37").ionRangeSlider({
        type: "double",
        min: 0,
        max: 100,
        from: 30,
        to: 70,
        keyboard: true
    });



    $("#range_38").ionRangeSlider({
        type: "double",
        min: 0,
        max: 100,
        from: 30,
        to: 70,
        keyboard: true,
        keyboard_step: 20
    });




    $("#range_39").ionRangeSlider({
        min: +moment().subtract(1, "years").format("X"),
        max: +moment().format("X"),
        from: +moment().subtract(6, "months").format("X"),
        prettify: function (num) {
            return moment(num, "X").format("LL");
        }
    });



    $("#range_40").ionRangeSlider({
        min: +moment().subtract(12, "hours").format("X"),
        max: +moment().format("X"),
        from: +moment().subtract(6, "hours").format("X"),
        prettify: function (num) {
            return moment(num, "X").format("MMM Do, hh:mm A");
        }
    });



    $("#range_41").ionRangeSlider({
        min: +moment().subtract(12, "hours").format("X"),
        max: +moment().format("X"),
        from: +moment().subtract(6, "hours").format("X"),
        grid: true,
        force_edges: true,
        prettify: function (num) {
            var m = moment(num, "X").locale("ru");
            return m.format("Do MMMM, HH:mm");
        }
    });




    $("#range_42").ionRangeSlider({
        min: +moment().subtract(12, "hours").format("X"),
        max: +moment().format("X"),
        from: +moment().subtract(6, "hours").format("X"),
        grid: true,
        force_edges: true,
        prettify: function (num) {
            var m = moment(num, "X").locale("ja");
            return m.format("Do MMMM, LT");
        }
    });




    $("#range_119").ionRangeSlider({
        type: "double",
        min: 0,
        max: 100,
        from: 38,
        to: 58,
        min_interval: 20
    });




    $("#range_120").ionRangeSlider({
        type: "double",
        min: 0,
        max: 100,
        from: 41,
        to: 91,
        max_interval: 50
    });



    $("#range_121").ionRangeSlider({
        type: "double",
        min: 0,
        max: 100,
        from: 30,
        to: 70,
        drag_interval: true
    });







});