<?php
namespace Dashboard;

use Cache\Cache;
use Json\Json;

// Get the most recent data
if (! file_exists("{$root_folder}/app/data/statistics.json")) {
    exit('Folder statistics.json does not exist.');
}
$json_file = file_get_contents("{$root_folder}/app/data/statistics.json");
$stats = json_decode($json_file, true);
$latest_stats = $stats[end(array_keys($stats))];

$supported_locales = [];
foreach ($latest_stats as $product_id => $product_data) {
    $supported_locales = array_merge(array_keys($product_data), $supported_locales);
}
$supported_locales = array_unique($supported_locales);
sort($supported_locales);

$requested_locale = isset($_REQUEST['locale'])
    ? htmlspecialchars($_REQUEST['locale'])
    : Utils::detectLocale($supported_locales, 'it');
if (! in_array($requested_locale, $supported_locales) && $requested_locale != 'all') {
    exit("Locale {$requested_locale} is not supported");
}
$html_supported_locales = '';
foreach ($supported_locales as $supported_locale) {
    // Add to locale selector
    $supported_locale_label = str_replace('-', '&#8209;', $supported_locale);
    $html_supported_locales .= "<a href=\"?locale={$supported_locale}\">{$supported_locale_label}</a> ";
}

$requested_module = isset($_REQUEST['module'])
    ? htmlspecialchars($_REQUEST['module'])
    : 'all';
$supported_modules = array_keys($latest_stats);
if ($requested_module != 'all' && ! in_array($requested_module, $supported_modules)) {
    exit("Unknown module {$requested_module}");
}
$html_supported_modules = '';

$module_names = [
    'androidl10n' => 'android-l10n',
    'fennec'      => 'Firefox for Android',
    'firefox'     => 'Firefox',
    'fxa'         => 'Firefox Accounts',
    'fxios'       => 'Firefox for iOS',
    'lockwiseios' => 'Lockwise for iOS',
    'monitor'     => 'Firefox Monitor',
    'mozillaorg'  => 'mozilla.org',
];

foreach ($supported_modules as $supported_module) {
    // Add to module selector
    $module_name = isset($module_names[$supported_module])
        ? $module_names[$supported_module]
        : $supported_module;
    $html_supported_modules .= "<a href=\"?module={$supported_module}&amp;locale=all\">{$module_name}</a> ";
}
