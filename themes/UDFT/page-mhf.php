<?php
/*
 * Template Name: Medical History Form Template
*/
?>

<?php
    get_header();
?>
    <div class="page-content-top">
        <div class="breadcrumbs-box"><?php echo kmwp_breadcrumbs(); ?></div>
    </div>
    <div id="inner_content" class="clearfix">
    <div id="mhf-page container">
        <div id="content">
            <?php
                the_post();
                $content = get_the_content();
                $content = str_replace( '<h1>', '<h1 class="entry-title">', get_the_content() );
                $content = '<div class="mhf">'.$content.'</div>';
                echo $content;
                if(class_exists('MHF_Plugin'))
                    echo MHF_Plugin::get_medical_history_form();
            ?>
        </div><!-- #content -->
    </div><!-- #container -->
</div>
<?php get_footer(); ?>