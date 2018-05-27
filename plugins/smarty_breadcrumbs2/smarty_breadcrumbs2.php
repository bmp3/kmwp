<?php
/*
  Plugin Name: Smarty breadcrumbs  2
  Description: Can be based on menu or on url hierarchy or custom page parameter (parent_page_id)
  Version: 2.0
  Author: Cron Agency
 */

function assignMenuItem($mitem)
{
   if($mitem->object=="category")
   {
        return ConvertToBreadcrumbItem(get_category($mitem->object_id));
   }

   if($mitem->object=="page")
       return ConvertToBreadcrumbItem(get_post($mitem->object_id));

    return ConvertToBreadcrumbItem(get_post($mitem->object_id));
}

function ConvertToBreadcrumbItem($entry)
{
    if(is_a($entry, 'WP_Post'))
    {
        return array(
            'title'=>get_the_title($entry->ID),
            'link'=>get_the_permalink($entry->ID)
        );
    }
    if($entry->taxonomy=="category")
    {
        return array(
            'title'=>$entry->name,
            'link'=>get_category_link($entry->term_id)
        );
    }
    if($entry->taxonomy=="product_cat")
    {
        return array(
            'title'=>$entry->name.' category',
            'link'=>get_category_link($entry->term_id)
        );
    }
}

