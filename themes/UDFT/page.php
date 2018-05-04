<?php
/**
* Чистый Шаблон для разработки
* Шаблон обычной страницы
* @package WordPress
* @subpackage clean
*/

get_header(); ?>
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
<div class="page-content-top">
	<div class="breadcrumbs-box"><?php echo kmwp_breadcrumbs(); ?></div>
</div>
<?php kmwp_get_template_part( 'templates/content/page-content', null, 'echo' ); ?>
<?php endwhile; ?>
<?php /*get_sidebar();*/ ?>
<?php get_footer(); ?>