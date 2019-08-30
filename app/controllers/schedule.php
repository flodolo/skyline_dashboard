<?php
namespace Dashboard;

// Get the most recent data
if (! file_exists("{$root_folder}/app/data/delivery.json")) {
    exit('File delivery.json does not exist.');
}
$json_file = file_get_contents("{$root_folder}/app/data/delivery.json");
$schedule_data = json_decode($json_file, true);

$initiatives = array_keys($schedule_data);

$date_from = strtotime('2019-07-05');
$date_to = strtotime('now');

$dates_js = 'let dates = [';
$dates = [];
for ($d = $date_from; $d <= $date_to; $d += 86400) {
    $dates_js .= '"' . date('Y-m-d', $d) . '",';
    $dates[]= date('Y-m-d', $d);
}
$dates_js .= "];\n";

$graph_data = '
<script type="text/javascript">
    let container = document.getElementById("mainbody");
    let graphCanvas;
    let chart;
    let ctx;
    ' . $dates_js . '

    ';

foreach ($schedule_data as $project_name => $project_data) {
    $project_id = md5($project_name);

    $graph_data .= '
    graphCanvas = document.createElement("canvas");
    graphCanvas.id = "' . $project_id . '";
    container.appendChild(graphCanvas);
    ctx = document.getElementById("' . $project_id . '");
    chart = new Chart(ctx, {
        type: "bar",
        options: {
            legend: {
                position: "right"
            },
            scales: {
                yAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: "Words Count"
                    }
                }]
            },
            title: {
                display: true,
                text: "' . $project_name . '",
                fontSize: 24,
                padding: 10
            }
        },
        data: {
        labels: dates,
        datasets: [{
            label: "Estimated",
            backgroundColor: "blue",
            data: [';

    foreach ($dates as $day) {
        $graph_data .= isset($project_data['estimated'][$day])
            ? $project_data['estimated'][$day] . ','
            : '0,';
    }

    $graph_data .= ']
        }, {
            label: "Actual",
            backgroundColor: "red",
            data: [';

    foreach ($dates as $day) {
        $graph_data .= isset($project_data['actual'][$day])
            ? $project_data['actual'][$day] . ','
            : '0,';
    }

    $graph_data .= ']
        }
        ]
    }});

    ';
}

$graph_data .= '</script>';

$page_title = 'Schedule Graphs';
$selectors_enabled = false;
