<?php

// При видаленні плагіна видаляємо контент з БД
$properties = get_posts(array('post_type'=>['property','agent'],'numberposts'=>-1)); // Отримаємо всі пости
foreach($properties as $property){
    wp_delete_post($property->ID,true); // Видаляємо їх в циклі
}