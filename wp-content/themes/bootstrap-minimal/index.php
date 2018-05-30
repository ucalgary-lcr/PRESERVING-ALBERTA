<?php get_header(); ?>
<div class="container">
  <div class="row" style="margin-top:175px; margin-bottom: 50px;"><!-- Blog Entries Column -->
    <h1>News</h1>
  </div>
</div>

<div class="fieldable-panels-pane container-fluid brick top-margin bottom-margin">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <form class="" method="get" id="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
          <div class="input-group input-group-lg">
            <input type="search" class="form-control" placeholder="Search" name="s" id="search-input" value="<?php echo esc_attr(get_search_query()); ?>" />
              <span class="input-group-btn">
                  <button class="btn btn-default maroon-back" type="button"><i class="fa fa-search" id="search-submit" value="Search"></i></button>
              </span>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="container" >
    <div class="row"><!-- Blog Entries Column -->
        <div class="col-md-12">
          <?php
          // Query posts if there are any
          if(have_posts()){
            while(have_posts()){  the_post(); ?>
              <article <?php post_class(); ?>>
                <div class='news-post-feed'>

                  <div class='col-md-6 no-padding'>
                    <?php if (has_post_thumbnail()){ ?>
                      <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'large', array( 'class' => 'img-responsive news-photo',)); ?></a>
                    <?php } ?>
                  </div>

                  <div class='col-md-6 news-excerpt'>
                    <div class='col-md-12'>
                      <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                      <?php posted_on(); ?>
                      <p><?php the_excerpt(); ?></p>
                      <p><a href="<?php the_permalink(); ?>">continue reading</a></p>
                    </div>
                  </div>
                </div>
              </article>
          <?php }} ?>
          <?php theme_numeric_posts_nav(); ?>
        </div>

    </div>
  </div>
<?php get_footer(); ?>
