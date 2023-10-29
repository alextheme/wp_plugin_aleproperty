<?php

// архівний файл для постайпа

get_header(); ?>

<?php $aleProperty_Template->get_template_part('partials/filter'); ?>

<div class="wrapper archive_property">
    <?php

    // Вивід контенту
    if(!empty($_POST['submit'])){
        
        $args = array(
            'post_type'=>'property',
            'posts_per_page' => -1,
            'meta_query' => array('relation'=>'AND'),
            'tax_query' => array('relation'=>'AND'),
        );

        if(isset($_POST['aleproperty_type']) && $_POST['aleproperty_type'] !=''){
            array_push($args['meta_query'],array(
                'key' => 'aleproperty_type',
                'value' => esc_attr($_POST['aleproperty_type']),
            ));
        }

        if(isset($_POST['aleproperty_price']) && $_POST['aleproperty_price'] !=''){
            array_push($args['meta_query'],array(
                'key' => 'aleproperty_price',
                'value' => esc_attr($_POST['aleproperty_price']),
                'type' => 'numeric',
                'compare' => '<=',
            ));
        }

        if(isset($_POST['aleproperty_agent']) && $_POST['aleproperty_agent'] !=''){
            array_push($args['meta_query'],array(
                'key' => 'aleproperty_agent',
                'value' => esc_attr($_POST['aleproperty_agent']),
            ));
        }

        if(isset($_POST['aleproperty_property-type']) && $_POST['aleproperty_property-type'] != ''){
            array_push($args['tax_query'],array(
                'taxonomy' => 'property-type',
                'terms' => $_POST['aleproperty_property-type'],
            ));
        }

        if(isset($_POST['aleproperty_location']) && $_POST['aleproperty_location'] != ''){
            array_push($args['tax_query'],array(
                'taxonomy' => 'location',
                'terms' => $_POST['aleproperty_location'],
            ));
        }

        $properties = new WP_Query($args);

        if ( $properties->have_posts() ) {

            // Load posts loop.
            while ( $properties->have_posts() ) {
                $properties->the_post(); 
            
                $aleProperty_Template->get_template_part('partials/content');
            
            }
        } else {
            echo '<p>'.esc_html__('No Properties','aleproperty').'</p>';
        }

    } else {

        if ( have_posts() ) {

            // Load posts loop.
            while ( have_posts() ) {
                the_post(); 
            
                $aleProperty_Template->get_template_part('partials/content');
            
            }
        
        //Pagination
        posts_nav_link();

        
        } else {
            echo '<p>'.esc_html__('No Properties','aleproperty').'</p>';
        }
    }
    ?>
</div>

<?php
get_footer();