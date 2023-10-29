<?php

// Наш класс для завантаження шаблонів

class aleProperty_Template_Loader extends Gamajo_Template_Loader {

    protected $filter_prefix = 'aleproperty'; // префікс це наш слаг

    protected $theme_template_directory = 'aleproperty'; // як правило таж назва. Це назва теки яка буде в шаблоні теми переписувати шаблони плагіна

    protected $plugin_directory = ALEPROPERTY_PATH; // шлях до плагіна

    protected $plugin_template_director = 'templates'; // каталог в плагіні де зберігаються шаблони

    public $templates;

    // ...
    public function register(){

        // Підключення наших шаблонів через фільтр
        add_filter('template_include', [$this,'aleproperties_templates']);

        $this->templates = array(
            'tpl/template-addproperty.php' => 'Add Property',
            'tpl/template-listproperty.php' => 'List Personal Properties',
            'tpl/template-wishlist.php' => 'Template Wishlist',
        );
        add_filter('theme_page_templates',[$this,'custom_template']);
        add_filter('template_include', [$this,'load_template']);
    }

    public function load_template($template){

        global $post;

        $template_name = get_post_meta($post->ID,'_wp_page_template',true);

        
        if($template_name && $this->templates[$template_name] ) {
            $file = ALEPROPERTY_PATH . $template_name;
            if(file_exists($file)){
                return $file;
            }
        }
        

        return $template;
    }

    public function custom_template($templates){

        $templates = array_merge($templates,  $this->templates);

        return $templates;

    }

    // ...
    public function aleproperties_templates($template){

        // перевірка на пост тайп "property"
        if(is_post_type_archive('property')){
            // пошук цих файлів в корні теми, на території темплейтів
            $theme_files = ['archive-property.php','aleproperty/archive-property.php'];
            $exist = locate_template($theme_files, false);
            if($exist != ''){
                return $exist;
            } else {
                return plugin_dir_path(__DIR__).'templates/archive-property.php';
            }
        }
        // перевірка на пост тайп "agent"
        elseif(is_post_type_archive('agent')){
            // пошук цих файлів в корні теми, на території темплейтів
            $theme_files = ['archive-agent.php','aleproperty/archive-agent.php'];
            $exist = locate_template($theme_files, false);
            if($exist != ''){
                return $exist;
            } else {
                return plugin_dir_path(__DIR__).'templates/archive-agent.php';
            }
        }
        // перевірка на пост тайп окремої сторінки "property"
        elseif(is_singular('property')){
            // пошук цих файлів в корні теми, на території темплейтів
            $theme_files = ['single-property.php','aleproperty/single-property.php'];
            $exist = locate_template($theme_files, false);
            if($exist != ''){
                return $exist;
            } else {
                return plugin_dir_path(__DIR__).'templates/single-property.php';
            }
        }
        // перевірка на пост тайп окремої сторінки "agent"
        elseif(is_singular('agent')){
            // пошук цих файлів в корні теми, на території темплейтів
            $theme_files = ['single-agent.php','aleproperty/single-agent.php'];
            $exist = locate_template($theme_files, false);
            if($exist != ''){
                return $exist;
            } else {
                return plugin_dir_path(__DIR__).'templates/single-agent.php';
            }
        }

        return $template;
    }
}

// ініціалізація методів класу
$aleProperty_Template = new aleProperty_Template_Loader();
$aleProperty_Template->register();