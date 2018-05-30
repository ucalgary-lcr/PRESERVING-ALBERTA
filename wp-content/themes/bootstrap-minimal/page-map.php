<?php get_header();
/*
Template Name: Map
*/
?>
<div class="fieldable-panels-pane container-fluid brick text-cta contextual-links-region">
  <div class="row map-title-margin">
    <div class="container">
      <div class="row">
        <div class="row-md-height">
          <div class="col-md-4 col-md-height col-md-middle vspacer-xs vspacer-sm headline">
            <h1>Map</h1>
          </div>
          <div class="col-md-8 col-md-height col-md-middle description">
						<p><?php echo(types_render_field("map-desc", array("output" => "raw"))); ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="map-styles"></div>
			<?php while(have_posts()): the_post(); ?>
			<?php the_content();
        endwhile;
			?>
<?php get_footer(); ?>
