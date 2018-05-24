<?php
/**
* Чистый Шаблон для разработки
* Шаблон футера
*/

kmwp_finish_content_layout();

do_action( 'kmwp_after_post_content' );

echo '</div> <!-- content-wrap -->
    
    </div><!-- body-wrap -->

<footer>';

do_action( 'kmwp_footer_begin' );

echo '
    <div class="f-divider"></div>
    <div class="container">
        <div class="f-box2 row">
            <div class="f-col1 col col-lg-4 col-6">
                <div class="f-title">get started</div>
                <button class="btn medium">Fill Out <br>Medical History Form</button>
            </div>
            <div class="f-col2 col col-lg-4 col-6">
                <div class="f-title">our clinic</div>' .

                 wp_nav_menu(
                     array(
                         'theme_location' => 'footer-location',
                         'menu_class' => 'f-menu',
                         'echo' => false
                     )
                 ) .

           '</div>
            <div class="f-col3 col col-lg-4 col-12">
                <div class="f-title">contacts</div>
                <div class="f-col-content">
                    <a class="footer-title" href="#">kingsberg medical</a>
                    <div class="f-line">2800 West State Road - 84, suite 118</div>
                    <div class="f-line">Fort Lauderdale, FL 33312</div>
                </div>
                <div class="f-col-content">
                    <div class="f-line">PHONE: <a href="tel:800-787-0408">800-787-0408</a></div>
                    <div class="f-line">FAX: <a href="tel:954-321-8882">954-321-8882</a></div>
                    <div class="f-line"><a href="mailto:info@kingsbergmedical.com">info@kingsbergmedical.com</a></div>
                </div>
            </div>
        </div>
    </div>
</footer>';

	wp_footer(); // Необходимо для нормальной работы плагинов
?>
</body>
</html>