<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset=utf-8>
	<title>L10N Dashboard - <?php echo $page_title; ?></title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css" media="all" />
  <link rel="stylesheet" href="assets/css/datatables.min.css" type="text/css" media="all" />
  <link rel="stylesheet" href="assets/css/datatables.bootstrap4.min.css" type="text/css" media="all" />
  <link rel="stylesheet" href="assets/css/main.css" type="text/css" media="all" />
  <script src="assets/js/jquery-3.4.1.min.js"></script>
  <script src="assets/js/bootstrap.min.js"></script>
  <script src="assets/js/datatables.min.js"></script>
  <script src="assets/js/datatables.bootstrap4.min.js"></script>
  <script src="assets/js/main.js"></script>
</head>
<body>
  <div class="container">
	<?php
        if ($selectors_enabled):
    ?>
	<h1>Skyline L10N Dashboard</h1>
	<p>See the <a href="https://github.com/flodolo/skyline_dashboard/">GitHub repository</a> for background information.</p>
    <h2>Locale: <?php echo $requested_locale; ?></h2>
	<div class="list locale_list">
      <p>
        Display localization status for a specific locale<br/>
        <?php echo $html_supported_locales; ?>
      </p>
    </div>
	<h2>Product: <?php echo $requested_module; ?></h2>
	<div class="list module_list">
      <p>
        Display localization status for a specific product<br/>
        <?php echo $html_supported_modules; ?>
      </p>
    </div>
	<?php
        endif;
    ?>

	<?php include "{$root_folder}/app/templates/{$sub_template}"; ?>

  </div>
</body>