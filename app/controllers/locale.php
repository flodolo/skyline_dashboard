<?php
namespace Dashboard;

use Cache\Cache;

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

// Completion chart.js graph for locale
$modules = array_keys($module_names);

$cache_id = "locale_stats_{$requested_locale}";
if (! $locale_stats = Cache::getKey($cache_id, 60 * 60)) {
    $locale_stats = [];
    foreach ($modules as $module) {
        $locale_stats[$module] = [];
    }
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
    Cache::setKey($cache_id, $locale_stats);
}

$graph_data = "<script type=\"text/javascript\">\n";

$labels = '    let dates = [';
foreach (array_keys($full_stats) as $date) {
    $labels .= '"' . $date . '",';
}
$labels .= "]\n";
$graph_data .= $labels;

foreach ($modules as $module) {
    $graph_data .= "    let {$module} = [" . implode(',', $locale_stats[$module]) ."]\n";
}
$graph_data .= "
    let ctx = document.getElementById(\"localeChart\");
    let localeChart = new Chart(ctx, {
    type: 'line',
    options: {
        legend: {
            position: \"right\"
        },
        scales: {
            xAxes: [{
                type: 'time',
                time: {
                    unit: 'day'
                }
            }],
            yAxes: [{
                scaleLabel: {
                    display: true,
                    labelString: '% completion'
                }
            }]
        },
        title: {
            display: true,
            text: 'Completion Level',
            fontSize: 24,
            padding: 10
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
            backgroundColor: \"" . $module_colors[$module] . "\",
            borderColor: \"" . $module_colors[$module] . "\"
        },
    ";
}

$graph_data .= "]
    }});

";

// Completion chart.js graph for total/missing strings
$cache_id = "locale_numbers_{$requested_locale}";
if (! $locale_numbers = Cache::getKey($cache_id, 60 * 60)) {
    $locale_numbers = [
        'missing' => [],
        'total'   => [],
    ];

    foreach ($full_stats as $date => $date_data) {
        $total_date = $missing_date = 0;
        foreach ($date_data as $module_id => $data) {
            if (isset($data[$requested_locale])) {
                $total_date += $data[$requested_locale]['total'];
                $missing_date += $data[$requested_locale]['missing'];
            }
        }
        $locale_numbers['total'][] = $total_date;
        $locale_numbers['missing'][] = $missing_date;
    }

    Cache::setKey($cache_id, $locale_numbers);
}

$graph_data .= "    let missing = [" . implode(',', $locale_numbers['missing']) ."]\n";
$graph_data .= "    let total = [" . implode(',', $locale_numbers['total']) ."]\n";

$graph_data .= "
    let ctxStrings = document.getElementById(\"localeChartStrings\");
    let localeChartStrings = new Chart(ctxStrings, {
    type: 'line',
    options: {
        legend: {
            position: \"right\"
        },
        scales: {
            xAxes: [{
                type: 'time',
                time: {
                    unit: 'day'
                }
            }],
            yAxes: [{
                scaleLabel: {
                    display: true,
                    labelString: 'Number of Strings'
                }
            }]
        },
        title: {
            display: true,
            text: 'Missing and Total Strings',
            fontSize: 24,
            padding: 10
        }
    },
    data: {
        labels: dates,
        datasets: [";
$graph_data .= "
        {
            data: missing,
            label: \"Missing Strings\",
            fill: false,
            backgroundColor: \"#8dd3c7\",
            borderColor: \"#8dd3c7\"
        },
        {
            data: total,
            label: \"Total Strings\",
            fill: false,
            backgroundColor: \"#fdb462\",
            borderColor: \"#fdb462\"
        },
    ]
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


$module_name = isset($module_names[$requested_module])
    ? $module_names[$requested_module]
    : 'All';
$locale_name = $requested_locale;
$tier_name = 'All';

$page_title = 'Locale View';
$selectors_enabled = true;
$sub_template = 'locale.php';
