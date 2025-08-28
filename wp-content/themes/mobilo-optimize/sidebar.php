<aside id="sidebar">
    <?php if (is_active_sidebar('main_sidebar')) : ?>
        <?php dynamic_sidebar('main_sidebar'); ?>
    <?php else : ?>
        <p>Add widgets here</p>
    <?php endif; ?>
</aside>