<h1><?php esc_html_e('Welcome to aleProperty Plugin','aleproperty'); ?></h1>
<div class="content">
    <?php settings_errors(); ?>
    <form method="post" action="options.php">
        <?php
            settings_fields('aleproperty_settings');
            do_settings_sections('aleproperty_settings');
            submit_button();
        ?>
    </form>
</div>