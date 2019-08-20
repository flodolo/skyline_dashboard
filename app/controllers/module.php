<?php

$html_detail_body = '';

$module_name = isset($module_names[$requested_module])
    ? $module_names[$requested_module]
    : $requested_module;
$locale_name = 'All';
$tier_name = 'All';

$module_data = $latest_stats[$requested_module];
$groups = [
    'complete' => [],  // 100%
    'good'     => [],  // 95-100%
    'average'  => [],  // 75-95%
    'poor'     => [],  // 45-75%
    'bad'      => [],  // up to 45%
    'stale'    => [],  // 0%
];
foreach ($module_data as $locale => $data) {
    $percentage = $data['completion'];

    // Assign to group based on level of completion
    if ($percentage == 0) {
        $groups['stale'][] = $locale;
    } elseif ($percentage < 45) {
        $groups['bad'][] = $locale;
    } elseif ($percentage < 75) {
        $groups['poor'][] = $locale;
    } elseif ($percentage < 95) {
        $groups['average'][] = $locale;
    } elseif ($percentage < 100) {
        $groups['good'][] = $locale;
    } else {
        $groups['complete'][] = $locale;
    }

    if ($percentage == 100) {
        $class = 'success';
    } elseif ($percentage > 50) {
        $class = 'warning';
    } else {
        $class = 'danger';
    }
    $html_detail_body .= "
	<tr class=\"{$class}\">
		<td><a href=\"?locale={$locale}\">{$locale}</a></td>
		<td>{$data['total']}</td>
		<td>{$percentage} %</td>
		<td>{$data['missing']}</td>
	</tr>
	";
}

$graph_data = "<script type=\"text/javascript\">\n";
$graph_data .= '    let groups_data = [';
foreach ($groups as $group_locales) {
    $graph_data .= count($group_locales) . ",";
}
$graph_data .= "];\n";

$graph_data .= "    let groups_locales = [\n";
foreach ($groups as $group_locales) {
    $graph_data .= '        ["' . implode($group_locales, ',') . "\"],\n";
}
$graph_data .= "    ];";

$graph_data .= "
    let ctx = document.getElementById(\"moduleChart\");

    let options = {
        legend: {
            position: \"right\"
        },
        responsive: false,
        title: {
            display: true,
            text: 'Completion Level Distribution',
            fontSize: 24,
            padding: 10
        },
        tooltips: {
            displayColors : false,
            callbacks: {
                title: function(tooltipItem, data) {
                    return data['labels'][tooltipItem[0]['index']];
                },
                label: function(tooltipItem, data) {
                    return 'Locales (' + groups_data[tooltipItem.index] +'): ' + groups_locales[tooltipItem.index];
                }
            },
        }
    };

    let moduleChart = new Chart(ctx, {
        type: 'polarArea',
        options: options,
        data: {
            labels: [
                \"Complete\",
                \"More than 95%\",
                \"75% to 95%\",
                \"45% to 75%\",
                \"Up to 45%\",
                \"Untranslated\"
            ],
            datasets: [{
                data: groups_data,
                backgroundColor: [
                    \"rgba(179,0,0, 0.6)\",
                    \"rgba(227,74,51, 0.6)\",
                    \"rgba(252,141,89, 0.6)\",
                    \"rgba(253,187,132, 0.6)\",
                    \"rgba(253,212,158, 0.6)\",
                    \"rgba(254,240,217, 0.6)\"
                ],
            }],
        }
    });
</script>
";

$page_title = 'Product View';
$selectors_enabled = true;
$sub_template = 'module.php';
