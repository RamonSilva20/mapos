(function ($) {
    "use strict";


    /* 
    ---------------
    LobiPanel Function
    ---------------*/
        $(function () {
            $('.lobipanel-basic').lobiPanel({
                sortable: true,
                reload: {
                    icon: 'ti-loop'
                },
                editTitle: {
                    icon: 'ti-pencil-alt',
                    icon2: 'ti-save'
                },
                unpin: {
                    icon: 'ti-pin-alt'
                },
                minimize: {
                    icon: 'ti-angle-up',
                    icon2: 'ti-angle-down'
                },
                close: {
                    icon: 'ti-close'
                },
                expand: {
                    icon: 'ti-fullscreen',
                    icon2: 'fa fa-compress'
                }
            });
        });





})(jQuery);