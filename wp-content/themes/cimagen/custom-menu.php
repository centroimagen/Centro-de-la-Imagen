<div id="menu-wrapper">
  <?php while ( have_posts() ) : the_post(); ?>
    <div id="menu-container">
      <?php the_content(); ?>
      <?php the_post_thumbnail(); ?>
    </div>
  <?php endwhile; ?>
</div>