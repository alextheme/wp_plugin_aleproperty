<?php
class aleProperty_Bookingform {

    public function __construct(){

        add_action('wp_enqueue_scripts',[$this,'enqueue']);
        add_action('init',[$this,'aleproperty_booking_shortcode']);

        add_action('wp_ajax_booking_form',[$this,'booking_form']);
        add_action('wp_ajax_nopriv_booking_form',[$this,'booking_form']);
    }

    public function enqueue(){
        wp_enqueue_script('aleproperty_bookingform', plugins_url('aleproperty/assets/js/front/bookingform.js'), array('jquery'),'1.0',true);
   
        wp_localize_script('aleproperty_bookingform','aleproperty_bookingform_var',array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('_wpnonce'),
            'title' => esc_html__('Booking Form','aleproperty'),
        ));
    }

    public function aleproperty_booking_shortcode(){
        add_shortcode('aleproperty_booking',[$this,'booking_form_html']);
    }

    public function booking_form_html($atts, $content){

        extract(shortcode_atts(array(
            'location' => '',
            'offer' => '',
            'price' => '',
            'agent' => '',
            'type' => '',
        ),$atts));

        echo '
        <div id="aleproperty_result"></div>
        <form method="post">
            <p>
                <input type="text" name="name" id="aleproperty_name"/>
            </p>
            <p>
                <input type="text" name="email" id="aleproperty_email" />
            </p>
            <p>
                <input type="text" name="phone" id="aleproperty_phone" />
            </p>';

            if($price != ''){
                echo '<p>
                    <input type="hidden" name="price" id="aleproperty_price" value="'.esc_html($price).'" />
                </p>';
            }

            if($location != ''){
                echo '<p>
                    <input type="hidden" name="location" id="aleproperty_location" value="'.esc_html($location).'" />
                </p>';
            }
            if($agent != ''){
                echo '<p>
                    <input type="hidden" name="agent" id="aleproperty_agent" value="'.esc_html($agent).'" />
                </p>';
            }


            echo '<p>
                <input type="submit" name="submit" id="aleproperty_booking_submit" />
            </p>
            </form>';
    }

    function booking_form(){

        check_ajax_referer('_wpnonce', 'nonce');

        if(!empty($_POST)){

            if(isset($_POST['name'])){
                $name = sanitize_text_field($_POST['name']);
            }
            if(isset($_POST['email'])){
                $email = sanitize_text_field($_POST['email']);
            }
            if(isset($_POST['phone'])){
                $phone = sanitize_text_field($_POST['phone']);
            }
            if(isset($_POST['price'])){
                $price = sanitize_text_field($_POST['price']);
            }
            if(isset($_POST['location'])){
                $location = sanitize_text_field($_POST['location']);
            }
            if(isset($_POST['agent'])){
                $agent = sanitize_text_field($_POST['agent']);
            }

            //email Admin
            $data_message = '';

            $data_message .= 'Name: '.esc_html($name).'<br>';
            $data_message .= 'Email: '.esc_html($email).'<br>';
            $data_message .= 'Phone: '.esc_html($phone).'<br>';
            $data_message .= 'Price: '.esc_html($price).'<br>';
            $data_message .= 'Location: '.esc_html($location).'<br>';
            $data_message .= 'Agent: '.esc_html($agent).'<br>';

            //echo $data_message;

            $result_admin = wp_mail(get_option('admin_email'), 'New Reservation', $data_message);
            
            if($result_admin){
                echo "All Right";
            }

            //email client
            $message = esc_html__('Thank you for your reservation. We will contat you soon!');
            wp_mail($email, esc_html__('Booking','aleproperty'), $message);

        } else {
            echo 'smth wrong';
        }

        wp_die();
    }
   
}

$booking_form = new aleProperty_Bookingform();