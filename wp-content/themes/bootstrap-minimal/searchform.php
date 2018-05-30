<?php
// Default search form
?>
<form class="form-inline" method="get" id="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
  <div class="input-group input-group-lg">
    <input type="search" class="form-control" style="width:100% !important;" placeholder="Search" name="s" id="search-input" value="<?php echo esc_attr(get_search_query()); ?>" />
    <span class="input-group-btn">
      <button class="btn btn-default maroon-back" type="button"><i class="fa fa-search" id="search-submit" value="Search"></i></button>
    </span>
  </div>
</form>
