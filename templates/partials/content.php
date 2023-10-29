<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php if(get_the_post_thumbnail(get_the_ID(),'large')) {
        echo get_the_post_thumbnail(get_the_ID(),'large');
    } ?>
    <h2><?php the_title(); ?></h2>
    <div class="description"><?php the_excerpt(); ?></div>
    <div class="property_info">
        <?php
        $locations = get_the_terms(get_the_ID(),'location');

        if(!empty($locations)){
             ?>
            <span class="location"><?php esc_html_e('Location:','aleproperty'); 
        
            foreach($locations as $location){
                echo " ".$location->name;
            } ?>
            </span>
        <?php } ?> 
        <span class="type"><?php esc_html_e('Type:','aleproperty'); 
        $types = get_the_terms(get_the_ID(),'property-type');

        foreach($types as $type){
            echo " ".esc_html($type->name);
        }
        ?> </span>
        <span class="price"><?php esc_html_e('Price:','aleproperty'); echo ' '.esc_html(get_post_meta(get_the_ID(),'aleproperty_price', true));?> </span>
        <span class="offer"><?php esc_html_e('Offer:','aleproperty'); echo ' '.esc_html(get_post_meta(get_the_ID(),'aleproperty_type', true));?> </span>
        <span class="agent"><?php esc_html_e('Agent:','aleproperty'); 
        
        $agent_id = get_post_meta(get_the_ID(),'aleproperty_agent', true);
        $agent = get_post($agent_id);

        echo " ".esc_html($agent->post_title);
        ?> </span>
    </div>
    <a href="<?php the_permalink(); ?>">Open This Property</a><br>

    <?php if(is_user_logged_in()){ 
        $property_id = get_the_ID();
        $user_id = get_current_user_id();
        $wishlist = new alePropoerty_Wishlist();
        
        if($wishlist->aleproperty_in_wishlist($user_id, $property_id)){ 
            if(is_page_template('tpl/template-wishlist.php')){?>
                <a href="<?php echo admin_url('admin-ajax.php'); ?>" class="aleproperty_remove_property" data-property-id="<?php echo $property_id; ?>" data-user-id="<?php echo  $user_id; ?>">Remove From Wishlist</a>
            <?php } else {
                esc_html_e('Already Added');
            }
           
        } else { ?>
            <form action="<?php echo admin_url('admin-ajax.php') ?>" method="post" id="aleproperty_add_to_wishlist_form_<?php echo $property_id; ?>">
                <input type="hidden" name="ale_user_id" value="<?php echo esc_attr($user_id); ?>" >
                <input type="hidden" name="ale_property_id" value="<?php echo esc_attr($property_id); ?>" >
                <input type="hidden" name="action" value="aleproperty_add_wishlist" >
            </form>
            <a href="#" data-property-id="<?php echo $property_id; ?>" class="aleproperty_add_to_wishlist">Add to Wishlist</a>
            <span class="succesfull_added" style="display:none;">Added to Wishlist</span>
        <?php } ?>
        
    <?php } ?>
    
</article>