<?php
/**
* Чистый Шаблон для разработки
* Шаблон хэдера
* @package WordPress
* @subpackage clean
*/
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=360, initial-scale=1, maximum-scale=1.0, user-scalable=no">

<link rel="alternate" type="application/rdf+xml" title="RDF mapping" href="<?php bloginfo('rdf_url'); ?>" />
<link rel="alternate" type="application/rss+xml" title="RSS" href="<?php bloginfo('rss_url'); ?>" />
<link rel="alternate" type="application/rss+xml" title="Comments RSS" href="<?php bloginfo('comments_rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/style.css">
 <!--[if lt IE 9]>
 <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
 <![endif]-->
<title><?php kmwp_site_title(); ?></title>
<?php

    global $post, $kmwp;

    $kmwp = correct_global_var();
    $header_content = kmwp_get_header_content();

	wp_head();
?>
</head>
<body <?php	body_class(); ?>>

<?php

echo '
<header class="custom-header" ' . $kmwp['header_calc_bg'] . '>

    <div class="container-fluid">

        <div class="top-panel-box">
            <div class="line-contacts">
                <div class="container">
                    <div class="header-contacts-box row">
                        <div class="header-contacts col col-lg-6">
                            <div class="contacts">
                                <a href="/about-us/">about us</a><a href="/contacts/">contacts</a>
                            </div>
                            <div class="phone">
                                <a href="tel:877-321-8885">877-321-8885</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="line-menu">
                <div class="header-menu-box container">
                    <div class="header-menu-row row">
                        <div class="header-logo col col-lg-4 col-xs-12">
                            <a class="logo-link" href="/">
                                <div class="logo"></div>
                                <span>kingsberg medical</span>
                            </a>
                        </div>' .

                        wp_nav_menu(
                                array(
                                    'theme_location' => 'header-location',
                                    'container_class' => 'header-menu col col-xs-12',
	                                'menu_class' => 'menu',
                                    'echo' => false,
                                    'link_before' => '<span>',
                                    'link_after' => '</span>',
                                    'walker' => new kmwp_nav_menu()
                                )
                        ) .

                    '</div>
                </div>
                <div class="menu-button"><span></span><span></span><span></span></div>
            </div>
        </div>

        <div class="header-main">
            <div class="container">
                <div class="row">
                    <div class="header-content col col-lg-12">' .
                        $header_content .
                    '</div>
                </div>
            </div>
        </div>

    </div>

</header>

<div class="body-wrap ' . $kmwp['box-type'] . '">
    
    <div class="content-wrap row">';

    echo kmwp_get_the_title( $post->ID );

    kmwp_start_content_layout();

?>