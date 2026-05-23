<?php
$this->CI = &get_instance();
?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<title><?= phpb_e($page->get('title')) ?></title>
	<meta name="description" content="<?= phpb_e($page->get('meta_description')) ?>">
	<?php $this->CI->load->view('pendaftaran/components/dependency'); ?>
	<script src="<?= base_url('assets/admin/js/font-awesome.js') ?>" async></script>

	<!-- THEME SPECIFIC -->
	<link href="<?= phpb_theme_asset('/public/css/theme/blue.css') ?>" rel="stylesheet" />

</head>

<body>
	<?php
	echo $body;
	?>
	<!-- Run PHPageBuilder script.js files -->
	<script type="text/javascript">
		document.querySelectorAll("script").forEach(function(scriptTag) {
			scriptTag.dispatchEvent(new Event('run-script'));
		});
	</script>
</body>

</html>