! function(t) {
    "use strict"

    function e() {
        t(".slimscroll-menu").slimscroll({
            height: "auto",
            position: "right",
            size: "5px",
            color: "#9ea5ab",
            wheelStep: 5
        })
    }

    function n() {
        t(".slimscroll").slimscroll({
            height: "auto",
            position: "right",
            size: "5px",
            color: "#9ea5ab"
        })
    }

    function a() {
        t("#side-menu").metisMenu()
    }

    function i() {
        t(".button-menu-mobile").on("click", function(e) {
            e.preventDefault(), t("body").toggleClass("enlarged")
        })
    }

    function s() {
        t(window).width() < 1025 ? t("body").addClass("enlarged") : t("body").removeClass("enlarged")
    }

    function o() {
        t("#sidebar-menu a").each(function() {
            this.href == window.location.href && (t(this).addClass("active"), t(this).parent().addClass("active"), t(this).parent().parent().addClass("in"), t(this).parent().parent().prev().addClass("active"), t(this).parent().parent().parent().addClass("active"), t(this).parent().parent().parent().parent().addClass("in"), t(this).parent().parent().parent().parent().parent().addClass("active"))
        })
    }

    function r() {
        e(), n(), a(), i(), s(), o()
    }
    r()
}(jQuery)