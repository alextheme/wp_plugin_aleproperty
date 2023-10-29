<?php 

/**
 * Template name: Template Wishlist
 */

 get_header();?>

<div class="wrapper archive_property">
    <?php 

    
    if ( have_posts() ) {

        // Load posts loop.
        while ( have_posts() ) {
            the_post(); 

            the_content();
        
            //$aleProperty_Template->get_template_part('partials/content');
        
        }
    }
    ?>

    <?php 
        if(is_user_logged_in()){
            $user_id = get_current_user_id();
            $wislist_items = get_user_meta($user_id,'aleproperty_wishlist_properties');
            if(count($wislist_items) > 0){
                $args = array(
                    'post_type' => 'property',
                    'posts_per_page' => -1,
                    'post__in' => $wislist_items,
                    'orderby' => 'post__in'
                );
                $properties = new WP_Query($args);

                if ( $properties->have_posts() ) {

                    // Load posts loop.
                    while ( $properties->have_posts() ) {
                        $properties->the_post(); 
                    
                        $aleProperty_Template->get_template_part('partials/content');
                    
                    }
                }
            } else {
                esc_html_e('No Properties in WishList');
            }
        }
    ?>
</div>

<?php
 get_footer();