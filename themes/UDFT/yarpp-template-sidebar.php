<?php
/*
YARPP Template: Kinsberg YARPP Template Sidebar
Author: Cron Agency Inc.
Description: Just YARPP template.
*/
?>
<h3 class="widgettitle">top stories</h3>
<div class="sidebar-items">
	<?php if (have_posts()):?>
		<?php while (have_posts()) : the_post(); ?>
			<?php
			global $post;

			$pbc = smarty_breadcrumbs( 'data', $post->ID );
			$p_link = '<a class="p-link" href="' . $pbc[1]['link'] . '">' . $pbc[1]['title'] . '</a>';

			$out =
				'<div class="sb-item">
                     <div class="sb-item-img"><a href="' . get_permalink( $post->ID ) . '">' . get_the_post_thumbnail( $post->ID, 'sidebar-thumb', array( 'class' => 'sidebar-size' ) ) . '</a></div>
                     <div class="sb-item-content">
                         <div class="sb-item-category"><span>' . $p_link . '</span></div>
                         <div class="sb-item-title"><a href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a></div>
                     </div>
                </div>';
			echo $out;
			?>
		<?php endwhile; ?>
	<?php else: ?>
		<p>No related posts.</p>
	<?php endif; ?>
</div>