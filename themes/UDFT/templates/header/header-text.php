<?php

/**
 * Header Text Template
 */

global $post, $kmwp;

$img_meta = kmwp_get_img_meta( $kmwp['header-text-img']['id'] );
$header_text_img = $kmwp['header-text-img']['url'];

?>


<img class="header-image" <?php echo $img_meta['title']; ?> <?php echo $img_meta['alt']; ?> src="<?php echo $header_text_img; ?>">
<div class="form-box">
	<div class="caption">
		<div class="main-title">The Most Effective Hormone Replacement Therapies</div>
	</div>
	<a class="btn large has-arrow" href="/get-started">get started</a>
</div>