<?php


$html_detail_body = '';
foreach ($latest_stats as $module_name => $data) {
    if (! isset($data[$requested_locale])) {
        continue;
    }

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
	</tr>
	";
}

$page_title = 'Locale View';
$selectors_enabled = true;
$sub_template = 'locale.php';
