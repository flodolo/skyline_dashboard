<?php
namespace Dashboard;

use Cache\Cache;

// Create the table's body
$html_detail_body = '';

$tier_name = isset($tier_names[$requested_tier])
    ? $tier_names[$requested_tier]
    : $requestedrequested_tier_module;

$requested_locales = $tiers[$requested_tier];
$locales_number = count($requested_locales);

$table_header = '<th>Product</th>';
foreach ($requested_locales as $locale) {
    $table_header .= "<th class=\"locale\">{$locale}</th>\n";
}

foreach ($latest_stats as $module_id => $data) {
    $module_name = isset($module_names[$module_id])
        ? $module_names[$module_id]
        : $module_id;

    $row_data = [];
    $total_row = 0;
    foreach ($requested_locales as $locale) {
        if (! isset($data[$locale])) {
            $row_data[] = '';
            // Consider this as complete for visualization purposes
            $total_row += 100;
        } else {
            $row_data[] = $data[$locale]['completion'];
            $total_row += $data[$locale]['completion'];
        }
    }

    $percentage = $total_row / $locales_number;
    if ($percentage == 100) {
        $class = 'success';
    } elseif ($percentage > 50) {
        $class = 'warning';
    } else {
        $class = 'danger';
    }
    $html_detail_body .= "
	<tr>
        <th>{$module_name}</th>";
    foreach ($row_data as $locale_data) {
        if ($locale_data == '') {
            $html_detail_body .= "\t\t<td> </td>\n";
        } else {
            if ($locale_data == 100) {
                $class = 'success';
            } elseif ($locale_data > 50) {
                $class = 'warning';
            } else {
                $class = 'danger';
            }
            $html_detail_body .= "\t\t<td class=\"{$class}\">{$locale_data} %</td>\n";
        }
    }
    $html_detail_body .= "\t</tr>";
}

// Average completion chart.js graph for locale
$cache_id = "locale_averages_{$requested_tier}";
if (! $locale_averages = Cache::getKey($cache_id, 60 * 60)) {
    $locale_averages = [];
    foreach ($full_stats as $date => $date_data) {
        foreach ($requested_locales as $locale) {
            $locale_avg = $modules_count = 0;
            foreach ($date_data as $module_id => $module_data) {
                if (isset($module_data[$locale])) {
                    $modules_count += 1;
                    $locale_avg += $module_data[$locale]['completion'];
                }
            }
            $locale_averages[$locale][] = round($locale_avg / $modules_count, 2);
        }
    }
    Cache::setKey($cache_id, $locale_averages);
}

$graph_data = "<script type=\"text/javascript\">\n";

$labels = '    let dates = [';
foreach (array_keys($full_stats) as $date) {
    $labels .= '"' . $date . '",';
}
$labels .= "]\n";
$graph_data .= $labels;

foreach ($requested_locales as $locale) {
    $graph_data .= "    let {$locale} = [" . implode(',', $locale_averages[$locale]) ."]\n";
}
$graph_data .= "
    let ctx = document.getElementById(\"localesChart\");
    var myChart = new Chart(ctx, {
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
                    labelString: 'Average completion'
                }
            }]
        },
        title: {
            display: true,
            text: 'Average Completion Level',
            fontSize: 24,
            padding: 10
        }
    },
    data: {
        labels: dates,
        datasets: [";

$colors = [
    '#8dd3c7', '#ffffb3', '#bebada', '#fb8072', '#80b1d3', '#fdb462',
    '#b3de69', '#fccde5', '#d9d9d9', '#bc80bd', '#ccebc5', '#ffed6f',
    '#a6cee3', '#1f78b4', '#b2df8a', '#33a02c', '#fb9a99', '#e31a1c',
    '#fdbf6f', '#ff7f00', '#cab2d6', '#6a3d9a', '#ffff99', '#b15928',
];

$i = 0;
foreach ($requested_locales as $locale) {
    $graph_data .= "
        {
            data: {$locale},
            label: \"" . $locale . "\",
            fill: false,
            backgroundColor: \"" . $colors[$i] . "\",
            borderColor: \"" . $colors[$i] . "\"
        },
    ";

    $i += 1;
    if ($i >= count($colors)) {
        $i = 0;
    }
}

$graph_data .= "]
    }});

";

$locale_name = 'All';
$module_name = 'All';

$page_title = 'Tier View';
$selectors_enabled = true;
$sub_template = 'tier.php';
