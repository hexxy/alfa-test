$(document).ready(function () {
    // При загрузке страницы выводится график доходности первого в таблице ПАММ счета
    drawChart();

    // При клику по чекбоксу графики доходности перерисовываются
    $(".graph").click(function () {
        drawChart();

    });
});

var chart;
var chartData = [];

// Получение статистики по выбранным памм счетам с сервера
function getChartData() {

    var data = [];
    var pamm_ids = [];
    $(".graph:checked").each(function (index) {
        pamm_ids.push($(this).attr("id"));
    });

    $.ajax({
        url: "/get-chart-data",
        data: {"pamm_ids": pamm_ids},
        dataType: "json",
        async: false
    }).done(function(json) {
            data = json;
    });

    return data;

}

// Начальный зум: последний месяц
function zoomChart() {
    chart.zoomToIndexes(chartData['stat'].length - 30, chartData['stat'].length - 1);
}

// Создание графика Amcharts из данных полученных с сервера
function drawChart()
{
        chartData = getChartData();

        // Объект графика
        chart = new AmCharts.AmSerialChart();
        chart.marginTop = 0;
        chart.autoMarginOffset = 5;
        chart.pathToImages = "http://www.amcharts.com/lib/images/";
        chart.zoomOutButton = {
            backgroundColor: '#000000',
            backgroundAlpha: 0.15
        };
        chart.dataProvider = chartData['stat'];
        chart.categoryField = "date";

        // Оси координат
        var categoryAxis = chart.categoryAxis;
        categoryAxis.parseDates = true;
        categoryAxis.minPeriod = "DD";
        categoryAxis.dashLength = 2;
        categoryAxis.gridAlpha = 0.15;
        categoryAxis.axisColor = "#DADADA";

        var valueAxis = new AmCharts.ValueAxis();
        valueAxis.axisColor = "#FF6600";
        valueAxis.axisThickness = 2;
        valueAxis.gridAlpha = 0;
        chart.addValueAxis(valueAxis);

        // Создание графиков доходности

        chartData['labels'].forEach(function(entry) {
            var graph1 = new AmCharts.AmGraph();
            graph1.id = entry;
            graph1.title = entry;
            graph1.valueField = entry;
            graph1.bullet = "round";
            graph1.hideBulletsCount = 30;
            graph1.balloonText = entry +": [[value]]%";
            chart.addGraph(graph1);
        });

        // Курсор
        var chartCursor = new AmCharts.ChartCursor();
        chartCursor.cursorPosition = "mouse";
        chart.addChartCursor(chartCursor);

        // Скролл
        var chartScrollbar = new AmCharts.ChartScrollbar();
        chartScrollbar.autoGridCount = true;
        chartScrollbar.graph = chartData['labels'][0];
        chartScrollbar.scrollbarHeight = 40;
        chart.addChartScrollbar(chartScrollbar);

        // Легенда на графике
        var legend = new AmCharts.AmLegend();
        legend.marginLeft = 110;
        chart.addLegend(legend);
        chart.addListener("init", zoomChart);
        zoomChart();

        chart.write("chartdiv");
}

