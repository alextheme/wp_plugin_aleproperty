<?php
class WPBakery_aleProperty_Shortcodes {

    protected $aleProperty_Template;

    function __construct(){

        add_action('init',[$this,'create_shortcode']);
        
        add_shortcode('aleproperty_list',[$this,'render_shortcode']);
    }

    public function create_shortcode(){
        if(function_exists('vc_map')){
            vc_map(array(
                'name' => 'List Properties',
                'base' => 'aleproperty_list',
                'description' => 'First Shortcode',
                'category' => 'aleProperty',
                'params' => array(
                    array(
                        'type' => 'textfield',
                        'heading' => 'Title',
                        'param_name' => 'title',
                        'value'=> '',
                        'description' => 'Insert the title',
                    ),
                    array(
                        'type' => 'textfield',
                        'heading' => 'Count',
                        'param_name' => 'count',
                        'value'=> '',
                        'description' => 'Insert the count',
                    )
                ),
            ));


            vc_map(array(
                'name' => 'Filter',
                'base' => 'aleproperty_filter',
                'description' => 'Filter Shortcode',
                'category' => 'aleProperty',
                'params' => array(
                    
                    array(
                        'type' => 'textfield',
                        'heading' => 'Location',
                        'param_name' => 'location',
                        'description' => 'Paste 1 to show or 0 to hide',
                    ),
                    array(
                        'type' => 'textfield',
                        'heading' => 'Type',
                        'param_name' => 'type',
                        'description' => 'Paste 1 to show or 0 to hide',
                    ),
                    array(
                        'type' => 'textfield',
                        'heading' => 'Agent',
                        'param_name' => 'agent',
                        'description' => 'Paste 1 to show or 0 to hide',
                    )
                ),
            ));
        }
        
    }

    public function render_shortcode($atts,$content,$tag){
        $atts = (shortcode_atts(array(
            'title' => '',
            'count' => '3'
        ),
        $atts));

        $this->aleProperty_Template = new aleProperty_Template_Loader();

        $args = array(
            'post_type' => 'property',
            'posts_per_page' => $atts['count'],
        );

        $properties = new WP_Query($args);


        echo '<div class="wrapper archive_property">';

        if ( $properties->have_posts() ) {

            // Load posts loop.
            while ( $properties->have_posts() ) {
                $properties->the_post(); 

                $this->aleProperty_Template->get_template_part('partials/content');
            
            }
        }

        echo '</div>';

        //return $html;
    }
}

new WPBakery_aleProperty_Shortcodes();