<?php
// The template for displaying all single posts and attachments
get_header(); ?>
<style>
.ct-location-images-numbers{background-color:rgba(0, 0, 0, 0.5); height:50px; width:100%; position: relative;padding:15px;counter-reset: section;}
body{counter-reset:scan-location;}
.ct-location-images-numbers section{color:#fff; counter-increment:scan-location;}
.ct-location-images-numbers section::before{content: 'Scan Location ' counter(scan-location);}
.wp-caption-text{background-color: rgba(109,100,93,.8);color: #fff;padding: 5px;width: 97%;}
.brick.tasks .tabs-left .tab-content {vertical-align: top;position:relative;}
.brick.tasks .tab-content > .tab-pane{vertical-align: top;position:relative;top: 0;-webkit-transform: none;-ms-transform: none;transform: none;padding: 0 30px 0 0;}
.brick.tasks .tabs-left, .brick.tasks .tabs-right {height: auto;}
</style>
<?php

  // Types toolset render variable
  $longitude = types_render_field( "map-location", array( 'format' => 'FIELD_LONGITUDE' ));
  $latitude = types_render_field( "map-location", array( 'format' => 'FIELD_LATITUDE' ));
  $postTitle =  get_the_title();

  //Desktop tab navigation display section
  $backgroundTab = "<li role='presentation' class='active'><a href='#background' aria-controls='background' role='tab' data-toggle='tab'>Background</a></li>";
  $photoGalleryTab = "<li role='presentation'><a href='#photo-gallery' aria-controls='photo-gallery' role='tab' data-toggle='tab'>Photo Gallery</a></li>";
  $mapTab = "<li role='presentation'><a href='#map' aria-controls='map' role='tab' data-toggle='tab'>Map</a></li>";
  $captureTechniquesTab = "<li role='presentation'><a href='#capture-techniques' aria-controls='capture-techniques' role='tab' data-toggle='tab'>Capture Techniques</a></li>";
  $dataFilesTab = "<li role='presentation'><a href='#data-files' aria-controls='data-files' role='tab' data-toggle='tab'>Data Files</a></li>";
  $movieTab = "<li role='presentation'><a href='#movies' aria-controls='movies' role='tab' data-toggle='tab'>Video</a></li>";
  $panoramaTab = "<li role='presentation'><a href='#panorama' aria-controls='panorama' role='tab' data-toggle='tab'>Panorama</a></li>";
  $vrTab = "<li role='presentation'><a href='#vr' aria-controls='vr' role='tab' data-toggle='tab'>Virtual Reality</a></li>";




    /**
     * Get an attachment ID given a URL.
     *
     * @param string $url
     *
     * @return int Attachment ID on success, 0 on failure
     */
    function get_attachment_id( $url ) {

    	$attachment_id = 0;

    	$dir = wp_upload_dir();

    	if ( false !== strpos( $url, $dir['baseurl'] . '/' ) ) { // Is URL in uploads directory?

    		$file = basename( $url );

    		$query_args = array(
    			'post_type'   => 'attachment',
    			'post_status' => 'inherit',
    			'fields'      => 'ids',
    			'meta_query'  => array(
    				array(
    					'value'   => $file,
    					'compare' => 'LIKE',
    					'key'     => '_wp_attachment_metadata',
    				),
    			)
    		);

    		$query = new WP_Query( $query_args );

    		if ( $query->have_posts() ) {

    			foreach ( $query->posts as $post_id ) {

    				$meta = wp_get_attachment_metadata( $post_id );

    				$original_file       = basename( $meta['file'] );
    				$cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );

    				if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
    					$attachment_id = $post_id;
    					break;
    				}

    			}

    		}

    	}

    	return $attachment_id;
    }




  //Types Toolset rendered content and foreach loops for tab content section
  $backgroundTabRender = types_render_field( "background", array( ) );
  $photoGalleryTabRender = types_render_field( "photo", array( "class" => "img-responsive gallery-images", "id" => "", "width" => "400px", "height" => "400px" ) );
  $photos = types_render_field( "photo", array( "output" => "raw",  "separator" => "|", "class" => "img-responsive gallery-images", "id" => "", "width" => "400px", "height" => "400px", "alt" => "" ) );
    $photosArray = explode( "|", $photos );
     function photoGallery($photosArray, $postTitle, $alt ){
       foreach ( $photosArray as $photoLite ) {
         $photoID = get_attachment_id($photoLite);
         $photoMeta = get_post_meta( $photoID );
         $alt = $photoMeta['_wp_attachment_image_alt'][0];
          $image = get_post($photoID);
          $image_title = $image->post_title;
          $image_caption = $image->post_excerpt;



          echo ("
            <div class='col-md-4'>
              <a href='$photoLite' data-title='$image_caption' data-lightbox='Testing'>
                <img src='$photoLite' class='thumbnail gallery-images' alt='$alt' width='400px' height='400px' />
              </a>
            </div>
            ");
          }}



  $MapTabRender = types_render_field( "display", array("output" => "raw" ) );
    function theMap($MapTabRender,$longitude,$latitude){
      if ($MapTabRender == "0") {

         echo ("<div class='google-mapping'><iframe src='https://www.google.com/maps/embed/v1/search?key=AIzaSyC7EKfWhQMFjH_tu530xq_irRej0PzBq24&q=$latitude,$longitude' width='100%' height='450' frameborder='0' style='border:0' allowfullscreen></iframe></div>");
      } elseif ($MapTabRender == "1") {
         echo ("<div class='mapping-images'><img src='https://preserve.ucalgary.ca/wp-content/uploads/2018/02/NW-Alberta-2.jpg' width='100%' height='400px' /></div>");
      } elseif ($MapTabRender == "2") {
         echo ("<div class='mapping-images'><img src='https://preserve.ucalgary.ca/wp-content/uploads/2018/02/NE-Alberta-3.jpg' width='100%' height='400px' /></div>");
      } elseif ($MapTabRender == "3") {
         echo ("<div class='mapping-images'><img src='https://preserve.ucalgary.ca/wp-content/uploads/2018/02/SW-Alberta-2.jpg' width='100%' height='400px' /></div>");
      } elseif ($MapTabRender == "4") {
         echo ("<div class='mapping-images'><img src='https://preserve.ucalgary.ca/wp-content/uploads/2018/02/SE-Alberta-2.jpg' width='100%' height='400px' /></div>");
      }}
  $moviesTabRender = types_render_field( "video", array('output' => 'raw',  "separator" => "|"));
  $video = explode( "|", $moviesTabRender );
    function youtubeVideo($video){foreach( $video as $youTube){
        echo ("
        <div class='col-md-6' style='padding-bottom:25px;'>
          <div class='embed-responsive embed-responsive-16by9'>
            <iframe width='560' height='315' src='https://www.youtube.com/embed/$youTube' frameborder='0' allow='autoplay; encrypted-media' allowfullscreen></iframe>
          </div>
        </div>
          ");}}
  $panoramaTabRender = types_render_field( "panorama", array('output' => 'raw'));
    function panoramaLink($panoramaTabRender){
      echo (
        "
        <div class='embed-responsive embed-responsive-16by9'>
          <iframe src='https://h5.veer.tv/photo-player?pid=$panoramaTabRender' frameborder='0' allowfullscreen='true' width='560' height='315'></iframe>
        </div>
        "
      );
    }
  $dataFilesTabRender = types_render_field( "file", array('output' => 'raw', 'separator' => '|') );
  $dataNameTabRender = types_render_field( "file-name", array('separator' => '|' ) );
    $dataFilesArray= explode( "|", $dataFilesTabRender );
    $dataNameArray = explode( "|", $dataNameTabRender );
      function dataLinks($dataNameArray, $dataFilesArray) {
        foreach($dataNameArray as $key=>$dataTitle){
          echo("<a href='$dataFilesArray[$key]' target='_blank'><h3>$dataTitle</h3></a>");
        }
      }


    $ctDetails = types_render_field( 'details', array( ) );
    $ctScanLocations = types_render_field( 'scan-locations', array( ) );
    $ctLocations = types_render_field( 'locations', array( "output" => "raw",  "separator" => "|" ) );
    $ctLoc = explode( "|", $ctLocations );
    $ctLocationImageContent = " ";
      foreach ( $ctLoc as $ctlocPhotos ) {
         $ctContainers = "<div class='col-md-4 ct-img-gal'><a href='$ctlocPhotos' data-title='$postTitle' data-lightbox='$postTitle'><img src='$ctlocPhotos' class='ct-location-images' /></a> <div class='ct-location-images-numbers'><strong><section></section></strong></div></div>";
          $ctLocationImageContent = $ctLocationImageContent . $ctContainers;
        }
        $captureTechniquesTabRender = "
          <div class='container'><div class='row'><div class='col-md-12'>$ctDetails</div></div></div>
          <div class='container'><div class='row'><div class='col-md-12'><br><h4>Scan Locations</h4></div></div></div>
          <div class='container'><div class='row'><div class='col-md-12'><div class='ct-scanloction-image'>$ctScanLocations</div></div></div></div>
          <div class='container'><div class='row'>$ctLocationImageContent</div></div>
        ";

  $vrTabRender = types_render_field( "vr", array( ) );


  //Mobile tab variables
  $backgroundMobileTab = "<div class='panel panel-default'><div class='panel-heading' role='tab' id='headingOne'><h4 class='panel-title'><a role='button' data-toggle='collapse' data-parent='#accordion' href='#background-mobile' aria-expanded='true' aria-controls='collapseOne'>Background</a></h4></div>";
  $photoGalleryMobileTab = "<div class='panel panel-default'><div class='panel-heading' role='tab' id='headingTwo'><h4 class='panel-title'><a class='collapsed' role='button' data-toggle='collapse' data-parent='#accordion' href='#photo-gallery-mobile' aria-expanded='false' aria-controls='collapseTwo'>Photo Gallery</a></h4></div>";
  $mapMobileTab = "<div class='panel panel-default'><div class='panel-heading' role='tab' id='headingThree'><h4 class='panel-title'><a class='collapsed' role='button' data-toggle='collapse' data-parent='#accordion' href='#map-mobile' aria-expanded='false' aria-controls='collapseThree'>Map</a></h4></div>";
  $captureTechniquesMobileTab = "<div class='panel panel-default'><div class='panel-heading' role='tab' id='headingFour'><h4 class='panel-title'><a class='collapsed' role='button' data-toggle='collapse' data-parent='#accordion' href='#capture-techniques-mobile' aria-expanded='false' aria-controls='collapseFour'>Capture Techniques</a></h4></div>";
  $dataFilesMobileTab = "<div class='panel panel-default'><div class='panel-heading' role='tab' id='headingFive'><h4 class='panel-title'><a class='collapsed' role='button' data-toggle='collapse' data-parent='#accordion' href='#data-files-mobile' aria-expanded='false' aria-controls='collapseFive'>Data Files</a></h4></div>";
  $movieMobileTab = "<div class='panel panel-default'><div class='panel-heading' role='tab' id='headingSix'><h4 class='panel-title'><a class='collapsed' role='button' data-toggle='collapse' data-parent='#accordion' href='#movie-mobile' aria-expanded='false' aria-controls='collapseSix'>Video</a></h4></div>";
  $panoramaMobileTab = "<div class='panel panel-default'><div class='panel-heading' role='tab' id='headingSeven'><h4 class='panel-title'><a class='collapsed' role='button' data-toggle='collapse' data-parent='#accordion' href='#panorama-mobile' aria-expanded='false' aria-controls='collapseSeven'>Panorama</a></h4></div>";
  $vrMobileTab = "<div class='panel panel-default'><div class='panel-heading' role='tab' id='headingEight'><h4 class='panel-title'><a class='collapsed' role='button' data-toggle='collapse' data-parent='#accordion' href='#vr-mobile' aria-expanded='false' aria-controls='collapseSeven'>Virtual Reality</a></h4></div>";
?>


<!-- Desktop banner content section -->
<div class="fieldable-panels-pane container-fluid brick hero-cta large rounded-top bottom-margin hidden-md hidden-sm hidden-xs ">
  <div class="container">
    <div class="row">
      <div class="col-sm-12 ">
        <div class="location-content-holder hero-content-open">
        <a href="#"> <span class="fa fa-angle-double-left fa-2x content-btn" style="margin-top:5px;float:right;"></span></a>
        <div class="location-content">
          <div class="col-md-12">
            <h1><?php the_title(); ?></h1>
            <p><?php echo(types_render_field( "description", array( ) )); ?></p>
            <p><strong>Region:</strong> <?php echo(types_render_field( "country", array( ) )); ?></p>
            <p><strong>Field Documentation:</strong> <?php echo(types_render_field( "field-documentation", array( ) )); ?></p>
            <p><strong>Field Documentation Type:</strong> <?php echo(types_render_field( "documentation-type", array( ) )); ?></p>
            <p><strong>Culture:</strong> <?php echo(the_category( ', ' )); ?></p>
            <p><strong>Historic Period:</strong> <?php echo(types_render_field( "historic-period", array( ) )); ?>CE</p>
          </div>

          <div class="col-md-4">
            <p><strong>Latitude</strong></p>
            <p><?php echo($latitude); ?></p>
          </div>
          <div class="col-md-4">
            <p><strong>Longitude</strong></p>
            <p><?php echo($longitude); ?></p>
          </div>
          <div class="col-md-4">
            <p><strong>Datum Type</strong></p>
            <p><?php echo(types_render_field( "datum-type", array( ) )); ?></p>
          </div>
          <div class="col-sm-12 ">
          <h3>Threat Level</h3>
        </div>
          <div class="col-xs-4 col-sm-4" style="background-color:green;height: 20px;">
            <?php
              $threatRender = types_render_field( "threat-level", array("output" => "raw" ) );
              $green = "<span class='fa fa-sort-desc fa-4x' aria-hidden='true' id='green-threat-arrow' style=''></span>";
              $orange = "<span class='fa fa-sort-desc fa-4x' aria-hidden='true' id='orange-threat-arrow' style=''></span>";
              $red =  "<span class='fa fa-sort-desc fa-4x' aria-hidden='true' id='red-threat-arrow' style=''></span>";
              if ($threatRender == "0") {echo ($green);}  elseif ($threatRender == "1") {echo($orange);} else {echo($red);}
            ?>
          </div>
          <div class="col-xs-4 col-sm-4" style="background-color:orange;height: 20px;"></div>
          <div class="col-xs-4 col-sm-4" style="background-color:red;height: 20px;"></div>

        </div>
        <div class="location-content" style="display:none;">
            <h1 class="vertical-title"><?php the_title(); ?></h1>
        </div>

      </div>
    </div>
  </div>
</div>
<iframe width="100%" height="100%" src="https://sketchfab.com/models/<?php echo(types_render_field( "sketchfab-id", array( ) )); ?>/embed?autostart=1" frameborder="0" allowvr allowfullscreen mozallowfullscreen="true" webkitallowfullscreen="true" onmousewheel=""></iframe>
</div>

<!-- desktop tab navigation and content -->
<div class="fieldable-panels-pane container-fluid brick tasks hidden-md hidden-sm hidden-xs">

        <div class="tabcordion tabs-left">
            <ul class="nav nav-tabs" role="tablist">
                <?php
                  if ($backgroundTabRender == null) {echo ("");}  else {echo($backgroundTab);}
                  if ($photoGalleryTabRender == null) {echo ("");}  else {echo($photoGalleryTab);}
                  if ($MapTabRender == null) {echo ("");}  else {echo($mapTab);}
                  if ($captureTechniquesTabRender == null) {echo ("");}  else {echo($captureTechniquesTab);}
                  if ($dataFilesTabRender == null) {echo ("");}  else {echo($dataFilesTab);}
                  if ($moviesTabRender == null) {echo ("");}  else {echo($movieTab);}
                  if ($panoramaTabRender == null) {echo ("");}  else {echo($panoramaTab);}
                  if ($vrTabRender == null) {echo ("");}  else {echo($vrTab);}
                ?>
            </ul>
            <div class="tab-content container" style="border:none;">
              <div role="tabpanel" class="tab-pane active" id="background">
                <div class="container">
                  <div class="row">
                    <div class="col-md-12 w-photo">
                      <?php echo($backgroundTabRender); ?>
                    </div>
                  </div>
                </div>
              </div>
              <div role="tabpanel" class="tab-pane" id="photo-gallery">
                <div class="container">
                  <div class="row">
                    <div class="col-md-12">
                      <?php photoGallery($photosArray, $postTitle); ?>
                    </div>
                  </div>
                </div>
              </div>
              <div role="tabpanel" class="tab-pane" id="map">
                <div class="container">
                  <div class="row">
                    <div class="col-md-12">
                      <?php theMap($MapTabRender,$longitude,$latitude); ?>

                    </div>
                  </div>
                </div>
              </div>
              <div role="tabpanel" class="tab-pane" id="capture-techniques">
                    <?php echo($captureTechniquesTabRender); ?>
              </div>
              <div role="tabpanel" class="tab-pane" id="data-files">
                <div class="container">
                  <div class="row">
                    <div class="col-md-12">
                      <?php echo(types_render_field( "data-details", array( ) )); ?>
                      <?php
                        dataLinks($dataNameArray, $dataFilesArray);
                      ?>
                    </div>
                  </div>
                </div>
              </div>
              <div role="tabpanel" class="tab-pane" id="movies">
                <div class="container">
                  <div class="row">
                    <?php youtubeVideo($video); ?>
                  </div>
                </div>
              </div>
              <div role="tabpanel" class="tab-pane" id="panorama">
                <div class="container">
                  <div class="row">
                    <div class="col-md-12">
                      <?php panoramalink($panoramaTabRender); ?>
                    </div>
                  </div>
                </div>
              </div>
              <div role="tabpanel" class="tab-pane" id="vr">
                <div class="container">
                  <div class="row">
                    <div class="col-md-12">
                      <?php echo($vrTabRender); ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

<!-- mobile section -->
<style>
  @media (min-width:280px) and (max-width:1199px) {
    .panel {margin-bottom: -5px !important;}
    .panel-heading .panel-title a{color:#fff;}
    .panel-heading .panel-title a:hover{color:#f47c00;}
    .panel-default > .panel-heading {color: #fff;background-color: #e30c00;border-color: #e30c00;}
  }
  @media (min-width:1025px) and (max-width:1049px) {
    header #navigation .uc-nav .uc-menuwrapper ul li {
      margin: 0 0 0 25px;position: relative;left: -15px;
    }
  }
  @media (min-width:992px) and (max-width:1024px) {
    header #navigation .uc-nav .uc-menuwrapper ul li {
      margin: -3px 0 0px 0;position: relative;left: 190px;
    }
  }
</style>

<!-- mobile banner content section -->
<div class="fieldable-panels-pane container-fluid brick top-margin bottom-margin hidden-lg" style="">
  <iframe class="iframe-mobile" src="https://sketchfab.com/models/<?php echo(types_render_field( "sketchfab-id", array( ) )); ?>/embed?autostart=0" frameborder="0" allowvr allowfullscreen mozallowfullscreen="true" webkitallowfullscreen="true" onmousewheel=""></iframe>
</div>
<div class="sites-location-content-mobile-brick hidden-lg">
  <div class="container">
    <div class="row">
      <div class="col-sm-12 ">
        <div class="col-md-12">
          <h1><?php the_title(); ?></h1>
          <p><?php echo(types_render_field( "description", array( ) )); ?></p>
          <p><strong>Country:</strong> <?php echo(types_render_field( "country", array( ) )); ?></p>
          <p><strong>Field Documentation:</strong> <?php echo(types_render_field( "field-documentation", array( ) )); ?></p>
          <p><strong>Culture:</strong> <?php echo(the_category( ', ' )); ?></p>
          <p><strong>Historic Period:</strong> <?php echo(types_render_field( "historic-period", array( ) )); ?></p>
        </div>
        <div class="col-md-3">
          <p><strong>Latitude</strong></p>
          <p><?php echo(types_render_field( "latitude", array( ) )); ?></p>
        </div>
        <div class="col-md-3">
          <p><strong>Longitude</strong></p>
          <p><?php echo(types_render_field( "longitude", array( ) )); ?></p>
        </div>
        <div class="col-md-3">
          <p><strong>CDT</strong></p>
          <p><?php echo(types_render_field( "cdt", array( ) )); ?></p>
        </div>
        <div class="col-md-3">
          <p><strong>Datum Type</strong></p>
          <p><?php echo(types_render_field( "datum-type", array( ) )); ?></p>
        </div>
        <div class="col-sm-12 "><h3>Threat Level</h3></div>
        <?php
          $threatRender = types_render_field( "threat-level", array("output" => "raw" ) );
          $greenMobile = "<span class='fa fa-sort-desc fa-4x center' aria-hidden='true' style='color:#fff;margin-top:-50px;'></span>";
          $orangeMobile = "<span class='fa fa-sort-desc fa-4x center' aria-hidden='true' style='color:#fff;margin-top:-50px;'></span>";
          $redMobile =  "<span class='fa fa-sort-desc fa-4x center' aria-hidden='true' style='color:#fff;margin-top:-50px;'></span>";
        ?>
        <div class="col-xs-4 col-sm-4 center" style="background-color:green;height:20px;">

            <?php if ($threatRender == "0") {echo ($greenMobile);} else {echo("");} ?>

        </div>
        <div class="col-xs-4 col-sm-4 center" style="background-color:orange;height:20px;">

          <?php if ($threatRender == "1") {echo ($orangeMobile);} else {echo("");} ?>

        </div>
        <div class="col-xs-4 col-sm-4 center" style="background-color:red;height:20px;">

            <?php if ($threatRender == "2") {echo ($redMobile);} else {echo("");} ?>

        </div>
      </div>
    </div>
  </div>
</div>
<script>

</script>
<!-- Mobile tab navigation and content -->
  <div class="hidden-lg">
    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
      <?php if ($backgroundTabRender == null) {echo ("");}  else {echo("<div class='hidden-lg'>$backgroundMobileTab</div>");} ?>
            <div id="background-mobile" class="panel-collapse collapse hidden-lg" role="tabpanel" aria-labelledby="headingOne">
              <div class="panel-body">
                <div class="col-md-12 w-photo">
                  <?php echo($backgroundTabRender); ?>
                </div>
              </div>
            </div>
          </div>
      <?php if ($photoGalleryTabRender == null) {echo ("");}  else {echo("<div class='hidden-lg'>$photoGalleryMobileTab</div>");} ?>
          <div id="photo-gallery-mobile" class="panel-collapse collapse hidden-lg" role="tabpanel" aria-labelledby="headingTwo">
            <div class="panel-body">
              <?php  photoGallery($photosArray); ?>
            </div>
          </div>
        </div>
      <?php if ($MapTabRender == null) {echo ("");}  else {echo("<div class='hidden-lg'>$mapMobileTab</div>");} ?>
        <div id="map-mobile" class="panel-collapse collapse hidden-lg" role="tabpanel" aria-labelledby="headingThree">
          <div class="panel-body">
            <?php theMap($MapTabRender,$longitude,$latitude); ?>
          </div>
        </div>
      </div>
      <?php if ($captureTechniquesTabRender == null) {echo ("");}  else {echo("<div class='hidden-lg'>$captureTechniquesMobileTab</div>");} ?>
        <div id="capture-techniques-mobile" class="panel-collapse collapse hidden-lg" role="tabpanel" aria-labelledby="headingFour">
          <div class="panel-body">
            <?php echo($captureTechniquesTabRender); ?>
          </div>
        </div>
      </div>
      <?php if ($dataFilesTabRender == null) {echo ("");}  else {echo("<div class='hidden-lg'>$dataFilesMobileTab</div>");} ?>
        <div id="data-files-mobile" class="panel-collapse collapse hidden-lg" role="tabpanel" aria-labelledby="headingFive">
          <div class="panel-body">
            <?php echo(types_render_field( "data-details", array( ) )); ?>
            <?php dataLinks($dataNameArray, $dataFilesArray); ?>
          </div>
        </div>
      <?php if ($moviesTabRender == null) {echo ("");}  else {echo("<div class='hidden-lg'>$movieMobileTab</div>");} ?>
        <div id="movie-mobile" class="panel-collapse collapse hidden-lg" role="tabpanel" aria-labelledby="headingSix">
          <div class="panel-body">
            <?php youtubeVideo($video); ?>
          </div>
        </div>
      <?php if ($panoramaTabRender == null) {echo ("");}  else {echo("<div class='hidden-lg'>$panoramaMobileTab</div>");} ?>
        <div id="panorama-mobile" class="panel-collapse collapse hidden-lg" role="tabpanel" aria-labelledby="headingSeven">
          <div class="panel-body">
            <?php panoramalink(); ?>
          </div>
        </div>
      <?php if ($vrTabRender == null) {echo ("");}  else {echo("<div class='hidden-lg'>$vrMobileTab</div>");} ?>
        <div id="vr-mobile" class="panel-collapse collapse hidden-lg" role="tabpanel" aria-labelledby="headingEight">
          <div class="panel-body">
            <?php echo($vrTabRender); ?>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
  $('.tabcordion').tabcordion();
  $("img").addClass("img-responsive");
  $(".w-photo div").css("float", "right");
  $(".tabcordion li a").first().click();

  //hero content functions
  $('.content-btn').click(function() {
    $('.location-content').toggle();
    $('.content-btn').toggleClass('fa-angle-double-left').animate();
    $('.content-btn').toggleClass('fa-angle-double-right').animate();
    $('.location-content-holder').toggleClass('hero-content-open').animate();
    $('.location-content-holder').toggleClass('hero-content-closed').animate();
  });
</script>
<?php get_footer(); ?>
