<!DOCTYPE html>
<?php
$lang = esc_attr(get_field('lang', 'option') ?: 'en');
?>
<!--[if lt IE 7]>
<html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="<?php echo $lang; ?>"> <![endif]-->
<!--[if IE 7]>
<html class="no-js lt-ie9 lt-ie8" lang="<?php echo $lang; ?>"> <![endif]-->
<!--[if IE 8]>
<html class="no-js lt-ie9" lang="<?php echo $lang; ?>"> <![endif]-->
<!--[if gt IE 8]><!-->
<!--[if lt IE 10]>
<html class="no-js ie-9" lang="<?php echo $lang; ?>"><![endif]-->
<html class="no-js" lang="<?php echo $lang; ?>">
<!--<![endif]-->

<head>

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="format-detection" content="telephone=no">
	<meta name="IE_RM_OFF" content="true">
	<?php
	$bing_verify = get_field('bing_verification', 'options');
	if ($bing_verify) {
		echo '<meta name="msvalidate.01" content="' . $bing_verify . '">';
	}
	?>
	<title><?php wp_title(); ?></title>

	<link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg" />
	<link rel="shortcut icon" href="/favicon/favicon.ico" />
	<link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />
	<link rel="manifest" href="/favicon/site.webmanifest" />

	<meta name="google-site-verification" content="9SalED68BJlFkiBK1_xPB_c0eK3n7r9Rs5cNxtWGnXA" />
	<?php wp_head(); ?>
	<!--[if lte IE 8]>
    <script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/v2-legacy.js"></script>
    <![endif]-->
</head>
<?php
$trans_nav = false; // This wasn't set, so was filling up the php error log on the server
$behavior  = get_field('behavior', 'option');
$logo      = get_field('sitelogo', 'options');
$hide_nav  = get_field('hide_nav');
$nn        = ($_GET['nn'] ?? '');

add_filter('body_class', function ($classes) use ($behavior, $hide_nav, $nn) {
	if ($hide_nav || $nn === '1') {
		$classes[] = 'hide-nav';
	}
	$classes[] = $behavior . '-nav';

	return $classes;
});

$header_classes = [];
if ($behavior === 'sticky' || $behavior === 'peekaboo') {
	$header_classes[] = 'position-sticky';
	$header_classes[] = 'top-0';
}

$header_attrs = [];
$navbar_attrs = [];

$is_transparent = false;
if (is_single() || is_page()) {
	$is_transparent = get_field('transparent_navigation');
}
if ($is_transparent) {
	$header_attrs[] = 'data-bs-theme="dark"';
	$navbar_attrs[] = 'data-bs-theme="dark"';

	if (get_field('sitelogo_transparent', 'options')) {
		$logo = get_field('sitelogo_transparent', 'options');
	}
}
?>

<body data-bs-theme="light" <?php body_class(); ?>>
	<a tabindex="1" href="#skipNav" class="z-9999 visually-hidden-focusable px-3 py-2 btn btn-primary position-absolute">Skip Navigation</a>
	<header id="header" class="<?php echo implode(' ', $header_classes); ?>" <?php echo implode(" ", $header_attrs) ?>>

		<div id="navbar" class="nav-wrapper">
			<nav class="utility-nav navbar navbar-expand p-0 d-none d-lg-block" aria-label="Utility Navigation" data-bs-theme="light">
				<div class="nav-container pt-3 d-flex align-items-center justify-content-lg-end ms-auto gap-lg-5">
					<?php $utilitynav = wp_nav_menu([
						'theme_location' => 'utility',
						'container'      => '',
						'menu_id'        => 'utility-menu',
						'menu_class'     => 'navbar-nav d-flex align-items-center gap-lg-4',
						'fallback_cb'    => false,
						'walker'         => new Level\NavWalker(),
						'echo'           => false,
					]);

					$utilitynav = str_replace('>Search<', '><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg>Search<', $utilitynav);
					echo $utilitynav; ?>

					<?php if (get_field('is_language_dropdown', 'options')) {
						echo do_shortcode('[gtranslate]');
					} ?>
				</div>

			</nav>

			<nav class="main-nav-wrapper navbar navbar-expand-lg p-lg-0" aria-label="Main Navigation">
				<div class="nav-container d-block d-lg-flex align-items-center">
					<div class="brand-wrapper">
						<a class="navbar-brand" href="<?php echo home_url(); ?>" title="<?php echo bloginfo('name'); ?>">
							<?php if (! empty($logo)) {
								echo wp_get_attachment_image($logo, 'full', false, []);
							}; ?>

							<?php if (! empty($logo_light)) {
								echo wp_get_attachment_image($logo_light, 'full', false, []);
							}; ?>

						</a>

						<div class="d-lg-none ms-auto me-2">
							<?php if (get_field('is_language_dropdown', 'options')) {
								echo do_shortcode('[gtranslate]');
							} ?>
						</div>

						<button type="button" class="mobile-toggle navbar-toggler collapsed" data-bs-toggle="collapse" data-bs-target="#mainNavDropdown">
							<span class="visually-hidden">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
					</div>

					<div class="nav-wrapper flex-fill">

						<div class="main-nav">
							<div class="collapse navbar-collapse" id="mainNavDropdown">
								<?php wp_nav_menu([
									'theme_location' => 'main-menu',
									'container'      => '',
									'menu_class'     => 'navbar-nav ms-auto gap-lg-3',
									'menu_id'        => 'main-menu',
									'fallback_cb'    => false,
									'walker'         => new Level\NavWalker(),
								]); ?>
							</div>

						</div>

					</div>
				</div>
			</nav>
		</div>

		<?php if (is_single() && get_post_type() === 'post') : ?>
			<div class="progress-bar-container">
				<div class="progress-bar" id="progressBar"></div>
			</div>
		<?php endif; ?>
	</header>

	<div id="skipNav" class="visually-hidden"></div>

	<main id="main">