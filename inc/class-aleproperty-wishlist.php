<?php 

class alePropoerty_Wishlist {

    function register(){
        add_action('wp_ajax_aleproperty_add_wishlist',[$this,'aleproperty_add_wishlist']);
        add_action('wp_ajax_aleproperty_remove_wishlist',[$this,'aleproperty_remove_wishlist']);
    }

    public function aleproperty_add_wishlist(){

        //ale_user_id
        //ale_property_id

        if(isset($_POST['ale_property_id']) && isset($_POST['ale_user_id'])){
            $property_id = intval($_POST['ale_property_id']);
            $user_id = intval($_POST['ale_user_id']);

            if($property_id > 0 && $user_id > 0) {
                if(add_user_meta($user_id, 'aleproperty_wishlist_properties', $property_id)){
                     esc_html_e('Succeesful ladded to wishlist','aleproperty');
                } else {
                    esc_html_e('Failed','aleproperty');
                }
            }

        }

        wp_die();
    }


    public function aleproperty_remove_wishlist(){

        if(isset($_POST['ale_property_id']) && isset($_POST['ale_user_id'])){
            $property_id = intval($_POST['ale_property_id']);
            $user_id = intval($_POST['ale_user_id']);

            if($property_id > 0 && $user_id > 0) {
                if(delete_user_meta($user_id, 'aleproperty_wishlist_properties', $property_id)){
                     echo 3; //Success
                } else {
                    echo 2; //Failed
                }
            } else {
                echo 1; //Bad
            }

        } else {
            echo 1; //Bad
        }

        wp_die();
    }

    public function aleproperty_in_wishlist($user_id, $property_id){
        global $wpdb;
        $result = $wpdb->get_results("SELECT * FROM $wpdb->usermeta WHERE meta_key='aleproperty_wishlist_properties' AND meta_value=".$property_id." AND user_id=".$user_id);
        if(isset($result[0]->meta_value) && $result[0]->meta_value == $property_id){
            return true;
        } else {
            return false;
        }
    }
}
$alePropoerty_Wishlist = new alePropoerty_Wishlist();
$alePropoerty_Wishlist->register();