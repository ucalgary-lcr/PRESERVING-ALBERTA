<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
if(is_singular()){ wp_enqueue_script('comment-reply'); }
wp_head();
?>
<script src="https://use.fontawesome.com/a4a20cabef.js"></script>

</head>

<?php echo '<body class="'.join(' ', get_body_class()).'">'.PHP_EOL; ?>

  <header class="container-fluid">
     <div class="group" id="navigation">
       <div class="row uc-logo-container">
         <div class="container">
           <div class="row">
             <div class="col-sm-12">
               <div class="uc-logo">
                 <a href="https://ucalgary.ca/" target="_blank"><img src="https://libapps.ucalgary.ca/Springshare/src/img/Master/4-White/UC-horz-white.png"></a>
               </div>
             </div>
           </div>
         </div>
       </div>
       <div class="row uc-nav-container">
         <div class="container">
           <div class="row division">
             <div class="col-sm-12">
               <ul>
                 <li class="group">
                   <a  href="<?php echo home_url(); ?>" title="<?php echo get_bloginfo('description'); ?>"><?php bloginfo('name'); ?></a>
                 </li>
               </ul>
             </div>
           </div>
           <div class="row menus">
             <div class="col-sm-12">
               <div class="uc-nav">
                 <div class="uc-menuwrapper" id="uc-menu">
                   <button class="uc-trigger hamburger hamburger-htx" id="hamburger" type="button"><span class="sr-only">Toggle Navigation</span> <span class="glyphicon glyphicon-menu-hamburger"></span></button>


                   <ul class="level1 uc-menu uc-menu-toggle">
                     <?php
                       wp_nav_menu( array(
                         'menu'              => 'primary',
                         'theme_location'    => 'primary',
                         'depth'             => 2,
                         'container'         => 'div',
                         'container_class'   => 'navbar-collapse collapse',
                         'container_id'      => 'navbar',
                         'menu_class'        => 'nav navbar-nav navbar-right',
                         'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
                         'walker'            => new wp_bootstrap_navwalker())
                       );
                     ?>

                   </ul>
                 </div>


               </div>
             </div>
           </div>
         </div>
       </div>
     </div>
   </header>



   <?php if(get_header_image() != ''){ ?>
   <div id="header-image"><img class="img-responsive center-block" src="<?php header_image(); ?>"  height="<?php echo get_custom_header()->height; ?>" width="<?php echo get_custom_header()->width; ?>" alt="<?php bloginfo('name'); ?>"/></div>
   <?php } ?>
<main id="content">
