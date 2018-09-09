<?php
/**
 * Template Name: Get Started Page Template CURRENT
 * The template to display Get Started Page
 *
 */

get_header();

?>

<?php

echo '

	<section class="prefs-section st-section">
		<article>
			<div class="container">
				<h1 class="section-title">3 Easy Steps to Get Started Growth Hormone and Testosterone Therapies</h1>
				<div class="prefs-box row">
					<div class="single-pref col col-lg-4 col-12">
						<div class="pref-digit digit1">
							<span></span>
							<div class="pref-shadow">
								<!--<div class="pref-shadow inner"></div>-->
							</div>
						</div>
						<div class="pref-content-box">
							<h3>Contact Kingsberg Medical</h3>
							<div class="pref-content">
								Fill out &quot;get started&quot; form or Call us!<br>That&lsquo;s it... it&lsquo;s easy
							</div>
						</div>
					</div>
					<div class="single-pref col col-lg-4 col-12">
						<div class="pref-digit digit2">
							<span></span>
							<div class="pref-shadow">
								<!--<div class="pref-shadow inner"></div>-->
							</div>
						</div>
						<div class="pref-content-box">
							<h3>Blood Work and Diagnostic</h3>
							<div class="pref-content">
								With a zip code, we will schedule for blood testing and a physical exam near your home or office.
							</div>
						</div>
					</div>
					<div class="single-pref col col-lg-4 col-12">
						<div class="pref-digit digit3">
							<span></span>
							<div class="pref-shadow">
								<!--<div class="pref-shadow inner"></div>-->
							</div>
						</div>
						<div class="pref-content-box">
							<h3>Get Prescription From a Doctor</h3>
							<div class="pref-content">
								If your lab work indicates GH deficiency or low Testosterone, one of our local doctors will prescribe you all necessary hormone replacement therapies.
							</div>
						</div>
					</div>
				</div>
			</div>
		</article>
	</section>
  
    <section class="get-started-section st-section">
        <article>
            <div class="container">' .
                 do_shortcode( '[kmwp_get_content_form]' ) .
            '</div>
        </article>
    </section>';    	

?>


<?php
    get_footer();
?>
