(function($) {
    "use strict";


    //No checked stars
    var that = this;
    var toolitup = $("#no-checked-stars").jRate({
        rating: 0,
        strokeColor: '#CCCCCC',
        backgroundColor: '#CCCCCC',
        width: 18,
        height: 18,
        shapeGap: '3px',
        startColor: '#FCC54E',
        endColor: '#E3B146',
        precision: 0.1,
        minSelected: 1,
        onChange: function(rating) {
            console.log("OnChange: Rating: "+rating);
        },
        onSet: function(rating) {
            console.log("OnSet: Rating: "+rating);
        }
    });




    //Half & Full Stars
    var that = this;
    var toolitup = $("#half-full-stars").jRate({
        rating: 2,
        strokeColor: '#CCCCCC',
        backgroundColor: '#CCCCCC',
        width: 18,
        height: 18,
        shapeGap: '3px',
        startColor: '#FCC54E',
        endColor: '#E3B146',
        precision: 0.1,
        minSelected: 1,
        onChange: function(rating) {
            console.log("OnChange: Rating: "+rating);
        },
        onSet: function(rating) {
            console.log("OnSet: Rating: "+rating);
        }
    });



    //Full Stars Only
    var that = this;
    var toolitup = $("#full-stars-only").jRate({
        rating: 5,
        strokeColor: '#CCCCCC',
        backgroundColor: '#CCCCCC',
        width: 18,
        height: 18,
        shapeGap: '3px',
        startColor: '#FCC54E',
        endColor: '#E3B146',
        precision: 0.1,
        minSelected: 1,
        onChange: function(rating) {
            console.log("OnChange: Rating: "+rating);
        },
        onSet: function(rating) {
            console.log("OnSet: Rating: "+rating);
        }
    });


    //blue-stars
    var that = this;
    var toolitup = $("#blue-stars").jRate({
        rating: 3,
        strokeColor: '#CCCCCC',
        backgroundColor: '#CCCCCC',
        width: 18,
        height: 18,
        shapeGap: '3px',
        startColor: '#14B9D5',
        endColor: '#14B9D5',
        precision: 0.1,
        minSelected: 1,
        onChange: function(rating) {
            console.log("OnChange: Rating: "+rating);
        },
        onSet: function(rating) {
            console.log("OnSet: Rating: "+rating);
        }
    });












    //Horizontal stars
    var that = this;
    var toolitup = $("#hrizontal-stars").jRate({
        rating: 3,
        strokeColor: '#CCCCCC',
        backgroundColor: '#CCCCCC',
        width: 18,
        height: 18,
        horizontal: false,
        shapeGap: '3px',
        startColor: '#FCC54E',
        endColor: '#E3B146',
        precision: 0.1,
        minSelected: 1,
        onChange: function(rating) {
            console.log("OnChange: Rating: "+rating);
        },
        onSet: function(rating) {
            console.log("OnSet: Rating: "+rating);
        }
    });


    //Reverse stars
    var that = this;
    var toolitup = $("#reverse-stars").jRate({
        rating: 2,
        strokeColor: '#CCCCCC',
        backgroundColor: '#CCCCCC',
        width: 18,
        height: 18,
        reverse: true,
        shapeGap: '3px',
        startColor: '#FCC54E',
        endColor: '#E3B146',
        precision: 0.1,
        minSelected: 1,
        onChange: function(rating) {
            console.log("OnChange: Rating: "+rating);
        },
        onSet: function(rating) {
            console.log("OnSet: Rating: "+rating);
        }
    });


    //Read Only stars
    var that = this;
    var toolitup = $("#read-only-stars").jRate({
        rating: 3,
        strokeColor: '#CCCCCC',
        backgroundColor: '#CCCCCC',
        width: 18,
        height: 18,
        readOnly: true,
        shapeGap: '3px',
        startColor: '#FCC54E',
        endColor: '#E3B146',
        precision: 0.1,
        minSelected: 1,
        onChange: function(rating) {
            console.log("OnChange: Rating: "+rating);
        },
        onSet: function(rating) {
            console.log("OnSet: Rating: "+rating);
        }
    });


    //Precision stars
    var that = this;
    var toolitup = $("#precision-stars").jRate({
        rating: 3,
        strokeColor: '#CCCCCC',
        backgroundColor: '#CCCCCC',
        width: 18,
        height: 18,
        shapeGap: '3px',
        startColor: '#FCC54E',
        endColor: '#E3B146',
        precision: 0.5,
        minSelected: 1,
        onChange: function(rating) {
            console.log("OnChange: Rating: "+rating);
        },
        onSet: function(rating) {
            console.log("OnSet: Rating: "+rating);
        }
    });



    //Custom Icon-1
    var that = this;
    var toolitup = $("#custom-icon-1").jRate({
        rating: 3,
        strokeColor: '#CCCCCC',
        backgroundColor: '#CCCCCC',
        width: 30,
        height: 30,
        shape: 'FOOD',
        shapeGap: '3px',
        startColor: '#BA4AA0',
        endColor: '#E892B6',
        precision: 0.5,
        minSelected: 1,
        onChange: function(rating) {
            console.log("OnChange: Rating: "+rating);
        },
        onSet: function(rating) {
            console.log("OnSet: Rating: "+rating);
        }
    });

    //Custom Icon-2
    var that = this;
    var toolitup = $("#custom-icon-2").jRate({
        rating: 3,
        strokeColor: '#CCCCCC',
        backgroundColor: '#CCCCCC',
        width: 30,
        height: 30,
        shape: 'TWITTER',
        shapeGap: '3px',
        startColor: '#58E4FF',
        endColor: '#4898F7',
        precision: 0.5,
        minSelected: 1,
        onChange: function(rating) {
            console.log("OnChange: Rating: "+rating);
        },
        onSet: function(rating) {
            console.log("OnSet: Rating: "+rating);
        }
    });

    //Custom Icon-3
    var that = this;
    var toolitup = $("#custom-icon-3").jRate({
        rating: 3,
        strokeColor: '#F96868',
        backgroundColor: '#ffffff',
        width: 30,
        height: 30,
        shape: 'RECTANGLE',
        shapeGap: '3px',
        startColor: '#F96868',
        endColor: '#F96868',
        precision: 0.5,
        minSelected: 1,
        onChange: function(rating) {
            console.log("OnChange: Rating: "+rating);
        },
        onSet: function(rating) {
            console.log("OnSet: Rating: "+rating);
        }
    });




    //Custom Icon-4
    var that = this;
    var toolitup = $("#custom-icon-4").jRate({
        rating: 3,
        strokeColor: '#CCCCCC',
        backgroundColor: '#CCCCCC',
        width: 30,
        height: 30,
        shape: 'BULB',
        shapeGap: '3px',
        startColor: '#B3D678',
        endColor: '#67AE32',
        precision: 0.5,
        minSelected: 1,
        onChange: function(rating) {
            console.log("OnChange: Rating: "+rating);
        },
        onSet: function(rating) {
            console.log("OnSet: Rating: "+rating);
        }
    });

    
    
    /*  order list
    ---------------*/
    
        //Full Stars Only
    var that = this;
    var toolitup = $(".full-stars-only").jRate({
        rating: 5,
        strokeColor: '#CCCCCC',
        backgroundColor: '#CCCCCC',
        width: 18,
        height: 18,
        shapeGap: '3px',
        startColor: '#FCC54E',
        endColor: '#E3B146',
        precision: 0.1,
        minSelected: 1,
        onChange: function(rating) {
            console.log("OnChange: Rating: "+rating);
        },
        onSet: function(rating) {
            console.log("OnSet: Rating: "+rating);
        }
    });
    
    
        //Half & Full Stars
    var that = this;
    var toolitup = $(".half-full-stars").jRate({
        rating: 2,
        strokeColor: '#CCCCCC',
        backgroundColor: '#CCCCCC',
        width: 18,
        height: 18,
        shapeGap: '3px',
        startColor: '#FCC54E',
        endColor: '#E3B146',
        precision: 0.1,
        minSelected: 1,
        onChange: function(rating) {
            console.log("OnChange: Rating: "+rating);
        },
        onSet: function(rating) {
            console.log("OnSet: Rating: "+rating);
        }
    });

    


})(jQuery);