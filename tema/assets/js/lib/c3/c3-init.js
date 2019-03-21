$(document).ready(function() {

    $(function(){

        var chart = c3.generate({
            bindto: '#c3-chart',
            data: {
              columns: [
                  ['data1', 100, 200, 150, 300, 200],
                  ['data2', 400, 500, 250, 700, 300],
              ],
                colors: {
                    data1: '#eac459',
                    data2: '#62549a'
                    //data3: '#0000ff'
                },
              axes: {
                data2: 'y2' // ADD
              }
            },
            axis: {
              y2: {
                show: true // ADD
              }
            }
        });

    });

    $(function() {
        var chart = c3.generate({
            bindto: '#c3-combination',
            data: {
                columns: [
                    ['data1', 30, 20, 50, 40, 60, 50],
                    ['data2', 200, 130, 90, 240, 130, 220],
                    ['data3', 300, 200, 160, 400, 250, 250]
                ],
                type: 'bar',
                colors: {
                    data1: '#eac459',
                    data2: '#23b9a9',
                    data3: '#4aa9e9'
                },
                types: {
                    data3: 'spline',
                    data4: 'line',
                    data6: 'area'
                },
                groups: [
                    ['data1','data2']
                ]
            }
        });
    });

    $(function() {
        var chart = c3.generate({
            bindto: '#c3-area',
            data: {
                columns: [
                    ['data1', 300, 350, 300, 0, 0, 0],
                    ['data2', 130, 100, 140, 200, 150, 50]
                ],
                colors: {
                    data1: '#62549a',
                    data3: '#ff6c60'
                },
                types: {
                    data1: 'area',
                    data2: 'area-spline'
                }
            }
        });
    });

    $(function() {
        var chart = c3.generate({
            bindto: '#c3-rotated_axis',
            data: {
                columns: [
                    ['data1', 30, 200, 100, 400, 150, 250],
                    ['data2', 50, 20, 10, 40, 15, 25]
                ],
                colors: {
                    data1: '#62549a',
                    data3: '#eac459',
                },
                types: {
                    data1: 'bar'
                }
            },
            axis: {
                rotated: true
            }
        });
    });

});