function smarty_breadcrumbs( $mode = 'html', $pid = null ) {
    $theme_locations = get_nav_menu_locations();
    $menu_object = wp_get_nav_menu_object($theme_locations["header-location"]);
    $menu_raw_items = wp_get_nav_menu_items($menu_object->term_id);

    if ( $mode = 'html' ) {
	    $current_page_id = get_the_ID();
    }
    else if ( isset( $pid ) ) {
	    $current_page_id = $pid;
    }

    if(is_category())
    {
        $current_page_id = get_the_category($current_page_id);
        $current_page_id = $current_page_id[0]->term_id;
    }

    if( function_exists('is_product_category') && is_product_category() )
    {
        $current_page_id = get_the_terms( $current_page_id, 'product_cat' );
        $current_page_id = $current_page_id[key($current_page_id)]->term_id;
        //        var_dump($current_page_id);
    }

    if(class_exists( 'WooCommerce' )&&is_shop())
    {
        $current_page_id = get_option( 'woocommerce_shop_page_id' );;
    }

    if ( is_array( $menu_raw_items ) ) {
	    foreach ( $menu_raw_items as $item ) {
		    $menu_items[ $item->ID ] = $item;
	    }
    }

    //    var_dump($current_page_id);

    $breadcrumbs = array();
	if ( is_array( $menu_items ) ) {
		foreach ( $menu_items as $mitem ) {
			if ( $mitem->object_id == $current_page_id ) {
				$breadcrumbs[] = assignMenuItem( $mitem );
				if ( $mitem->menu_item_parent != 0 ) {
					//var_dump( 'item', $mitem );
					$item = $mitem;
					while ( $item->menu_item_parent > 0 ) {
//                    var_dump($item->menu_item_parent);
						$item = $menu_items[ $item->menu_item_parent ];
						//                    var_dump($item);
						//                    var_dump(get_the_permalink($item->ID));
						//                    var_dump(is_a($item, 'WP_Post'));
						//                    var_dump(get_post($item->object_id));
						//                    var_dump(get_the_permalink(get_post($item->object_id)));
						//                    var_dump($item->taxonomy);
						//                    exit;
						if ( $item->object != 'custom' ) {
							$breadcrumbs[] = assignMenuItem( $item );
						} else {
							$breadcrumbs[] = array(
								'title' => $item->post_title,
								'link'  => $item->url
							);
						}
					}
				}
			}
		}
	}
    //    var_dump($breadcrumbs);
    //    exit;

    // if don't in menu
    //            echo '<pre>';
    //            var_dump($breadcrumbs);
    //            echo '</pre>';
    if(count($breadcrumbs)==0)
    {
        if(is_page())
        {
            $page = get_post(get_the_ID());
            $breadcrumbs[] = ConvertToBreadcrumbItem($page);
            while($page->post_parent>0)
            {
                $page = get_post($page->post_parent);
                $breadcrumbs[] = ConvertToBreadcrumbItem(get_post($page->ID));
            }
        }

        if(is_404())
        {
            $breadcrumbs[] = array(
                'title'=>'404 not found',
                'link'=>get_site_url().$_SERVER[REQUEST_URI]
            );
        }

        if(is_single())
        {
            $cat = get_the_category(get_the_ID());
            $breadcrumbs[] = ConvertToBreadcrumbItem(get_post(get_the_ID()));
            if(count($cat)>0)
            {
                $cat = $cat[0]; // nned only first category
                $breadcrumbs[] = ConvertToBreadcrumbItem($cat);
                while($cat->parent>0)
                {
                    $cat = get_the_category($cat->parent);
                    $cat = $cat[0];
                    $breadcrumbs[] = ConvertToBreadcrumbItem($cat);
                }
            }


            $breadcrumbs[] = array(
                'title'=>'Blog',
                'link'=>get_site_url().'/blog/'
            );
        }

        if(is_category())
        {
            $cat = get_the_category(get_the_ID()); // nned only first category
            $cat = $cat[0]; // nned only first category
            $breadcrumbs[] = ConvertToBreadcrumbItem($cat);
            while($cat->parent>0)
            {
                $cat = get_the_category($cat->parent);
                $cat = $cat[0];
                $breadcrumbs[] = ConvertToBreadcrumbItem($cat);

            }
            $breadcrumbs[] = array(
                'title'=>'Blog',
                'link'=>get_site_url().'/blog/'
            );
        }

        if(function_exists('is_product_category')&&is_product_category())
        {
            //$current_page_id = get_the_terms( get_the_ID(), 'product_cat' );
            //$current_page_id = $current_page_id[key($current_page_id)]->term_id;

            $cat = get_the_terms( get_the_ID(), 'product_cat' );
            $cat = $cat[key($cat)]; // nned only first category
            //$cat = get_the_category(get_the_ID()); // nned only first category
            $breadcrumbs[] = ConvertToBreadcrumbItem($cat);
            while($cat->parent>0)
            {
                $cat = get_the_category($cat->parent);
                $cat = $cat[0];
                $breadcrumbs[] = ConvertToBreadcrumbItem($cat);

            }
            $breadcrumbs[] = array(
                'title'=>'Products',
                'link'=>get_site_url().'/products/'
            );
        }

        if(is_home())
        {
            $breadcrumbs[] = array(
                'title'=>'Blog',
                'link'=>get_site_url().'/blog/'
            );
        }
    }
    $bc_html = '<div class="bc-item" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
                    <a href="' . home_url() . '" itemprop="url" class="home">
                        <span><i class="fa fa-home"></i></span>
                        <span itemprop="title" style="display:none">Home</span>
                    </a><div class="bc-separator">>></div> 
                </div>';

    //echo '<pre>';
    //var_dump($breadcrumbs);
    //var_dump(is_page());
    //var_dump(is_single());
    //var_dump(is_category());
    //var_dump(is_home());
    //var_dump(is_404());
    //echo '</pre>';

    if ( count( $breadcrumbs ) > 0 )
    {
        for ( $i = count( $breadcrumbs ) - 1; $i > 0; $i-- )
        {
            $bc_html .= '<div  class="bc-item" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
                            <a href="' . $breadcrumbs[$i]['link'] . '" itemprop="url">
                                <span itemprop="title">' . $breadcrumbs[ $i ]['title']  . '</span>
                            </a><div class="bc-separator">>></div>
                        </div>';
        }
        $bc_html .= '<div  class="bc-item" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
                        <a href="' . $breadcrumbs[0]['link'] . '" itemprop="url">
                            <span class="breadcrumb_last" itemprop="title">' . $breadcrumbs[0]['title']  . '</span>
                        </a>
                    </div>';
    }
    if ( !$pid ) {
	    return $bc_html;
    }
    else if ( isset( $pid ) ) {
    	return $breadcrumbs;
    }
}