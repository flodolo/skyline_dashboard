<?php

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
            $html_detail_body .= "\t\t<td>&nbsp;</td>\n";
        } else {
            if ($locale_data == 100) {
                $class = 'success';
            } elseif ($locale_data > 50) {
                $class = 'warning';
            } else {
                $class = 'danger';
            }
            $html_detail_body .= "\t\t<td class=\"{$class}\">{$locale_data}&nbsp;%</td>\n";
        }
    }
    $html_detail_body .= "\t</tr>";
}

$locale_name = 'All';
$module_name = 'All';

$page_title = 'Tier View';
$selectors_enabled = true;
$sub_template = 'tier.php';
