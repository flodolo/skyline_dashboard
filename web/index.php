<?php
namespace Dashboard;

include realpath(__DIR__ . '/../app/inc/init.php');
include "{$root_folder}/app/inc/query_params.php";

if ($requested_tier != 'all') {
    $controller = 'tier';
} else {
    $controller = $requested_module != 'all'
        ? 'module'
        : 'locale';
}

include "{$root_folder}/app/controllers/{$controller}.php";
include "{$root_folder}/app/templates/base.php";
