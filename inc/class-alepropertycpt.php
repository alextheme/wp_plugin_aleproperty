<?php
if(!class_exists('alePropertyCpt')){

    class alePropertyCpt{

        // Склали всі хуки сюди
        public function register(){
            // Реєструємо пост тайпи
            add_action('init',[$this,'custom_post_type']);

            // Підключаємо мета бокси
            add_action('add_meta_boxes',[$this,'add_meta_box_property']);
            // Підключення ф-ції збереження данних метабоксів
            add_action('save_post',[$this,'save_matabox'],10,2);

            add_action('manage_property_posts_columns', [$this,'custom_colums_for_property']);
            add_action('manage_property_posts_custom_column', [$this,'custom_property_columns_data'],10,2);
            add_filter('manage_edit-property_sortable_columns', [$this,'custom_property_columns_sort']);
            add_action('pre_get_posts',[$this,'custom_property_order']);
        
        }

        /**
         * Додаємо Мета Бокси для Пост тайпів (ціна, умови, період, номер телефону і т.і)
         * @return void
         */
        public function add_meta_box_property(){
            // Мета бокси для пост тайпа "property"
            add_meta_box(
                'aleproperty_settings',
                'Property Settings',
                [$this, 'metabox_property_html'], // Ф-ція, для будування тіла
                'property',
                'normal',
                'default'
            );
        }

        // Функція для збереження введених даних в БД, з перевірками на безпеку
        public function save_matabox($post_id,$post){

            // Перевірка на нонсенс (дані з прихованого поля wp_nonce_field();
            if(!isset($_POST['_aleproperty']) || !wp_verify_nonce($_POST['_aleproperty'], 'alepropertyfields')){
                return $post_id;
            }

            // Авто зберігання відміняємо
            if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
                return $post_id;
            }

            // Чи ми на необхідному ПостТайпі
            if($post->post_type != 'property'){
                return $post_id;
            }

            // Чи у користувача є дозвіл зберігати пости
            $post_type = get_post_type_object($post->post_type);
            if(!current_user_can($post_type->cap->edit_post,$post_id)){
                return $post_id;
            }


            // Якщо є щось в полі то зберігаємо через санітізацію,
            // якщо пусто - видаляємо

            // Для прайса
            if(is_null($_POST['aleproperty_price'])){
                delete_post_meta($post_id,'aleproperty_price');
            } else {
                update_post_meta($post_id,'aleproperty_price', sanitize_text_field(intval($_POST['aleproperty_price'])));
            }

            // Для періода
            if(is_null($_POST['aleproperty_period'])){
                delete_post_meta($post_id,'aleproperty_period');
            } else {
                update_post_meta($post_id,'aleproperty_period', sanitize_text_field($_POST['aleproperty_period']));
            }

            // Для типу
            if(is_null($_POST['aleproperty_type'])){
                delete_post_meta($post_id,'aleproperty_type');
            } else {
                update_post_meta($post_id,'aleproperty_type', sanitize_text_field($_POST['aleproperty_type']));
            }

            // Для агента
            if(is_null($_POST['aleproperty_agent'])){
                delete_post_meta($post_id,'aleproperty_agent');
            } else {
                update_post_meta($post_id,'aleproperty_agent', sanitize_text_field($_POST['aleproperty_agent']));
            }

            return $post_id;
        }

        // Ф-ція, для будування тіла метабокса
        public function metabox_property_html($post){
            // get_post_meta - достати мета бокси із БД
            $price = get_post_meta($post->ID, 'aleproperty_price', true);
            $period = get_post_meta($post->ID, 'aleproperty_period', true);
            $type = get_post_meta($post->ID, 'aleproperty_type', true);
            $agent_meta = get_post_meta($post->ID, 'aleproperty_agent', true);

            // Передача прихованих полів для перевірки пізніше
            wp_nonce_field('alepropertyfields','_aleproperty');

            echo '
            <p>
                <label for="aleproperty_price">'.esc_html__('Price','aleproperty').'</label>
                <input type="number" id="aleproperty_price" name="aleproperty_price" value="'.esc_attr($price).'">
            </p>

            <p>
                <label for="aleproperty_period">'.esc_html__('Period','aleproperty').'</label>
                <input type="text" id="aleproperty_period" name="aleproperty_period" value="'.esc_attr($period).'">
            </p>

            <p>
                <label for="aleproperty_type">'.esc_html__('Type','aleproperty').'</label>
                <select id="aleproperty_type" name="aleproperty_type">
                    <option value="">Select Type</option>
                    <option value="sale" '.selected('sale',$type,false).'>'.esc_html__('For Sale','aleproperty').'</option>
                    <option value="rent" '.selected('rent',$type,false).'>'.esc_html__('For Rent','aleproperty').'</option>
                    <option value="sold" '.selected('sold',$type,false).'>'.esc_html__('Sold','aleproperty').'</option>
                </select>
            </p>
            ';

            $agents = get_posts(array('post_type'=>'agent','numberposts'=>-1));
            
            if($agents){
                echo '<p>
                <label for="aleproperty_agent">'.esc_html__('Agents','aleproperty').'</label>
                <select id="aleproperty_agent" name="aleproperty_agent">
                    <option value="">'.esc_html__('Select Agent','aleproperty').'</option>';

                foreach($agents as $agent){ ?>
                    <option value="<?php echo esc_html($agent->ID); ?>" <?php if($agent->ID == $agent_meta){echo 'selected'; } ?>><?php echo esc_html($agent->post_title) ?></option>
                <?php }

                echo '</select>
                </p>';
            }
        }

        /**
         * Custom Post Types
         * @return void
         */
        public function custom_post_type(){

            /* Post Types "Property" */
            register_post_type('property',
            array(
                'public' => true, // щоб був доступний у форонті
                'has_archive' => true, // це архівний пост
                'rewrite' => ['slug'=>'properties'], // посилання слаг
                'label' => esc_html__('Property','aleproperty'),
                'supports' => array('title','editor','thumbnail'),// Какие поля будет поддерживать этот пост
            ));
            /* Post Types "Agent" */
            register_post_type('agent',
            array(
                'public' => true,
                'has_archive' => true,
                'rewrite' => ['slug'=>'agents'],
                'label' => esc_html__('Agents','aleproperty'),
                'supports' => array('title','editor','thumbnail'),
                'show_in_rest' =>true, // для відображення нового редактора Guttenberg
            ));


            // Реєструємо таксономію "location"
            $labels = array(
                'name'              => esc_html_x( 'Locations', 'taxonomy general name', 'aleproperty' ),
                'singular_name'     => esc_html_x( 'Location', 'taxonomy singular name', 'aleproperty' ),
                'search_items'      => esc_html__( 'Search Locations', 'aleproperty' ),
                'all_items'         => esc_html__( 'All Locations', 'aleproperty' ),
                'parent_item'       => esc_html__( 'Parent Location', 'aleproperty' ),
                'parent_item_colon' => esc_html__( 'Parent Location:', 'aleproperty' ),
                'edit_item'         => esc_html__( 'Edit Location', 'aleproperty' ),
                'update_item'       => esc_html__( 'Update Location', 'aleproperty' ),
                'add_new_item'      => esc_html__( 'Add New Location', 'aleproperty' ),
                'new_item_name'     => esc_html__( 'New Location Name', 'aleproperty' ),
                'menu_name'         => esc_html__( 'Location', 'aleproperty' ),
            );
            $args = array(
                'hierarchical' => true, // Дерево видна структура таксономії
                'show_ui' => true, // За замовчанням буде технічна таксономія і невидна в адмінці, щоб була видна ставимо труе
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array('slug'=>'properties/location'),
                'labels' => $labels,
            );

            register_taxonomy('location','property',$args);

            unset($args);
            unset($labels);


            // Реєструємо таксономію "property-type"
            $labels = array(
                'name'              => esc_html_x( 'Types', 'taxonomy general name', 'aleproperty' ),
                'singular_name'     => esc_html_x( 'Type', 'taxonomy singular name', 'aleproperty' ),
                'search_items'      => esc_html__( 'Search Types', 'aleproperty' ),
                'all_items'         => esc_html__( 'All Typens', 'aleproperty' ),
                'parent_item'       => esc_html__( 'Parent Type', 'aleproperty' ),
                'parent_item_colon' => esc_html__( 'Parent Type:', 'aleproperty' ),
                'edit_item'         => esc_html__( 'Edit Type', 'aleproperty' ),
                'update_item'       => esc_html__( 'Update Type', 'aleproperty' ),
                'add_new_item'      => esc_html__( 'Add New Type', 'aleproperty' ),
                'new_item_name'     => esc_html__( 'New Type Name', 'aleproperty' ),
                'menu_name'         => esc_html__( 'Type', 'aleproperty' ),
            );
            $args = array(
                'hierarchical' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array('slug'=>'properties/type'),
                'labels' => $labels,
            );

            register_taxonomy('property-type','property',$args);
        }

        public function custom_colums_for_property($columns){

            $title = $columns['title'];
            $date = $columns['date'];
            $location = $columns['taxonomy-location'];
            $type = $columns['taxonomy-property-type'];


            $columns['title'] = $title;
            $columns['date'] = $date;
            $columns['taxonomy-location'] = $location;
            $columns['taxonomy-property-type'] = $type;
            $columns['price'] = esc_html__('Price','aleproperty');
            $columns['offer'] = esc_html__('Offer','aleproperty');
            $columns['agent'] = esc_html__('Agent','aleproperty');


            return $columns;
    
        }

        public function custom_property_columns_data($column,$post_id){

            $price = get_post_meta($post_id,'aleproperty_price',true);
            $offer = get_post_meta($post_id,'aleproperty_type',true);
            $agent_id = get_post_meta($post_id,'aleproperty_agent',true);
            if($agent_id){
                $agent = get_the_title($agent_id);
            } else {
                $agent = 'No Agent';
            }
            

            switch($column){
                case 'price':
                    echo esc_html($price);
                    break;
                case 'offer':
                    echo $offer;
                    break;
                case 'agent':
                    echo $agent;
                    break;
            }

        }

        public function custom_property_columns_sort($columns){

            $columns['price'] = 'price';
            $columns['offer'] = 'offer';
            //$columns['agent'] = 'agent';

            return $columns;

        }

        public function custom_property_order($query){

            if(!is_admin()){
                return;
            }
            $orderby = $query->get('orderby');

            if('price' ==  $orderby){
                $query->set('meta_key','aleproperty_price');
                $query->set('orderby','meta_value_num');
            }
            if('offer' ==  $orderby){
                $query->set('meta_key','aleproperty_type');
                $query->set('orderby','meta_value');
            }
        }

    }
}
if(class_exists('alePropertyCpt')){
    $alePropertyCpt = new alePropertyCpt();
    $alePropertyCpt->register(); // Викликаємо реєстрацію хуків
}