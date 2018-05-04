<?php

/**
 * Header Text Template
 */

global $post, $kmwp;

$header_text_img = $kmwp['thank-you-img']['url'];

?>


<img class="header-image" src="<?php echo $header_text_img; ?>">
<div class="header-text-box">
	<div class="header-text-content-box">
		<div class="header-text-title">Thank You</div>
		<div class="header-text-content">
			<p>We received your initial inquiry. In keeping with our Privacy Policy, your information will be held in the strictest confidence. No one will ever see this information except our local doctors and their assistants.</p>
			<p>Please let us know a little about your current health. To expedite the process of Doctor Review, please click the <b>Medical History Form button</b> and complete the form. One of our local physician assistants will call you shortly.</p>
		</div>
		<a class="btn large mhf" href="/get-started">medical history form</a>
	</div>
</div>