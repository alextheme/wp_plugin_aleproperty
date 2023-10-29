<?php
/*
Plugin Name: aleProperty
Plugin URI: https://geniuscourses.com/
Description: First Plugin
Version: 1.0
Author: CRIK0VA
Author URI: https://geniuscourses.com/
Licence: GPLv2 or later
Text Domain: aleproperty
Domain Path: /lang
*/

// Структура файлов
// aleproperty/
//   admin/
//   assets/
//	   css/
//       admin/
//       front/
//     img/
//     js/
//       admin/
//       front/
//    inc/
//    lang/
//    templates
//    aleproperty.php
//    uninstall.php


// Доступ к файлу только если ВП обращается
if(!defined('ABSPATH')){
    die;
}

define('ALEPROPERTY_PATH',plugin_dir_path(__FILE__));

if(!class_exists('alePropertyCpt')){
    require ALEPROPERTY_PATH . 'inc/class-alepropertycpt.php';
}
if(!class_exists('Gamajo_Template_Loader')){
    require ALEPROPERTY_PATH . 'inc/class-gamajo-template-loader.php';
}
require ALEPROPERTY_PATH . 'inc/class-aleproperty-template-loader.php';
require ALEPROPERTY_PATH . 'inc/class-aleproperty-shortcodes.php';
require ALEPROPERTY_PATH . 'inc/class-aleproperty-filter-widget.php';
require ALEPROPERTY_PATH . 'inc/class-aleproperty-elementor.php';
require ALEPROPERTY_PATH . 'inc/class-aleproperty-wpbakery.php';
require ALEPROPERTY_PATH . 'inc/class-aleproperty-bookingform.php';
require ALEPROPERTY_PATH . 'inc/class-aleproperty-wishlist.php';

class aleProperty{

    function register(){
        add_action('admin_enqueue_scripts',[$this,'enqueue_admin']);
        add_action('wp_enqueue_scripts', [$this,'enqueue_front']);

        add_action('plugins_loaded',[$this,'load_text_domain']);

        add_action('widgets_init',[$this,'register_widget']);

        add_action('admin_menu',[$this,'add_menu_item']);
        add_filter('plugin_action_links_'.plugin_basename(__FILE__),[$this,'add_plugin_setting_link']);
    
        add_action('admin_init',[$this, 'settings_init']);
    }

    public function settings_init(){

        register_setting('aleproperty_settings','aleproperty_settings_options');

        add_settings_section('aleproperty_settings_section', esc_html__('Settings','aleproperty'), [$this,'aleproperty_settings_section_html'],'aleproperty_settings');

        add_settings_field('filter_title', esc_html__('Title for Filter','aleproperty'), [$this,'filter_title_html'],'aleproperty_settings','aleproperty_settings_section');
        add_settings_field('archive_title', esc_html__('Title for Archive Page','aleproperty'), [$this,'archive_title_html'],'aleproperty_settings','aleproperty_settings_section');
    }

    public function aleproperty_settings_section_html(){
        esc_html_e('Settings for aleProperty Plugin');
    }

    public function filter_title_html(){

        $options = get_option('aleproperty_settings_options');

        ?>
        <input type="text" name="aleproperty_settings_options[filter_title]" value="<?php echo isset($options['filter_title']) ? $options['filter_title'] : "";  ?>" />
        <?php
    }

    public function archive_title_html(){

        $options = get_option('aleproperty_settings_options');

        ?>
        <input type="text" name="aleproperty_settings_options[archive_title]" value="<?php echo isset($options['archive_title']) ? $options['archive_title'] : "";  ?>" />
        <?php
    }

    public function add_plugin_setting_link($link){

        $aleproperty_link = '<a href="admin.php?page=aleproperty_settings">'.esc_html__('Settings Page','aleproperty').'</a>';
        array_push($link,$aleproperty_link);

        return $link;
    }

    public function add_menu_item(){
        add_menu_page(
            esc_html__('aleProperty Settings Page','aleproperty'),
            esc_html__('aleProperty','aleproperty'),
            'manage_options',
            'aleproperty_settings',
            [$this,'main_admin_page'],
            'dashicons-admin-plugins',
            100,
        );
    }

    public function main_admin_page(){
        require_once ALEPROPERTY_PATH .'admin/welcome.php';
    }

    public function register_widget(){
        register_widget('aleproperty_filter_widget');
    }

    public function get_terms_hierarchical($tax_name,$current_term){

        $taxonomy_terms = get_terms($tax_name,['hide_empty'=>'false','parent'=>0]);

        $html = '';
        if(!empty($taxonomy_terms)){
            foreach($taxonomy_terms as $term){
                if($current_term == $term->term_id){
                    $html .= '<option value="'.$term->term_id.'" selected >'.$term->name.'</option>';
                } else {
                    $html .= '<option value="'.$term->term_id.'" >'.$term->name.'</option>';
                }

                $child_terms = get_terms($tax_name, ['hide_empty'=>false, 'parent'=>$term->term_id]);
                
                if(!empty($child_terms)){
                    foreach($child_terms as $child){
                        if($current_term == $child->term_id){
                            $html .= '<option value="'.$child->term_id.'" selected > - '.$child->name.'</option>';
                        } else {
                            $html .= '<option value="'.$child->term_id.'" > - '.$child->name.'</option>';
                        }
                    }
                }
            
            }
        }
        return $html;
    }

    function load_text_domain(){
        load_plugin_textdomain('aleproperty', false, dirname(plugin_basename(__FILE__)).'/lang');
    }

    public function enqueue_admin(){
        wp_enqueue_style('aleProperty_style_admin', plugins_url('/assets/css/admin/style.css',__FILE__));
        wp_enqueue_script('aleProperty_script_admin', plugins_url('/assets/js/admin/scripts.js', __FILE__),array('jquery'),'1.0',true);
    }

    public function enqueue_front(){
        wp_enqueue_style('aleProperty_style', plugins_url('/assets/css/front/style.css',__FILE__));
        wp_enqueue_script('aleProperty_script', plugins_url('/assets/js/front/scripts.js', __FILE__),array('jquery'),'1.0',true);
        wp_enqueue_script('jquery-form');
    }

    // Обробка подій при активаціі та деактивації плагіна
    static function activation(){
        flush_rewrite_rules();
    }
    static function deactivation(){
        flush_rewrite_rules();
    }
}

// Чи є класс
if(class_exists('aleProperty')){
    $aleProperty = new aleProperty();
    $aleProperty->register();
}

// Хуки Активації/Деактивації
register_activation_hook(__FILE__, array($aleProperty,'activation') );
register_deactivation_hook(__FILE__, array($aleProperty,'deactivation') );
