<div id="comments">
  <?php if(have_comments()){ ?>
  <hr>
  <h2><?php comments_number( __( 'Leave a comment', '' ) ); ?></h2>
  <ol class="commentlist">
    <?php wp_list_comments( 'avatar_size=40' ); ?>
  </ol>
  <?php
  // are there comments to navigate through
    if(get_comment_pages_count() > 1 && get_option('page_comments')){  ?>
    <nav id="comment-nav-below" class="navigation clearfix">
      <h1 class="assistive-text"><?php _e('Comment navigation', ''); ?></h1>
      <span class="nav-previous"><?php previous_comments_link( __('&larr; Older Comments', '')); ?></span>
      <span class="nav-next"><?php next_comments_link( __('Newer Comments &rarr;', '')); ?></span>
    </nav>
  <?php }}
  // Comment form setup
  $args = array(
    'fields' => apply_filters(
     'comment_form_default_fields', array(

       // Author
      'author' =>'<p class="comment-form-author"><label for="author">'. __('Your Name', '').'</label>
      <input class="form-control" id="author" placeholder="Your Name (No Keywords)" name="author" type="text" value="'.esc_attr( $commenter['comment_author'] ) .'" size="30"'.$aria_req.'/></p>',

      // Email
     'email' => '<p class="comment-form-email"><label for="email">'. __('Your Email', '').'</label>
      <input class="form-control" id="email" placeholder="your-real-email@example.com" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ).'" size="30"'.$aria_req.'/></p>',

      // URL
      'url' => '<p class="comment-form-url"><label for="url">' . __('Website', '') . '</label>
      <input class="form-control" id="url" name="url" placeholder="http://your-site-name.com" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>'
     )
    ),

    // Comments
    'comment_field' => '<p class="comment-form-comment"><label for="comment">' . __('Let us know what you have to say:', '') . '</label>
    <textarea class="form-control" id="comment" name="comment" placeholder="Express your thoughts, idea or write a feedback by clicking here & start an awesome comment" cols="45" rows="8" aria-required="true"></textarea></p>',
    'comment_notes_after' => '',
    'title_reply' => 'Please Post Your Comments & Reviews'
  );

  // Form config
  ob_start();
  comment_form($args);
  $form = ob_get_clean();
  $form = str_replace('class="comment-form"','class="comment-form my-class"', $form);
  echo str_replace('class="submit"','class="btn btn-primary"', $form);
  ?>
</div>
