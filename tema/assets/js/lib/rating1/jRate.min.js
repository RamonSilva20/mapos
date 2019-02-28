/*
!The MIT License (MIT)
**/
! function (t) {
    t.fn.jRate = function (o) {
        "use strict";

        function r(t) {
            return "undefined" != typeof t
        }

        function e() {
            return r(M) ? M.rating : void 0
        }

        function n(t) {
            if (!r(t) || t < M.min || t > M.max) throw t + " is not in range(" + min + "," + max + ")";
            M.rating = t, l(t)
        }

        function a(t) {
            var o, r = '<svg width="' + M.width + '" height=' + M.height + ' xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"',
                e = M.horizontal,
                n = _.attr("id"),
                a = '<defs><linearGradient id="' + n + "_grad" + t + '" x1="0%" y1="0%" x2="' + (e ? 100 : 0) + '%" y2="' + (e ? 0 : 100) + '%"><stop offset="0%"  stop-color=' + M.backgroundColor + '/><stop offset="0%" stop-color=' + M.backgroundColor + "/></linearGradient></defs>";
            switch (M.shape) {
            case "STAR":
                o = r + 'viewBox="0 12.705 512 486.59">' + a + '<polygon style="fill: url(#' + n + "_grad" + t + ");stroke:" + M.strokeColor + ";fill-opacity:" + +M.transparency + ';stroke-width:2px;" points="256.814,12.705 317.205,198.566 512.631,198.566 354.529,313.435 414.918,499.295 256.814,384.427 98.713,499.295 159.102,313.435 1,198.566 196.426,198.566 "/></svg>';
                break;
            case "CIRCLE":
                o = r + ">" + a + '<circle  cx="' + M.width / 2 + '" cy="' + M.height / 2 + '" r="' + M.width / 2 + '" fill="url(#' + n + "_grad" + t + ')" style="stroke:' + M.strokeColor + ";fill-opacity:" + +M.transparency + ';stroke-width:2px;"/></svg>';
                break;
            case "RECTANGLE":
                o = r + ">" + a + '<rect width="' + M.width + '" height="' + M.height + '" fill="url(#' + n + "_grad" + t + ')" style="stroke:' + M.strokeColor + ";fill-opacity:" + +M.transparency + ';stroke-width:2px;"/></svg>';
                break;
            case "TRIANGLE":
                o = r + ">" + a + '<polygon points="' + M.width / 2 + ",0 0," + M.height + " " + M.width + "," + M.height + '" fill="url(#' + n + "_grad" + t + ')" style="stroke:' + M.strokeColor + ";fill-opacity:" + +M.transparency + ';stroke-width:2px;"/></svg>';
                break;
            case "RHOMBUS":
                o = r + ">" + a + '<polygon points="' + M.width / 2 + ",0 " + M.width + "," + M.height / 2 + " " + M.width / 2 + "," + M.height + " 0," + M.height / 2 + '" fill="url(#' + n + "_grad" + t + ')"  style="stroke:' + M.strokeColor + ";fill-opacity:" + +M.transparency + ';stroke-width:2px;"/></svg>';
                break;
            case "FOOD":
                o = r + 'viewBox="0 0 50 50">' + a + '<path fill="url(#' + n + "_grad" + t + ')" style="stroke:' + M.strokeColor + ";fill-opacity:" + +M.transparency + ';"d="M45.694,21.194C45.694,9.764,36.43,0.5,25,0.5S4.306,9.764,4.306,21.194c0,8.621,5.272,16.005,12.764,19.115c-1.882,2.244-3.762,4.486-5.645,6.73c-0.429,0.5-0.458,1.602,0.243,2.145c0.7,0.551,1.757,0.252,2.139-0.289c1.878-2.592,3.753-5.189,5.63-7.783c1.774,0.494,3.633,0.777,5.561,0.777c1.85,0,3.64-0.266,5.349-0.723c1.617,2.563,3.238,5.121,4.862,7.684c0.34,0.555,1.365,0.91,2.088,0.414c0.728-0.492,0.759-1.58,0.368-2.096c-1.663-2.252-3.332-4.508-4.995-6.76C40.3,37.354,45.694,29.91,45.694,21.194z M25,37.824c-1.018,0-2.01-0.105-2.977-0.281c1.07-1.48,2.146-2.965,3.215-4.447c0.939,1.48,1.874,2.959,2.81,4.436C27.058,37.717,26.041,37.824,25,37.824z M30.155,37c-1.305-1.764-2.609-3.527-3.91-5.295c0.724-1,1.446-1.998,2.17-3c1.644,0.746,3.646,0,4.827-1.787c1.239-1.872,0.005,0,0.005,0.002c0.01-0.014,5.822-8.824,5.63-8.97c-0.186-0.15-3.804,4.771-6.387,8.081l-0.548-0.43c2.362-3.481,5.941-8.426,5.757-8.575c-0.189-0.146-3.959,4.655-6.652,7.878l-0.545-0.428c2.463-3.398,6.202-8.228,6.014-8.374c-0.188-0.15-4.115,4.528-6.917,7.67l-0.547-0.43c2.575-3.314,6.463-8.02,6.278-8.169c-0.191-0.15-5.808,6.021-7.319,7.651c-1.325,1.424-1.664,3.68-0.562,5.12c-0.703,0.84-1.41,1.678-2.113,2.518c-0.781-1.057-1.563-2.111-2.343-3.17c1.975-1.888,1.984-5.234-0.054-7.626c-2.14-2.565-6.331-5.22-8.51-3.818c-2.093,1.526-1.14,6.396,0.479,9.316c1.498,2.764,4.617,3.965,7.094,2.805c0.778,1.227,1.554,2.455,2.333,3.684c-1.492,1.783-2.984,3.561-4.478,5.342C13.197,34.826,8.38,28.574,8.38,21.191c0-9.183,7.444-16.631,16.632-16.631c9.188,0,16.625,7.447,16.625,16.631C41.63,28.576,36.816,34.828,30.155,37z"/></svg>';
                break;
            case "TWITTER":
                o = r + 'viewBox="0 0 512 512">' + a + '<path fill="url(#' + n + "_grad" + t + ')" style="stroke:' + M.strokeColor + ";fill-opacity:" + +M.transparency + ';stroke-width:0.7px;"d="M512,97.209c-18.838,8.354-39.082,14.001-60.33,16.54c21.687-13,38.343-33.585,46.187-58.115c-20.299,12.039-42.778,20.78-66.705,25.49c-19.16-20.415-46.461-33.17-76.674-33.17c-58.011,0-105.043,47.029-105.043,105.039c0,8.233,0.929,16.25,2.72,23.939c-87.3-4.382-164.701-46.2-216.509-109.753c-9.042,15.514-14.223,33.558-14.223,52.809c0,36.444,18.544,68.596,46.73,87.433c-17.219-0.546-33.416-5.271-47.577-13.139c-0.01,0.438-0.01,0.878-0.01,1.321c0,50.894,36.209,93.348,84.261,103c-8.813,2.399-18.094,3.686-27.674,3.686c-6.769,0-13.349-0.66-19.764-1.887c13.368,41.73,52.16,72.104,98.126,72.949c-35.95,28.175-81.243,44.967-130.458,44.967c-8.479,0-16.84-0.497-25.058-1.471c46.486,29.806,101.701,47.197,161.021,47.197c193.211,0,298.868-160.062,298.868-298.872c0-4.554-0.103-9.084-0.305-13.59C480.11,136.773,497.918,118.273,512,97.209z"/></svg>';
                break;
            case "BULB":
                o = r + 'viewBox="0 0 512 512">' + a + '<path fill="url(#' + n + "_grad" + t + ')" style="stroke:' + M.strokeColor + ";fill-opacity:" + +M.transparency + ';stroke-width:0.7px;"d="M384,192c0,64-64,127-64,192H192c0-63-64-128-64-192c0-70.688,57.313-128,128-128S384,121.313,384,192z M304,448h-96c-8.844,0-16,7.156-16,16s7.156,16,16,16h2.938c6.594,18.625,24.188,32,45.063,32s38.469-13.375,45.063-32H304c8.844,0,16-7.156,16-16S312.844,448,304,448z M304,400h-96c-8.844,0-16,7.156-16,16s7.156,16,16,16h96c8.844,0,16-7.156,16-16S312.844,400,304,400z M81.719,109.875l28.719,16.563c4.438-9.813,9.844-19,16.094-27.656L97.719,82.125L81.719,109.875z M272,33.625V0h-32v33.625C245.344,33.063,250.5,32,256,32S266.656,33.063,272,33.625z M190.438,46.438l-16.563-28.719l-27.75,16l16.656,28.813C171.438,56.281,180.625,50.875,190.438,46.438z M430.281,109.875l-16-27.75l-28.813,16.656c6.25,8.656,11.688,17.844,16.125,27.656L430.281,109.875z M365.844,33.719l-27.688-16l-16.563,28.719c9.781,4.438,19,9.844,27.625,16.063L365.844,33.719z M96,192c0-5.5,1.063-10.656,1.625-16H64v32h33.688C97.063,202.688,96,197.438,96,192z M414.375,176c0.563,5.344,1.625,10.5,1.625,16c0,5.438-1.063,10.688-1.688,16H448v-32H414.375z M388.094,286.719l26.188,15.125l16-27.719l-29.063-16.75C397.188,267.313,392.813,277.063,388.094,286.719z M81.719,274.125l16,27.719l25.969-14.969c-4.688-9.688-9.063-19.5-13.031-29.438L81.719,274.125z"/></svg>';
                break;
            default:
                throw Error("No such shape as " + M.shape)
            }
            return o
        }

        function i() {
            _.css("white-space", "nowrap"), _.css("cursor", "pointer"), _.css("fill", M.shape)
        }

        function c(t, o) {
            t.on("mousemove", u(o)).on("mouseenter", u(o)).on("click", m(o)).on("mouseover", u(o)).on("hover", u(o)).on("mouseleave", d).on("mouseout", d).on("JRate.change", y).on("JRate.set", k), M.touch && t.on("touchstart", w(o)).on("touchmove", w(o)).on("touchend", v(o)).on("tap", v(o))
        }

        function s() {
            for (var t = _.attr("id"), o = 0; o < M.count; o++) z.eq(o).find("#" + t + "_grad" + (o + 1)).find("stop").eq(0).attr({
                offset: "0%"
            }), z.eq(o).find("#" + t + "_grad" + (o + 1)).find("stop").eq(0).attr({
                "stop-color": M.backgroundColor
            }), z.eq(o).find("#" + t + "_grad" + (o + 1)).find("stop").eq(1).attr({
                offset: "0%"
            }), z.eq(o).find("#" + t + "_grad" + (o + 1)).find("stop").eq(1).attr({
                "stop-color": M.backgroundColor
            })
        }

        function l(t) {
            s();
            var o = (M.max - M.min) / M.count;
            t = (t - M.min) / o;
            var e = M.startColor,
                n = _.attr("id");
            if (M.reverse)
                for (var a = 0; t > a; a++) {
                    var i = M.count - 1 - a;
                    if (z.eq(i).find("#" + n + "_grad" + (i + 1)).find("stop").eq(0).attr({
                            offset: "100%"
                        }), z.eq(i).find("#" + n + "_grad" + (i + 1)).find("stop").eq(0).attr({
                            "stop-color": e
                        }), parseInt(t) !== t) {
                        var c = Math.ceil(M.count - t) - 1;
                        z.eq(c).find("#" + n + "_grad" + (c + 1)).find("stop").eq(0).attr({
                            offset: 100 - 10 * t % 10 * 10 + "%"
                        }), z.eq(c).find("#" + n + "_grad" + (c + 1)).find("stop").eq(0).attr({
                            "stop-color": M.backgroundColor
                        }), z.eq(c).find("#" + n + "_grad" + (c + 1)).find("stop").eq(1).attr({
                            offset: 100 - 10 * t % 10 * 10 + "%"
                        }), z.eq(c).find("#" + n + "_grad" + (c + 1)).find("stop").eq(1).attr({
                            "stop-color": e
                        })
                    }
                    r(q) && (e = R(M.count - 1, a))
                } else
                    for (var a = 0; t > a; a++) z.eq(a).find("#" + n + "_grad" + (a + 1)).find("stop").eq(0).attr({
                        offset: "100%"
                    }), z.eq(a).find("#" + n + "_grad" + (a + 1)).find("stop").eq(0).attr({
                        "stop-color": e
                    }), 10 * t % 10 > 0 && (z.eq(Math.ceil(t) - 1).find("#" + n + "_grad" + (a + 1)).find("stop").eq(0).attr({
                        offset: 10 * t % 10 * 10 + "%"
                    }), z.eq(Math.ceil(t) - 1).find("#" + n + "_grad" + (a + 1)).find("stop").eq(0).attr({
                        "stop-color": e
                    })), r(q) && (e = R(M.count, a))
        }

        function f(t) {
            var o, r;
            return o = document.createElement("canvas"), o.height = 1, o.width = 1, r = o.getContext("2d"), r.fillStyle = t, r.fillRect(0, 0, 1, 1), r.getImageData(0, 0, 1, 1).data
        }

        function d() {
            M.readOnly || (l(M.rating), y(null, {
                rating: M.rating
            }))
        }

        function h(t) {
            var o = 1 / M.precision;
            return Math.round(t * o) / o
        }

        function g(t, o, r, e) {
            if (!M.readOnly) {
                var n, a = z.eq(o - 1);
                n = M.horizontal ? (t.pageX - a.offset().left) / a.width() : (t.pageY - a.offset().top) / a.height();
                var i = (M.max - M.min) / M.count;
                n = M.reverse ? n : 1 - n;
                var c = ((M.reverse ? M.max - M.min - o + 1 : o) - n) * i;
                c = M.min + Number(h(c)), c < M.minSelected && (c = M.minSelected), c <= M.max && c >= M.min && (l(c), e && (M.rating = c), a.trigger(r, {
                    rating: c
                }))
            }
        }

        function p(t, o, r, e) {
            if (!M.readOnly) {
                var n = t.originalEvent.changedTouches;
                if (!(n.length > 1)) {
                    var a, i = n[0],
                        c = z.eq(o - 1);
                    a = M.horizontal ? (i.pageX - c.offset().left) / c.width() : (i.pageY - c.offset().top) / c.height();
                    var s = (M.max - M.min) / M.count;
                    a = M.reverse ? a : 1 - a;
                    var f = ((M.reverse ? M.max - M.min - o + 1 : o) - a) * s;
                    f = M.min + Number(h(f)), f < M.minSelected && (f = M.minSelected), f <= M.max && f >= M.min && (l(f), e && (M.rating = f), c.trigger(r, {
                        rating: f
                    }))
                }
            }
        }

        function u(t) {
            return function (o) {
                g(o, t, "JRate.change")
            }
        }

        function m(t) {
            return function (o) {
                g(o, t, "JRate.set", !0)
            }
        }

        function w(t) {
            return function (o) {
                p(o, t, "JRate.touch")
            }
        }

        function v(t) {
            return function (o) {
                p(o, t, "JRate.tap", !0)
            }
        }

        function y(t, o) {
            M.onChange && "function" == typeof M.onChange && M.onChange.apply(this, [o.rating])
        }

        function k(t, o) {
            M.onSet && "function" == typeof M.onSet && M.onSet.apply(this, [o.rating])
        }

        function C() {
            var t, o, r, e;
            for (o = 0; o < M.count; o++) _.append(a(o + 1));
            for (z = _.find("svg"), o = 0; o < M.count; o++) t = z.eq(o), c(t, o + 1), M.horizontal ? t.css("margin-right", M.shapeGap || "0px") : t.css({
                display: "block",
                "margin-bottom": M.shapeGap || "0px"
            }), M.widthGrowth && (r = "scaleX(" + (1 + M.widthGrowth * o) + ")", t.css({
                transform: r,
                "-webkit-transform": r,
                "-moz-transform": r,
                "-ms-transform": r,
                "-o-transform": r
            })), M.heightGrowth && (e = "scaleY(" + (1 + M.heightGrowth * o) + ")", t.css({
                transform: e,
                "-webkit-transform": e,
                "-moz-transform": e,
                "-ms-transform": e,
                "-o-transform": e
            }));
            s(), l(M.rating), z.attr({
                width: M.width,
                height: M.height
            })
        }
        var x, q, z, _ = t(this),
            b = {
                rating: 0,
                shape: "STAR",
                count: 5,
                width: "20",
                height: "20",
                widthGrowth: 0,
                heightGrowth: 0,
                backgroundColor: "white",
                startColor: "yellow",
                endColor: "green",
                strokeColor: "black",
                transparency: 1,
                shapeGap: "0px",
                opacity: 1,
                min: 0,
                max: 5,
                precision: .1,
                minSelected: 0,
                horizontal: !0,
                reverse: !1,
                readOnly: !1,
                touch: !0,
                onChange: null,
                onSet: null
            },
            M = t.extend({}, b, o),
            R = function (t, o) {
                for (var r = [], e = 0; 3 > e; e++) {
                    var n = Math.round((x[e] - q[e]) / t),
                        a = x[e] + n * (o + 1);
                    r[e] = a / 256 ? (x[e] - n * (o + 1)) % 256 : (x[e] + n * (o + 1)) % 256
                }
                return "rgba(" + r[0] + "," + r[1] + "," + r[2] + "," + M.opacity + ")"
            };
        return M.startColor && (x = f(M.startColor)), M.endColor && (q = f(M.endColor)), i(), C(), t.extend({}, this, {
            getRating: e,
            setRating: n,
            setReadOnly: function (t) {
                M.readOnly = t
            },
            isReadOnly: function () {
                return M.readOnly
            }
        })
    }
}(jQuery);