<?php
/*
Template Name: Archives
*/
get_header();?>
<div class="container">
  <div class="row" style="margin-top:175px;">
    <div class="col-md-12">
      <?php
      // Query posts if there are any
      if(have_posts()){ ?>
        <h1 class="page-title">
          <?php
            if ( is_category() ) :
              single_cat_title();

            elseif ( is_tag() ) :
              single_tag_title();

            elseif ( is_author() ) :
              printf( esc_html__('Author: %s', ''), '<span class="vcard">' . get_the_author() . '</span>' );

            elseif ( is_day() ) :
              printf( esc_html__('Day: %s', ''), '<span>' . get_the_date() . '</span>' );

            elseif ( is_month() ) :
              printf( esc_html__('Month: %s', ''), '<span>' . get_the_date( _x('F Y', 'monthly archives date format', '') ) . '</span>' );

            elseif ( is_year() ) :
              printf( esc_html__('Year: %s', ''), '<span>' . get_the_date( _x('Y', 'yearly archives date format', '') ) . '</span>' );

            elseif ( is_tax( 'post_format', 'post-format-aside' ) ) :
              esc_html_e('Asides', '');

            elseif ( is_tax('post_format', 'post-format-gallery') ) :
              esc_html_e('Galleries', '');

            elseif ( is_tax( 'post_format', 'post-format-image' ) ) :
              esc_html_e( 'Images', '');

            elseif ( is_tax( 'post_format', 'post-format-video' ) ) :
              esc_html_e('Videos', '');

            elseif ( is_tax('post_format', 'post-format-quote') ) :
              esc_html_e('Quotes', '');

            elseif ( is_tax( 'post_format', 'post-format-link' ) ) :
              esc_html_e('Links', '');

            elseif ( is_tax( 'post_format', 'post-format-status' ) ) :
              esc_html_e('Statuses', '');

            elseif ( is_tax( 'post_format', 'post-format-audio' ) ) :
              esc_html_e('Audios', '');

            elseif ( is_tax( 'post_format', 'post-format-chat' ) ) :
              esc_html_e('Chats', '');

            else :
              esc_html_e('Search Results:', '');

            endif;
          ?>
        </h1>


        <div class="fieldable-panels-pane container-fluid brick top-margin bottom-margin">
          <div class="container">
            <div class="row">
              <div class="col-md-12">
                <form role="search" action="<?php echo site_url('/'); ?>" method="get" id="searchform">
                  <div class="input-group input-group-lg">
                    <input type="text" class="form-control" placeholder="Search for...">
                    <input type="hidden" name="post_type" value="sites" />
                    <span class="input-group-btn">
                      <input class="btn btn-default btn-lg maroon-back" type="submit" alt="Search" value="Search" type="button" />
                    </span>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>


        <?php
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
                  <p><?php echo(types_render_field( 'description', array() )); ?></p>
                  <p><a href="<?php the_permalink(); ?>">continue reading</a></p>
                </div>
              </div>
            </div>
          </article>

      <?php }} ?>
      <?php theme_numeric_posts_nav(); ?>
    </div>
    <!-- <div class="col-md-3">
      <?php //get_sidebar(); ?>
    </div>-->
  </div>
</div>
<?php get_footer(); ?>
