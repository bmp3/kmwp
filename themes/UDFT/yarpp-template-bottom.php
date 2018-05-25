<?php 
/*
YARPP Template: Kinsberg YARPP Template Bottom
Author: Cron Agency Inc.
Description: Just YARPP template.
*/
?>
<div class="pa-items-box">
    <h3 class="pa-items-title">read this text</h3>
    <div class="pa-items">
        <?php if (have_posts()):?>
            <div class="pa-item-box">
                <?php while (have_posts()) : the_post(); ?>
                    <?php
                        global $post;
                        echo kmwp_get_archive_single_post( $post, 'related-post-thumb-large' );
                    ?>
                <?php endwhile; ?>
            </div>
                <?php else: ?>
                <p>No related posts.</p>
        <?php endif; ?>
    </div>
</div>
