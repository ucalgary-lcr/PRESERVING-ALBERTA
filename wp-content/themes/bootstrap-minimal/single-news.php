<?php get_header();
/*
Template Name: News
Template Post Type: post, page, news
*/
?>
<div class="container">
  <div class="row" style="margin-top:130px; margin-bottom: 50px;"><!-- Blog Entries Column -->
    <h1><?php the_title(); ?></h1>
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

  <div class='container'>
    <div class='row'>

    <?php
      query_posts(array('post_type' => 'updates', 'showposts' => 20));

          if (have_posts()) {
            while (have_posts() ) : the_post();
            $newsExcerpts = types_render_field( 'update-excerpt', array());
            $newsContent = types_render_field('update-content', array());
            $newsImage = types_render_field('update-image', array("output" => "raw"));
            $newsLink = get_the_permalink();
            $title = get_the_title();
            $time = get_the_time('F jS, Y');
            $authorLink = get_the_author_link();


                echo ( "
                <div class='news-post-feed'>

                  <div class='col-md-6 no-padding'>
                    <a href='$newsLink'><img src='$newsImage' height='373px' width='520' class='img-responsive news-photo' /></a>
                  </div>

                  <div class='col-md-6 news-excerpt'>


                      <div class='col-md-12'>
                      <h2><a href='$newsLink'>$title</a></h2>
                      $newsExcerpts
                      </div>

                      <div class='col-md-12'>
                        <ul class='list-inline'>
                          <li><div class='btn btn-default btn-md red-back'><a href='$newsLink'>Continue reading »</a></div></li>
                          <li> </li>
                          <li><p><strong>Share:</strong></p></li>
                          <li><a href='https://plus.google.com/share?url=$newsLink' target='_blank'><span class='fa fa-google-plus'></span></a></li>
                          <li><a href='https://twitter.com/home?status=$newsLink' target='_blank'><span class='fa fa-twitter'></span></a></li>
                          <li><a href='https://www.facebook.com/sharer/sharer.php?u=$newsLink' target='_blank'><span class='fa fa-facebook'></span></a></li>
                          <li><a href='https://pinterest.com/pin/create/button/?url=$newsLink' target='_blank'><span class='fa fa-pinterest-p'></span></a></li>
                        </ul>
                      </div>
                    </div>

                  </div>

                " );
            endwhile;
            }

          else{
            echo('Broken');
          }
     ?>

    </div>
  </div>







<?php get_footer(); ?>
