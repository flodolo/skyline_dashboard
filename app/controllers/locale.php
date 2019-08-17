<?php

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
		<td>{$percentage}&nbsp;%</td>
		<td>{$data[$requested_locale]['missing']}</td>
	</tr>";
}

$module_name = isset($module_names[$requested_module])
    ? $module_names[$requested_module]
    : 'All';
$locale_name = $requested_locale;
$tier_name = 'All';

$page_title = 'Locale View';
$selectors_enabled = true;
$sub_template = 'locale.php';
