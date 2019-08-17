<?php

$html_detail_body = '';

$module_name = isset($module_names[$requested_module])
    ? $module_names[$requested_module]
    : $requested_module;
$locale_name = 'All';
$tier_name = 'All';

$module_data = $latest_stats[$requested_module];
foreach ($module_data as $locale => $data) {
    $percentage = $data['completion'];
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
		<td>{$percentage}&nbsp;%</td>
		<td>{$data['missing']}</td>
	</tr>
	";
}

$page_title = 'Product View';
$selectors_enabled = true;
$sub_template = 'module.php';
