<?php get_header(); ?>



<div class="container">
  <div class="row" style="margin-top:175px;">
   <br class="break" >
    <div class="col-md-9">
      <?php
      // Query posts if there are any
      if(have_posts()){ ?>
        <h1 class="page-title"><?php printf( esc_html__('Search Results for: %s', ''), '<span>' . get_search_query() . '</span>' ); ?></h1>
        <?php
        while(have_posts()){  the_post(); ?>
          <article <?php post_class(); ?>>
          <?php if (has_post_thumbnail()){ ?>
            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('thumbnail', array('class' => 'img-thumbnail img-responsive news-photo post-thumbnail')); ?></a>
          <?php } ?>
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <?php the_excerpt(); ?>
            <p><a href="<?php the_permalink(); ?>">continue reading</a></p>
        </article>
      <?php }}else{ ?>
        <h1>Nothing Found</h1>
        <div class="alert alert-warning" role="alert"><p>Sorry, but nothing matched your search terms. Please try again with some different keywords.</p></div>
        <?php get_template_part( 'content', 'none' ); ?>
      <?php } ?>
      <?php theme_numeric_posts_nav(); ?>
    </div>
    <div class="col-md-3">
      <?php get_sidebar(); ?>
    </div>
  </div>
</div>
<?php get_footer(); ?>
