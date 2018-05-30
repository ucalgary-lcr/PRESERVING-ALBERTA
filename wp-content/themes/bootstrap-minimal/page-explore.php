<?php get_header();
/*
Template Name: Explore
*/
?>
<style>
.ui-widget-header{background:#f6931f;border: 1px solid #f6931f;}
.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {border: 1px solid #f6931f;background: #f6931f;font-weight: 400;color: #555;}
.ui-widget.ui-widget-content {border: 1px solid #f6931f;}
.ui-slider-horizontal .ui-slider-handle {top: -0.4em;margin-left: -.4em;}
.ui-slider .ui-slider-handle {position: absolute;z-index: 2;width: 1.6em;height: 1.6em;cursor: default;-ms-touch-action: none;touch-action: none;}
main#content::after {content: '';display: block;height: 0;}
.explore-content-details{bottom: 0;position: absolute;color:#fff;}
.explore-circle-style{font-size: 26px;}
@media (min-width: 992px){.brick.chicklets .chicklet {height: 430px !important;padding: 30px !important;cursor: auto;}
.brick.chicklets .chicklet > h2 {padding: 0 15px;text-align: left;}
}
.desc-overflow{display: block;display: -webkit-box;height: 70px;margin: 0 auto;line-height: 1.4;-webkit-line-clamp: 3;-webkit-box-orient: vertical;overflow: hidden;text-overflow: ellipsis;}

</style>

<div class="fieldable-panels-pane container-fluid brick text-cta">
  <div class="row" style="margin-top:175px;">
    <div class="container">
      <div class="row">
        <div class="row-md-height">
          <div class="col-md-3 col-md-height col-md-middle vspacer-xs vspacer-sm headline">
            <h1><?php the_title(); ?></h1>
          </div>
          <div class="col-md-9 col-md-height col-md-middle description">
            <p>
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post();
              the_content();
              endwhile; else: ?>
              <p>Sorry, no posts matched your criteria.</p>
            <?php endif; ?>
          </p>

            <div class="row">
              <!-- Search Section -->
              <div class="col-lg-6">
                <div class="input-group">
                  <span class="input-group-btn">
                    <button class="btn btn-default maroon-back" id='culture-option' style="border-radius: 0;" type="button">Culture Filter</button>
                  </span>
                  <select class="form-control" id="culture-filter">
                    <option>Select</option>
                      <?php
                      $args = array(
                                 'orderby' => 'ID',
                                 'order'=> 'ASC',
                                 'exclude' => array(1)
                              );

                      $categories = get_categories($args);
                      foreach ( $categories as $category ) {
                          $term_link = get_category_link($category->term_id );
                          $term_link = esc_url( $term_link );
                          echo '<option value="'.$category->term_id.'"><a href="'.$term_link.'">'.$category->cat_name.'</a></option>';
                      }
                      ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-6">
                <div class="input-group">
                  <span class="input-group-btn">
                    <button class="btn btn-default maroon-back" id='threat-option' style="border-radius: 0;" type="button">Threat Filter</button>
                  </span>
                  <select class="form-control" id='threat-filter'>
                    <option>Select</option>
                    <option>Low</option>
                    <option>Medium</option>
                    <option>High</option>
                  </select>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>



<div class="fieldable-panels-pane container-fluid brick top-margin bottom-margin">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div id="slider-range"></div>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
          <h2 style="margin-top:25px;">
            <label for="amount">Year range:</label>
            <input type="text" id="amount" readonly style="border:0; color:#f6931f; font-weight:bold;">
          </h2>
        </div>
      </div>
    </div>
  </div>


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


<div class="fieldable-panels-pane container-fluid brick chicklets no-bottom-margin rounded-brick-above">
  <div class="row">


    <?php


    query_posts(array('post_type' => 'sites', 'posts_per_page' => 999, 'order' => 'DSC'));
    if (have_posts()) {
      $featuredPostCount = 0;
      while (have_posts()) :
        the_post() ;

      $featuredPost = types_render_field("featured", array("output" => "raw"));
      $photoURL =  get_the_post_thumbnail_url();
      $sitesPostLink = get_post_permalink();
      $postTitle = get_the_title();
      $description = types_render_field("description", array("output" => "raw"));
      $historicalPeriod = types_render_field("historic-period", array());
      $threatRender = types_render_field( "threat-level", array() );
      $category = get_the_category( );

      $categories = array_map( function($yourObject) { return $yourObject->name; }, $category );
      $categoryList = implode(", ", $categories );

      if($featuredPost == 1&&$featuredPostCount < 1 ){
      echo "
      <div class='col-sm-8 col-md-8 chicklet light-orange-back all' id='featured-1' data-feature='$featuredPost' data-year='$historicalPeriod' data-threat='$threatRender' data-culture='$categoryList' style='background:url($photoURL)' title='$postTitle'>
        <h2>$postTitle</h2>
        <div class='light-orange-back'>
          <h3>$postTitle</h3>
        <div class='desc-overflow'><p>$description</p></div>
        <p><a href='$sitesPostLink' class='read-more'>Read More</a></p>
        </div>
      </div>
      "; $featuredPostCount++;
      }

      endwhile;
      }
    else{
      echo('Broken');
    }
    ?>

    <?php

    query_posts(array('post_type' => 'sites', 'posts_per_page' => 999,  'order' => 'DSC'));
    if (have_posts()) {
      $featuredPostCountTwo = 0;
      while (have_posts() ) : the_post();

      $featuredPostTwo = types_render_field("featured", array("output" => "raw"));
      $photoURLTwo =  get_the_post_thumbnail_url();
      $sitesPostLinkTwo = get_post_permalink();
      $postTitleTwo = get_the_title();
      $descriptionTwo = types_render_field("description", array());
      $historicalPeriodTwo = types_render_field("historic-period", array());
      $threatRenderTwo = types_render_field( "threat-level", array() );
      $categoryTwo = get_the_category( );

      $categoriesTwo = array_map( function($yourObject) { return $yourObject->name; }, $categoryTwo );
      $categoryListTwo = implode(", ", $categoriesTwo );

      if($featuredPostTwo == 2&&$featuredPostCountTwo < 1){
        echo "
        <div class='col-sm-4 col-md-4 chicklet light-green-back all' id='featured-2' data-feature='$featuredPostTwo' data-year='$historicalPeriodTwo' data-threat='$threatRenderTwo' data-culture='$categoryListTwo' style='background:url($photoURLTwo)' title='$postTitleTwo'>
          <h2>$postTitleTwo</h2>
          <div class='light-green-back'>
            <h3>$postTitleTwo</h3>
          <div class='desc-overflow'><p>$descriptionTwo</p></div>
          <p><a href='$sitesPostLinkTwo' class='read-more'>Read More</a></p>
          </div>
        </div>
        "; $featuredPostCountTwo++;
        }
      else{ echo("");}

    endwhile;
    }

  else{
    echo('Broken');
  }
    ?>


    <?php

    query_posts(array('post_type' => 'sites', 'posts_per_page' => 999, 'order' => 'DSC'));
    if (have_posts()) {
      while (have_posts() ) : the_post();

      $generalPost = types_render_field("featured", array("output" => "raw"));
      $photoURLGeneral =  get_the_post_thumbnail_url();
      $sitesPostLinkGeneral = get_post_permalink();
      $postTitleGeneral = get_the_title();
      $descriptionGeneral = types_render_field("description", array());
      $historicalPeriodGeneral = types_render_field("historic-period", array());
      $threatRenderGeneral = types_render_field( "threat-level", array() );
      $categoryGeneral = get_the_category( );

      $categoriesGeneral = array_map( function($yourObject) { return $yourObject->name; }, $categoryGeneral );
      $categoryListGeneral = implode(", ", $categoriesGeneral );

      if($generalPost == 0){
          echo "
          <div class='col-sm-6 col-md-3 chicklet maroon-back all general' data-feature='$generalPost' data-year='$historicalPeriodGeneral' data-threat='$threatRenderGeneral' data-culture='$categoryListGeneral' style='background:url($photoURLGeneral)' title='$postTitleGeneral'>
            <h2>$postTitleGeneral</h2>
            <div class='maroon-back'>
              <h3>$postTitleGeneral</h3>
              <div class='desc-overflow'><p>$descriptionGeneral</p></div>
              <p><a href='$sitesPostLinkGeneral' class='read-more'>Read More</a></p>
            </div>
          </div>";
        }
        else{
          echo("");
        }

    endwhile;
    }

  else{
    echo('Broken');
  }
    ?>

      </div>
    </div>

    <script>
    function filterContents() {

      var beginYear = $("#slider-range").slider("values")[0];
      var endYear = $("#slider-range").slider("values")[1];
      var cultureSelection = $("#culture-filter option:selected").text();
      var re = new RegExp(cultureSelection);
      var threatSelection = $("#threat-filter option:selected").text();

      $(".all").hide()
          .filter(function () {
              var year = $(this).attr("data-year");
              var returnVal =
                  (year >= beginYear && year <= endYear)
                  && (cultureSelection == "Select" || re.test($(this).attr("data-culture")))
                  && (threatSelection == "Select" || $(this).attr("data-threat") == threatSelection)
                  ;

              return returnVal;
          })
          .show();
        }

        $('#culture-filter').change(function() {
          filterContents();
        });

        $('#threat-filter').change(function() {
          filterContents();
        });

        $(function () {
            $("#slider-range").slider({
                range: true,
                min: 1700,
                max: 2000,
                values: [1700, 2000],
                slide: function (event, ui) {
                    $("#amount").val( ui.values[0] + " - " + ui.values[1]);
              filterContents();
                }
            });

            $("#amount").val( $("#slider-range").slider("values", 0) +
                " - " + $("#slider-range").slider("values", 1));
        });
    </script>

<?php get_footer(); ?>
