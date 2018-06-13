
$(document).ready(function() {

    // Realtime Rickshaw Chart
    if ($('#rickshaw-realtime').length != 0) {

        (function() {

            var container = '#rickshaw-realtime';

            var seriesData = [
                [],
                [],
                []
            ];
            var random = new Rickshaw.Fixtures.RandomData(50);
            for (var i = 0; i < 50; i++) {
                random.addData(seriesData);
            }
            var graph = new Rickshaw.Graph({
                element: document.querySelector(container),
                height: 338,
                renderer: 'area',
                padding: {
                    top: 0.4
                },
                series: [{
                    data: seriesData[0],
                    color: 'rgba(74, 169, 233, 0.6)', // change contextual color rgba(98, 84, 154, 0.3)
                    name: 'Server 1'
                }, {
                    data: seriesData[1],
                    color: 'rgba(0,0,0,.06)', // change contextual color
                    name: 'Server 2'
                }]
            });

            var y_axis = new Rickshaw.Graph.Axis.Y({
                graph: graph,
                orientation: 'right',
                tickFormat: Rickshaw.Fixtures.Number.formatKMBT,
                element: document.getElementById('rickshaw-realtime_y_axis'),
            });

            var hoverDetail = new Rickshaw.Graph.HoverDetail({
                graph: graph
            });

            // Update the graph with realtime data
            setInterval(function() {
                random.removeData(seriesData);
                random.addData(seriesData);
                graph.update();
            }, 1000);

            d3.selectAll('#rickshaw-realtime_y_axis .tick.major line').attr('x2', '9');
            d3.selectAll('#rickshaw-realtime_y_axis .tick.major text').attr('x', '14');

            $(window).resize(function() {
                graph.configure({
                    width: $(container).width(),
                    height: 338
                });
                graph.render()
            });

            $(container).data('chart', graph);

        })();

    }

    // Stacked bar chart using Rickshaw
    if ($('#rickshaw-stacked-bars').length != 0) {

        (function() {
            var container = '#rickshaw-stacked-bars';

            var seriesData = [
                [],
                []
            ];
            var random = new Rickshaw.Fixtures.RandomData(50);
            for (var i = 0; i < 50; i++) {
                random.addData(seriesData);
            }

            var graph = new Rickshaw.Graph({
                renderer: 'bar',
                element: document.querySelector(container),
                height: 400,
                padding: {
                    top: 0.4
                },
                series: [{
                    data: seriesData[0],
                    color: 'rgba(74, 169, 233, 0.9)', // Change contextual color
                    name: "New users"
                }, {
                    data: seriesData[1],
                    color: 'rgba(0, 0, 0, 0.1)', // Change contextual color
                    name: "Returning users"

                }]

            });

            var hoverDetail = new Rickshaw.Graph.HoverDetail({
                graph: graph,
                formatter: function(series, x, y) {
                    var date = '<span class="date">' + new Date(x * 1000).toUTCString() + '</span>';
                    var swatch = '<span class="detail_swatch" style="background-color: ' + series.color + '"></span>';
                    var content = swatch + series.name + ": " + parseInt(y) + '<br>' + date;
                    return content;
                }
            });

            graph.render();


            $(window).resize(function() {
                graph.configure({
                    width: $(container).width(),
                    height: 400
                });
                graph.render()
            });

            $(container).data('chart', graph);

        })();

    }


});
