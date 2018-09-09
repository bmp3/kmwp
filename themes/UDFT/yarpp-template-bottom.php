<?php 
/*
YARPP Template: Kinsberg YARPP Template Bottom
Author: Cron Agency Inc.
Description: Just YARPP template.
*/

$i = 0;

?>
<div class="pa-items-box">
    <h3 class="pa-items-title">read this text</h3>
    <div class="pa-items">
        <?php if (have_posts()):?>
            <div class="pa-item-box">
                <?php while (have_posts()) : the_post(); ?>
                    <?php
                        global $post;
                        if ( $i > 3 && $i < 8 ) {
	                        echo kmwp_get_archive_single_post( $post, 'related-post-thumb-large' );
                        }
                        $i++;
                    ?>
                <?php endwhile; ?>
            </div>
                <?php else: ?>
                <p>No related posts.</p>
        <?php endif; ?>
    </div>
</div>