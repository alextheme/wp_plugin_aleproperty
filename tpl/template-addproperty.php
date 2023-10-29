<?php
/**
 * Template Name: Add Property
 */

 function aleproperty_image_validation($file_name){
     $valid_extensions = array('jpg', 'jpeg', 'gif', 'png');
     $exploded_array = explode('.', $file_name);
     if(!empty($exploded_array) && is_array($exploded_array)){
         $ext = array_pop($exploded_array);
         return in_array($ext, $valid_extensions);
     } else {
         return false;
     }
 }

 function aleproperty_insert_attachment($file_handler,$post_id,$setthumb=false){

    if($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();

    require_once(ABSPATH . "wp-admin" . "/includes/image.php");
    require_once(ABSPATH . "wp-admin" . "/includes/file.php");
    require_once(ABSPATH . "wp-admin" . "/includes/media.php");

    $attach_id = media_handle_upload($file_handler, $post_id);

    if($setthumb){
        update_post_meta($post_id, '_thumbnail_id',$attach_id);
    }

    return $attach_id;
 }

$success = '';

if(isset($_POST['action']) && is_user_logged_in()){
    if(wp_verify_nonce($_POST['property_nonce'],'submit_property')){

        $aleproperty_item = array();

        $aleproperty_item['post_title'] = sanitize_text_field($_POST['property_title']);
        $aleproperty_item['post_type'] = 'property';
        $aleproperty_item['post_content'] = sanitize_textarea_field($_POST['property_desc']);
        
        global $current_user; wp_get_current_user();
        $aleproperty_item['post_author'] = $current_user->ID;

        $aleproperty_action = $_POST['action'];

        if($aleproperty_action == 'aleproperty_add_property'){
            $aleproperty_item['post_status'] = 'pending';
            $aleproperty_item_id = wp_insert_post($aleproperty_item);

            if($aleproperty_item_id > 0){
                do_action('wp_insert_post','wp_insert_post');
                $success = 'Property Succesfull published';
            }
        } elseif($aleproperty_action == 'aleproperty_edit_property'){
            $aleproperty_item['post_status'] = 'pending';
            $aleproperty_item['ID'] = intval($_POST['property_id']);
            $aleproperty_item_id = wp_update_post($aleproperty_item);

            $success = 'Property Succesfull updated';
        }

        //metabox, taxonomy, featured image

        if($aleproperty_item_id > 0) {

            //Metabox
            if(isset($_POST['property_offer']) && $_POST['property_offer'] != ''){
                update_post_meta($aleproperty_item_id, 'aleproperty_type', trim($_POST['property_offer']));
            }
            if(isset($_POST['property_price'])){
                update_post_meta($aleproperty_item_id, 'aleproperty_price', trim($_POST['property_price']));
            }
            if(isset($_POST['property_period'])){
                update_post_meta($aleproperty_item_id, 'aleproperty_period', trim($_POST['property_period']));
            }
            if(isset($_POST['property_agent']) && $_POST['property_agent'] !='disable'){
                update_post_meta($aleproperty_item_id, 'aleproperty_agent', trim($_POST['property_agent']));
            } 

            //taxonomy
            if(isset($_POST['property_location'])){
                wp_set_object_terms($aleproperty_item_id, intval($_POST['property_location']), 'location');
            }
            if(isset($_POST['property_type'])){
                wp_set_object_terms($aleproperty_item_id, intval($_POST['property_type']), 'property-type');
            }

            //featured image
            if($_FILES){
                foreach($_FILES as $submitted_file => $file_array){
                    if(aleproperty_image_validation($_FILES[$submitted_file]['name'])){
                        $size = intval($_FILES[$submitted_file]['size']);

                        if($size > 0){
                            aleproperty_insert_attachment($submitted_file, $aleproperty_item_id, true);
                        }

                    }
                }
            }
        }
    }
}

get_header(); ?>

    <div class="wrapper">
    <?php 
    
    if ( have_posts() ) {

        // Load posts loop.
        while ( have_posts() ) {
            the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            
                <div class="description"><?php the_content(); ?></div>
            </article>

            <?php if(is_user_logged_in()){ 
                
                if(!empty($success)){
                    echo esc_html($success);
                } else {

                    if(isset($_GET['edit']) && !empty($_GET['edit'])){

                        $property_id_edit = intval(trim($_GET['edit']));

                        $ale_edit_property = get_post($property_id_edit);

                        if(!empty($ale_edit_property) && $ale_edit_property->post_type == 'property'){

                            global $current_user;
                            wp_get_current_user();

                            if($ale_edit_property->post_author == $current_user->ID){
                                $ale_metadata = get_post_custom($ale_edit_property->ID);

                    
                                ?>
                                <h2>Edit Property</h2>
                                <div class="add_form">
                                    <form method="post" id="add_property" enctype="multipart/form-data" >
                                        <p>
                                            <label for="property_title">Title</label>
                                            <input type="text" name="property_title" id="property_title" placeholder="Add the Title" value="<?php echo $ale_edit_property->post_title; ?>" required tabindex="1" />
                                        </p>
                                        <p>
                                            <label for="property_desc">Description</label>
                                            <textarea name="property_desc" id="property_desc" placeholder="Add the Description" required tabindex="2"><?php echo $ale_edit_property->post_content; ?></textarea>
                                        </p>
                                        <p>
                                            <label for="property_image">Featured Image</label>
                                            <input type="file" name="property_image" id="property_image" tabindex="3" />
                                        </p>
                                        <p>
                                            <label for="property_location">Select Location</label>
                                            <select id="property_location" name="property_location" tabindex="4">
                                                <?php 
                                                    $current_term_id = 0;
                                                    $tax_terms = get_the_terms($ale_edit_property->ID,'location');

                                                    if(!empty($tax_terms)){
                                                        foreach($tax_terms as $tax_term){
                                                            $current_term_id = $tax_term->term_id;
                                                            break;
                                                        }
                                                    }
                                                    $current_term_id = intval($current_term_id);

                                                    $locations = get_terms(array('location'),array('hide_empty'=>false));

                                                    if(!empty($locations)){
                                                        foreach($locations as $location){
                                                            $selected = '';
                                                            if($current_term_id == $location->term_id) {$selected = 'selected';}
                                                            echo '<option '.$selected.' value='.$location->term_id.'>'.$location->name.'</option>';
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </p>
                                        <p>
                                            <label for="property_type">Select Type</label>
                                            <select id="property_type" name="property_type" tabindex="5">
                                                <?php 

                                                $current_term_id = 0;
                                                $tax_terms = get_the_terms($ale_edit_property->ID,'property-type');

                                                if(!empty($tax_terms)){
                                                    foreach($tax_terms as $tax_term){
                                                        $current_term_id = $tax_term->term_id;
                                                        break;
                                                    }
                                                }
                                                $current_term_id = intval($current_term_id);


                                                    $types = get_terms(array('property-type'),array('hide_empty'=>false));

                                                    if(!empty($types)){
                                                        foreach($types as $type){
                                                            $selected = '';
                                                            if($current_term_id == $type->term_id) {$selected = 'selected';}
                                        
                                                            echo '<option '.$selected.' value='.$type->term_id.'>'.$type->name.'</option>';
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </p>
                                        <p>
                                            <label for="property_offer">Select Offer Type</label>
                                            <select id="property_offer" name="property_offer" tabindex="6">
                                                <option selected value="">Not Selected</option>
                                                <option value="sale" <?php if(get_post_meta($ale_edit_property->ID,'aleproperty_type',true)=='sale'){echo 'selected'; } ?>>For Sale</option>
                                                <option value="sold" <?php if(get_post_meta($ale_edit_property->ID,'aleproperty_type',true)=='sold'){echo 'selected'; } ?>>For Sold</option>
                                                <option value="rent" <?php if(get_post_meta($ale_edit_property->ID,'aleproperty_type',true)=='rent'){echo 'selected'; } ?>>For Rent</option>
                                            </select>
                                        </p>
                                        <p>
                                            <label for="property_price">Price</label>
                                            <input type="text" name="property_price" id="property_price" tabindex="7" value="<?php echo get_post_meta($ale_edit_property->ID,'aleproperty_price',true); ?>" />
                                        </p>
                                        <p>
                                            <label for="property_period">Period</label>
                                            <input type="text" name="property_period" id="property_period" tabindex="8" value="<?php echo get_post_meta($ale_edit_property->ID,'aleproperty_period',true); ?>" />
                                        </p>
                                        <p>
                                            <?php global $current_user; wp_get_current_user(); ?>
                                            <label for="property_agent">Agent</label>
                                            <select id="property_agent" name="property_agent" tabindex="9">
                                                <option selected value="disable">Disable Agents, Use User</option>
                                                <?php 
                                                    $agents = get_posts(array('post_type'=>'agent','numberposts'=>-1));

                                                    if(!empty($agents)){
                                                        $selected = '';
                                                        $current_agent = get_post_meta($ale_edit_property->ID,'aleproperty_agent',true);
                                                        foreach($agents as $agent){
                                                            if($current_agent == $agent->ID){
                                                                $selected = 'selected';
                                                            }
                                                            echo '<option '.$selected.' value='.$agent->ID.'>'.$agent->post_title.'</option>';
                                                        }
                                                    }


                                                ?>
                                            </select>
                                            
                                        </p>
                                        <p>
                                            <?php wp_nonce_field('submit_property','property_nonce'); ?>
                                            <input type="submit" name="submit" tabindex="10" value="Edit Property" />
                                            <input type="hidden" name="action" value="aleproperty_edit_property" />
                                            <input type="hidden" name="property_id" value="<?php echo esc_attr($ale_edit_property->ID); ?>" />
                                        </p>
                                    </form>
                                </div>

                                <?php

                            }
                        }

                    } else {
                        ?>
                        <h2>Add Property</h2>
                        <div class="add_form">
                            <form method="post" id="add_property" enctype="multipart/form-data" >
                                <p>
                                    <label for="property_title">Title</label>
                                    <input type="text" name="property_title" id="property_title" placeholder="Add the Title" value="" required tabindex="1" />
                                </p>
                                <p>
                                    <label for="property_desc">Description</label>
                                    <textarea name="property_desc" id="property_desc" placeholder="Add the Description" required tabindex="2"></textarea>
                                </p>
                                <p>
                                    <label for="property_image">Featured Image</label>
                                    <input type="file" name="property_image" id="property_image" tabindex="3" />
                                </p>
                                <p>
                                    <label for="property_location">Select Location</label>
                                    <select id="property_location" name="property_location" tabindex="4">
                                        <?php 
                                            $locations = get_terms(array('location'),array('hide_empty'=>false));

                                            if(!empty($locations)){
                                                foreach($locations as $location){
                                                    echo '<option value='.$location->term_id.'>'.$location->name.'</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </p>
                                <p>
                                    <label for="property_type">Select Type</label>
                                    <select id="property_type" name="property_type" tabindex="5">
                                        <?php 
                                            $types = get_terms(array('property-type'),array('hide_empty'=>false));

                                            if(!empty($types)){
                                                foreach($types as $type){
                                                    echo '<option value='.$type->term_id.'>'.$type->name.'</option>';
                                                }
                                            }
                                        ?>
                                    </select>
                                </p>
                                <p>
                                    <label for="property_offer">Select Offer Type</label>
                                    <select id="property_offer" name="property_offer" tabindex="6">
                                        <option selected value="">Not Selected</option>
                                        <option value="sale">For Sale</option>
                                        <option value="sold">For Sold</option>
                                        <option value="rent">For Rent</option>
                                    </select>
                                </p>
                                <p>
                                    <label for="property_price">Price</label>
                                    <input type="text" name="property_price" id="property_price" tabindex="7" value="" />
                                </p>
                                <p>
                                    <label for="property_period">Period</label>
                                    <input type="text" name="property_period" id="property_period" tabindex="8" value="" />
                                </p>
                                <p>
                                    <?php global $current_user; wp_get_current_user(); ?>
                                    <label for="property_agent">Agent</label>
                                    <select id="property_agent" name="property_agent" tabindex="9">
                                        <option selected value="disable">Disable Agents, Use User</option>
                                        <?php 
                                            $agents = get_posts(array('post_type'=>'agent','numberposts'=>-1));

                                            if(!empty($agents)){
                                                foreach($agents as $agent){
                                                    echo '<option value='.$agent->ID.'>'.$agent->post_title.'</option>';
                                                }
                                            }


                                        ?>
                                    </select>
                                    
                                </p>
                                <p>
                                    <?php wp_nonce_field('submit_property','property_nonce'); ?>
                                    <input type="submit" name="submit" tabindex="10" value="Add New Property" />
                                    <input type="hidden" name="action" value="aleproperty_add_property" />
                                </p>
                            </form>
                        </div>
                <?php } ?>
                <?php } ?>
            <?php } ?>
        <?php }
    
    } 
    ?>
    </div>

<?php
get_footer();