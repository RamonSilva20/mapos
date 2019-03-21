jQuery(function($) {
    "use strict";
    Holder.run();
    $('.box').asScrollable();
    $('.simple').asScrollable({
        contentSelector: ">",
        containerSelector: ">"
    });
    $('.box').on('asScrollable::scrolltop', function(e, api, direction) {
        console.info('top:' + direction);
    });
    $('.box').on('asScrollable::scrollend', function(e, api, direction) {
        console.info('end:' + direction);
    });
    $('.api-scroll-to').on('click', function() {
        var to = $(this).data('to');
        $('.simple').asScrollable('scrollTo', 'vertical', to);
        $('.simple').asScrollable('scrollTo', 'horizontal', to);
    });
    $('.api-scroll-by').on('click', function() {
        var to = $(this).data('by');
        $('.simple').asScrollable('scrollBy', 'horizontal', to);
        $('.simple').asScrollable('scrollBy', 'vertical', to);
    });
    $('.api-init').on('click', function() {
        $('.simple').asScrollable({
            contentSelector: ">",
            containerSelector: ">"
        });
    });
    $('.api-enable').on('click', function() {
        $('.simple').asScrollable('enable');
    });
    $('.api-disable').on('click', function() {
        $('.simple').asScrollable('disable');
    });
    $('.api-destroy').on('click', function() {
        $('.simple').asScrollable('destroy');
    });
});