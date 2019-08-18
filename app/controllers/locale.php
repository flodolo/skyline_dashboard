<?php

// Create the table's body

$html_detail_body = '';
foreach ($latest_stats as $module_id => $data) {
    if (! isset($data[$requested_locale])) {
        continue;
    }

    $module_name = isset($module_names[$module_id])
        ? $module_names[$module_id]
        : $module_id;

    $percentage = $data[$requested_locale]['completion'];
    if ($percentage == 100) {
        $class = 'success';
    } elseif ($percentage > 50) {
        $class = 'warning';
    } else {
        $class = 'danger';
    }
    $html_detail_body .= "
	<tr class=\"{$class}\">
		<td>{$module_name}</td>
		<td>{$data[$requested_locale]['total']}</td>
		<td>{$percentage} %</td>
		<td>{$data[$requested_locale]['missing']}</td>
	</tr>";
}

// Prepare data for the chart.js graph
$locale_stats = [];
$modules = array_keys($module_names);
$colors = [
    'fenix'           => '#8dd3c7',
    'fennec'          => '#ffffb3',
    'firefox'         => '#bebada',
    'fxa'             => '#fb8072',
    'fxios'           => '#80b1d3',
    'lockwiseandroid' => '#a1ef12',
    'lockwiseios'     => '#fdb462',
    'monitor'         => '#b3de69',
    'mozillaorg'      => '#fccde5',
];
foreach ($modules as $module) {
    $locale_stats[$module] = [];
}
$dates = array_keys($full_stats);
foreach ($full_stats as $date => $date_data) {
    // Ensure that new modules are available also in older dates
    foreach ($modules as $module) {
        if (! isset($date_data[$module])) {
            $date_data[$module] = [];
        }
    }

    foreach ($date_data as $module_id => $data) {
        if (isset($data[$requested_locale])) {
            $locale_stats[$module_id][] = $data[$requested_locale]['completion'];
        } else {
            $locale_stats[$module_id][] = '';
        }
    }
}

$graph_data = "<script type=\"text/javascript\">\n";

$labels = '    let dates = [';
foreach ($dates as $date) {
    $labels .= '"' . $date . '",';
}
$labels .= "]\n";
$graph_data .= $labels;

foreach ($modules as $module) {
    $graph_data .= "    let {$module} = [" . implode(',', $locale_stats[$module]) ."]\n";
}
$graph_data .= "
    let ctx = document.getElementById(\"localeChart\");
    var myChart = new Chart(ctx, {
    type: 'line',
    options: {
        legend: {
            position: \"right\"
        },
        scales: {
            yAxes: [{
                scaleLabel: {
                    display: true,
                    labelString: '% completion'
                }
            }]
        }
    },
    data: {
        labels: dates,
        datasets: [";
foreach ($modules as $module) {
    $graph_data .= "
        {
            data: {$module},
            label: \"" . $module_names[$module] . "\",
            fill: false,
            backgroundColor: \"" . $colors[$module] . "\",
            borderColor: \"" . $colors[$module] . "\"
        },
    ";
}

$graph_data .= "]
    }});
</script>
";

$module_name = isset($module_names[$requested_module])
    ? $module_names[$requested_module]
    : 'All';
$locale_name = $requested_locale;
$tier_name = 'All';

$page_title = 'Locale View';
$selectors_enabled = true;
$sub_template = 'locale.php';
