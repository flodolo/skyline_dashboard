<?php
namespace Dashboard;

// Get the most recent data
if (! file_exists("{$root_folder}/app/data/statistics.json")) {
    exit('Folder statistics.json does not exist.');
}
$json_file = file_get_contents("{$root_folder}/app/data/statistics.json");
$full_stats = json_decode($json_file, true);
$last_day = end(array_keys($full_stats));
$latest_stats = $full_stats[$last_day];

$supported_locales = [];
foreach ($latest_stats as $product_id => $product_data) {
    $supported_locales = array_merge(array_keys($product_data), $supported_locales);
}
$supported_locales = array_unique($supported_locales);
sort($supported_locales);

// Store if we're using the default view
$default_view = ! isset($_REQUEST['locale']) && ! isset($_REQUEST['module']);

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
    exit("Unknown product {$requested_module}");
}
$html_supported_modules = '';

$module_names = [
    'fenix'           => 'Fenix',
    'fennec'          => 'Firefox for Android',
    'firefox'         => 'Firefox',
    'fxa'             => 'Firefox Accounts',
    'fxios'           => 'Firefox for iOS',
    'lockwiseandroid' => 'Lockwise for Android',
    'lockwiseios'     => 'Lockwise for iOS',
    'monitor'         => 'Firefox Monitor',
];

foreach ($supported_modules as $supported_module) {
    // Add to module selector
    $module_name = isset($module_names[$supported_module])
        ? $module_names[$supported_module]
        : $supported_module;
    $html_supported_modules .= "<a href=\"?module={$supported_module}&amp;locale=all\">{$module_name}</a> ";
}

$tiers = [
    'tier1' => ['de', 'en-CA', 'en-GB', 'fr'],
    'top15' => [
        'cs', 'de', 'es-AR', 'es-ES', 'es-MX', 'fr', 'hu', 'id', 'it', 'ja',
        'nl', 'pl', 'pt-BR', 'ru', 'zh-CN',
    ],
];

$tier_names = [
    'tier1' => 'Tier 1',
    'top15' => 'Top 15',
];

$requested_tier = isset($_REQUEST['tier'])
    ? htmlspecialchars($_REQUEST['tier'])
    : 'all';
$supported_tiers = array_keys($tiers);
if ($requested_tier != 'all' && ! in_array($requested_tier, $supported_tiers)) {
    exit("Unknown tiers {$requested_tier}");
}

// If default view, use 'tier1' and reset others
if ($default_view && $requested_tier == 'all') {
    $requested_tier = 'tier1';
    $requested_locale = 'all';
    $requested_module = 'all';
}
