<?php

// архівний файл для постайпа

get_header(); ?>

<div class="wrapper archive_agent">
    <?php 

    // Вивід контенту
    if ( have_posts() ) {

        // Load posts loop.
        while ( have_posts() ) {
            the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <?php if(get_the_post_thumbnail(get_the_ID(),'large')) {
                    echo get_the_post_thumbnail(get_the_ID(),'large');
                } ?>
                <h2><?php the_title(); ?></h2>
                <div class="description"><?php the_excerpt(); ?></div>
                <a href="<?php the_permalink(); ?>">Open This Agent</a>
            </article>
        <?php }
    
    //Pagination
    posts_nav_link();
    
    } else {
        echo '<p>'.esc_html__('No Agents','aleproperty').'</p>';
    }
    ?>
</div>

<?php
get_footer();