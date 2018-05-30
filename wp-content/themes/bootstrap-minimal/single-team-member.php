<?php get_header();
/*
Template Name: Team Members
*/
?>
<div class="fieldable-panels-pane container-fluid brick text-cta">
  <div class="row" style="margin-top:175px;">
    <div class="container">
      <div class="row">
        <div class="row-md-height">
          <div class="col-md-4 col-md-height col-md-middle vspacer-xs vspacer-sm headline">
            <h1><?php the_title(); ?></h1>
          </div>
          <div class="col-md-8 col-md-height col-md-middle description">
            <p>
              <?php if ( have_posts() ) : while ( have_posts() ) : the_post();
                the_content();
                endwhile; else: ?>
                <p>Sorry, no posts matched your criteria.</p>
              <?php endif; ?>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

  <div class='container'>
    <div class='row'>
    <?php
      query_posts(array('post_type' => 'team-member', 'showposts' => 999));

          if (have_posts()) {

            $counter = 1;

            while (have_posts() ) : the_post();



            $imageTeam = types_render_field( 'team-photo', array( "class" => "img-responsive img-circle center-block profile-img-size" ));
            $title = get_the_title();
            $teamRole = types_render_field('team-role', array("output" => "raw"));
            $teamEmail = types_render_field('team-email', array("output" => "raw"));


                echo (
                  "
                    <div class='col-sm-6 col-md-3 col-lg-3 center'>
                      $imageTeam
                      <h3>$title</h3>

                        <div class='description-wrapper'>
                          <p>$teamRole</p>
                        </div>
                        "
                      );

                    if ( $teamEmail == true ) {
                      echo(
                          "
                          <div class='btn-wrapper'>
                            <div class='button'>
                              <a class='btn btn-default btn-lg maroon-back' href='mailto:$teamEmail' role='button'>Email</a>
                            </div>
                          </div>
                          "
                        );
                    }
                echo("
                </div>
                ");
                if ( $counter % 4 == 0 ){
                  echo"</div><div class='row' style='margin-bottom:50px;margin-top:50px;'>";
                }
                $counter++;
            endwhile;
            }

          else{
            echo('Broken');
          }
     ?>


  </div>
</div>





<?php get_footer(); ?>
