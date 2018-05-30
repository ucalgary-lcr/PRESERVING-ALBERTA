<?php

class WPV_ViewsHelpVideos extends Toolset_HelpVideosFactoryAbstract{
    protected function define_toolset_videos(){
        $videos = array(
            'views_template' =>  array(
                'name' => 'views_template',
                'url' => 'http://d7j863fr5jhrr.cloudfront.net/toolset-views-templates.mp4',
                'screens' => array('toolset_page_ct-editor'),
                'element' => '.toolset-video-box-wrap',
                'title' => __('Creating a template with Views', 'wpv-views'),
                'width' => '900px',
                'height' => '506px'
            ),
            'archive_views' =>  array(
                'name' => 'views_archives',
                'url' => 'http://d7j863fr5jhrr.cloudfront.net/toolset-custom-post-type-archives.mp4',
                'screens' => array('toolset_page_view-archives-editor'),
                'element' => '.toolset-video-box-wrap',
                'title' => __('Creating an archive with WordPress Archive', 'wpv-views'),
                'width' => '900px',
                'height' => '506px'
            ),
            'views_view' =>  array(
                'name' => 'views_view',
                'url' => 'http://d7j863fr5jhrr.cloudfront.net/toolset-views-lists-of-content.mp4',
                'screens' => array('toolset_page_views-editor'),
                'element' => '.toolset-video-box-wrap',
                'title' => __('Creating and displaying a View', 'wpv-views'),
                'width' => '900px',
                'height' => '506px'
            )
        );

	    // Avada / Divi adjustments
        if( defined( 'AVADA_VERSION') || defined( 'ET_CORE' ) ) {
	        // disable CT and WPA video
        	unset( $videos['views_template'] );
        	unset( $videos['archive_views'] );
        }

        return $videos;
    }
}
add_action( 'init', array("WPV_ViewsHelpVideos", "getInstance"), 9 );