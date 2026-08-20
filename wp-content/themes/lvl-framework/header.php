<!DOCTYPE html>
<?php
$lang = esc_attr( get_field( 'lang', 'option' ) ?: 'en' );
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
	$bing_verify = get_field( 'bing_verification', 'options' );
	if ( $bing_verify ) {
		echo '<meta name="msvalidate.01" content="' . $bing_verify . '">';
	}
	?>
    <title><?php wp_title(); ?></title>

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

	<?php wp_head(); ?>
    <!--[if lte IE 8]>
    <script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/v2-legacy.js"></script>
    <![endif]-->
</head>
<?php
$trans_nav = false; // This wasn't set, so was filling up the php error log on the server
$behavior  = get_field( 'behavior', 'option' );
$logo      = get_field( 'sitelogo', 'options' );
$hide_nav  = get_field( 'hide_nav' );
$nn        = ( $_GET['nn'] ?? '' );

add_filter( 'body_class', function ( $classes ) use ( $behavior, $hide_nav, $nn ) {
	if ( $hide_nav || $nn === '1' ) {
		$classes[] = 'hide-nav';
	}
	$classes[] = $behavior . '-nav';

	return $classes;
} );

$header_classes = [];
if ( $behavior === 'sticky' || $behavior === 'peekaboo' ) {
	$header_classes[] = 'position-sticky';
	$header_classes[] = 'top-0';
}

$header_attrs = [];
$navbar_attrs = [];

$is_transparent = false;
if ( is_single() || is_page() ) {
	$is_transparent = get_field( 'transparent_navigation' );
}
if ( $is_transparent ) {
	$header_attrs[] = 'data-bs-theme="dark"';
	$navbar_attrs[] = 'data-bs-theme="dark"';

	if ( get_field( 'sitelogo_transparent', 'options' ) ) {
		$logo = get_field( 'sitelogo_transparent', 'options' );
	}
}
?>
<body data-bs-theme="light" <?php body_class(); ?>>
<a tabindex="1" href="#skipNav" class="z-9999 visually-hidden-focusable px-3 py-2 btn btn-primary position-absolute">Skip Navigation</a>
<!--	<div class="site-wrapper">-->
<header id="header" class="<?php echo implode( ' ', $header_classes ); ?>" <?php echo implode( " ", $header_attrs ) ?>>
    <div id="navbar" class="nav-wrapper">
        <nav class="utility-nav navbar navbar-expand p-0" aria-label="Utility Navigation" data-bs-theme="light">
            <div class="nav-container container-fluid justify-content-lg-end ms-auto">
                <div class="collapse navbar-collapse justify-content-lg-end" id="topNavDropdown">
					<?php $utilitynav = wp_nav_menu( [
						'theme_location' => 'utility',
						'container'      => '',
						'menu_id'        => 'utility-menu',
						'menu_class'     => 'navbar-nav d-flex align-items-center',
						'fallback_cb'    => false,
						'walker'         => new Level\NavWalker(),
						'echo'           => false,
					] );
					echo str_replace( '>Search<', '>
                                <button style="color:var(--bs-navbar-color);" class="search-btn btn no-wrap ps-1 fw-normal" data-bs-toggle="modal" data-bs-target="#searchModal" aria-label="Click to open search window">
                                    <span class="visually-hidden">Search&nbsp;</span>
                                    <svg class="d-inline-block" xmlns="http://www.w3.org/2000/svg" width="17" height="18" viewBox="0 0 21 22" fill="none">
                                        <defs><title id="search-icon-title-2">Toggle search form visibility</title></defs>
                                        <circle cx="12" cy="9" r="7" stroke="currentColor" stroke-width="3"/>
                                        <path d="M7 15L2.5 19.5" stroke="currentColor" stroke-width="3" stroke-linecap="square"/>
                                    </svg>
                                </button>
                                <', $utilitynav ); ?>
                </div>
            </div>
        </nav>

        <nav class="main-nav-wrapper navbar navbar-expand-lg p-lg-0" aria-label="Main Navigation">
            <div class="nav-container container-lg-fluid d-block d-lg-flex">
                <div class="brand-wrapper">
                    <a class="navbar-brand" href="<?php echo home_url(); ?>" title="<?php echo bloginfo( 'name' ); ?>">
						<?php if ( ! empty( $logo ) ) {
							echo wp_get_attachment_image( $logo, 'full', false, [] );
						}; ?>

						<?php if ( ! empty( $logo_light ) ) {
							echo wp_get_attachment_image( $logo_light, 'full', false, [] );
						}; ?>

                    </a>
                    <button type="button" class="mobile-toggle navbar-toggler collapsed" data-bs-toggle="collapse" data-bs-target="#mobileNavDropdown">
						<span class="visually-hidden">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
                </div>
                <div class="nav-wrapper flex-fill">

                    <div class="main-nav">
                        <div class="collapse navbar-collapse" id="mainNavDropdown">
							<?php wp_nav_menu( [
								'theme_location' => 'main-menu',
								'container'      => '',
								'menu_class'     => 'navbar-nav ms-auto me-lg-0 gap-lg-2 mega-menu--wrapper',
								'menu_id'        => 'main-menu',
								'fallback_cb'    => false,
								'walker'         => new Level\NavWalker(),
							] ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</header>

<div id="skipNav" class="visually-hidden"></div>

<main id="main">