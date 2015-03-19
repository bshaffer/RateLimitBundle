<html>
<head>
<title>Realtime Dashboard</title>
<script type="text/javascript" src="<?php echo $view['assets']->getUrl('bundles/perimeterratelimit/js/jquery.js') ?>"></script>
<script type="text/javascript" src="<?php echo $view['assets']->getUrl('bundles/perimeterratelimit/js/d3.v2.js') ?>"></script>
<script type="text/javascript" src="<?php echo $view['assets']->getUrl('bundles/perimeterratelimit/js/jquery.quicksand.js') ?>"></script>
<script type="text/javascript" src="<?php echo $view['assets']->getUrl('bundles/perimeterratelimit/js/gauge.js') ?>"></script>
<link type="text/stylesheet" rel="stylesheet" media="screen" href="<?php echo $view['assets']->getUrl('bundles/perimeterratelimit/css/dashboard.css') ?>"/>
</head>

<body>
<div>
    <h3>API Utilization</h3>
    <canvas id="gauge"></canvas>
    <ul id="requests"></ul>
    <div id="data"></div>
</div>

<script type="text/javascript">

var target = document.getElementById('gauge');
var gauge = new Gauge(target).setOptions({customFillStyle: void 0});
gauge.maxValue = 100;
gauge.animationSpeed = 128;

updateDashboard = function() {
    $.get("<?php echo $view['router']->generate('rate_limit_dashboard_data') ?>", "", function (response) {

        $("#data").html(response);

        var maxValue = parseInt(d3.select("#data_max_request_rate").attr("value"));
        var curValue = parseInt(d3.select("#data_request_rate").attr("value"));

        gauge.maxValue = Math.max(gauge.maxValue, maxValue);
        gauge.set(curValue);

        $('#requests').quicksand($('#new-requests li'), {
            retainExisting: false
        });

        setTimeout('updateDashboard()', 1000);
    },
    "text");
}

$(document).ready(function() {
    updateDashboard();
});

</script>

</body>
</html>