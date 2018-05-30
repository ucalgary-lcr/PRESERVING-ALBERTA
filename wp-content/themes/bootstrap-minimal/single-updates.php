<?php
// The template for displaying all single posts and attachments
get_header(); ?>
<style>
main#content:after {height: 5px;content: '';display: block;}
</style>


<div class="fieldable-panels-pane container-fluid brick hero-cta parallax top  rounded-brick-below no-bottom-margin light  image-focus-right ga-hero-cta">
  <div class='row vbottom hleft'  style='background-image: url(&quot;<?php echo(types_render_field( "update-image", array("output" => "raw") )); ?>&quot;); background-position: 100% 0px;'>
    <div class='container'>
      <div class='row'>
        <div class='col-sm-12 hidden-md hidden-lg'>
          <img id='herobg1' class='img-responsive' src='<?php echo(types_render_field( "update-image", array("output" => "raw") )); ?>'>
        </div>
        <div class='col-sm-12'>
          <div class='cta-content cta-content-left'>
            <h1><?php the_title(); ?></h1>
            <?php echo (types_render_field( "update-excerpt", array())); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="fieldable-panels-pane container-fluid brick text no-margin-bottom">
  <div class="row left">
    <div class="container">

        <div class="row">
          <div class="col-sm-12 one-col" style="padding-top:50px;">
              <?php echo (types_render_field( "update-content", array("class" => "para-space"))); ?>
          </div>
      </div>

    </div>
  </div>
</div>


<?php get_footer(); ?>
