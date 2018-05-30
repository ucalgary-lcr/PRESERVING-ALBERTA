<?php
// The template for displaying all single posts and attachments
get_header(); ?>

<div class="fieldable-panels-pane container-fluid brick text-cta">
    <div class="row">
        <div class="container">
            <div class="row">
                <div class="row-md-height" style="margin-top:150px;">

                    <div class="col-md-6 col-md-height col-md-middle vspacer-xs vspacer-sm headline" >
                        <h1><?php the_title(); ?></h1>
                      </div>
                    <div class="col-md-6 col-md-height col-md-middle description">
                      <p><?php echo(types_render_field( "team-role", array( ) )); ?></p>
                    </div>



                </div>
            </div>
        </div>
    </div>
</div>


<?php get_footer(); ?>
