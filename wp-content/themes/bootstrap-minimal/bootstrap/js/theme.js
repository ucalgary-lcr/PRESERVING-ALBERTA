/*
 * Theme.js - Core JavaScript library for the UCalgary Theme
 */

// Mobile device detection
var isMobile = false;
if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)|| /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))){isMobile=true};

// Test if link is off UCalgary domain
function is_external(link_obj) {
    if (typeof link_obj !== 'undefined') {
        var hostname = $(link_obj).attr('href');
        if (typeof hostname !== 'undefined' && hostname != false) {
            return (link_obj.hostname != window.location.hostname);
        }
    }
    return false;
}

// URL query parser
var urlParams;
(window.onpopstate = function () {
    var match,
        pl = /\+/g,  // Regex for replacing addition symbol with a space
        search = /([^&=]+)=?([^&]*)/g,
        decode = function (s) {
            return decodeURIComponent(s.replace(pl, " "));
        },
        query = window.location.search.substring(1);
    urlParams = {};
    while (match = search.exec(query))
        urlParams[decode(match[1])] = decode(match[2]);
})();

(function ($) {

    $(document).ready(function () {

        //
        // Initialization
        //

        // Convert linked SVG files to inline for CSS/JS manipulation
        $("img.svg").svgInject(); // XML GENERATES A FIREFOX "not well-formed" ERROR

        // Activate Tabcordion functionality
        $('.tabcordion').tabcordion({breakWidth: 768, delay: 200});

        // Enable dismissability in iOS devices
        if ('ontouchstart' in document.documentElement && isMobile) {
            $('[data-toggle="popover"]').on({
                "shown.bs.popover" : function() {
                    $('body').css('cursor', 'pointer');
                },
                "hide.bs.popover" : function() {
                    $('body').css('cursor', 'auto');
                }
            });
            $('[data-toggle="tooltip"]').on({
                "shown.bs.tooltip" : function() {
                    $('body').css('cursor', 'pointer');
                },
                "hide.bs.tooltip" : function() {
                    $('body').css('cursor', 'auto');
                }
            });
        }

        // Activate Owl Carousel functionality
        $(".timelines .carousel").owlCarousel({
            navigation: true,
            navigationText: ['&lang;', '&rang;'],
            navigationText: ['<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>', '<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>'],
            scrollPerPage: true,
            rewindNav: false
        });

        // Opt-in to Bootstrap Tooltop / Popover functionality
        $('[data-toggle="popover"]').popover();
        $('[data-toggle="tooltip"]').tooltip();

        // Resets button states when mousing out (webkit)
        $(document).on("mouseleave",".btn",function(){
            $(this).blur();
        })


        //
        // UCalgary menu initialization
        //

        $('#uc-menu').dlmenu({
            animationClasses : { classin : 'uc-animate-in-2', classout : 'uc-animate-out-2' }
        });


        //
        // Parallax image control
        //

        var parallaxDegree = 4;
        function updateParallaxPosition() {
            var currPos = $(".hero-cta.top.parallax > .row").first().css("background-position").split(" ");
            var xPos = currPos[0]; // Existing horizontal background position
            var yPos = $(this).scrollTop() / parallaxDegree;  // Updated vertical parallax position
            $(".hero-cta.top.parallax > .row").first().css("background-position", xPos + " " + yPos + "px");
        }

        // Test if we have defined parallax images
        if ($(".hero-cta.top.parallax").length) {
            updateParallaxPosition(); // Inital position update
            $(window).scroll(function () {
                updateParallaxPosition(); // Update position on scroll
            });
        }


        //
        // Toolbox controls
        //

        // General toolbox display toggle
        $('#toolbox button.btn.btn-rounded').on('click', function() {
            $('#toolbox .row.button .btn').toggleClass("btn-tab"); // Toggle tab display
            $('#toolbox .row.content').slideToggle(); // Show/hide 'drawer'
        });

        // Global search toolbox control
        $("#search-desktop input").on("focus", function () {
            $('#toolbox .row.button .btn').removeClass("btn-tab"); // Disable tab display
            $('#toolbox .row.content').slideDown(); // Show 'drawer'
        });

        // Global search form submission
        $("#search-desktop,#search-mobile").submit(function (event) {
            $('#toolbox .row.button .btn').addClass("btn-tab"); // Enable tab display
            $('#toolbox .row.content').slideUp(); // Hide 'drawer'
            this.form.submit();
        });


        //
        // Bootstrap Menu Animation
        //

        $('.dropdown').on('show.bs.dropdown', function (e) {
            $(this).find('.dropdown-menu').first().stop(true, true).slideDown();
        });

        $('.dropdown').on('hide.bs.dropdown', function (e) {
            $(this).find('.dropdown-menu').first().stop(true, true).slideUp();
        });


        //
        // Make News/Events clickable
        //

        $(".event-item .event-date, .news-item .news-thumb").click(function () {
            window.location = $(this).parent().find("p.title a").attr("href");
            return false;
        });


        //
        // Make Program Tiles clickable
        //

        $(".program-tile-small, .program-tile-large, .grad-program-tile, .program-star .btn").click(function(event) {
            var target = event.target.nodeName.toLowerCase();
            if((target!="button")&&(target!="span")){ // !Comparison Star
                window.location = $(this).find("a").attr("href");
                return false;
            }
        });


        //
        // Make select elements consistant in height
        //

        // Update tab / tab content heights
        $('.brick.tabs .nav-tabs li a').matchHeight();
        $('.brick.tabs .tab-content .tab-pane').matchHeight(false);


        //
        // Continue list item count across multiple lists/bricks
        //

        var ucgy_ol_primary = $('ol.primary');
        if (ucgy_ol_primary) {
            var primary_length = $(ucgy_ol_primary).find('li').length;
            $('ol').each(function() {
                if ($(this).hasClass('continue')) {
                    $(this).css('counter-reset', 'item ' + primary_length);
                }
            });
        }


        //
        // Contextual links & publish/unpublish info
        //

        // Create a container. Use Append: This introduces spacing issues but has been fixed via CSS logic
        $('.logged-in .brick').each(function(){
            $(this).addClass('contextual-links-region');
            $(this).append('<div class="brick-editing"></div>');
        });

        // Move location of publish/unpublish info links to within fieldable panel panes
        $('.publishing-links-wrapper').each(function () {
            $(this).nextAll('.brick').first().find('.brick-editing').append(this);
        });

        // Move location of contextual links to within fieldable panel panes
        $('.contextual-links-wrapper, .publishing-links-wrapper').each(function () {
            $(this).nextAll('.brick').first().find('.brick-editing').append(this);
        });


        //
        // Remove those pesky empty <p>'s!
        //

        // $('p').each(function() {
        //     if($(this).html().replace(/\s|&nbsp;/g, '').length == 0) {
        //         $(this).remove();
        //     }
        // });


        //
        // Ajax spinner
        //

        $(document).ajaxStart(function () {
            // console.log('ajaxStart');
            $('.loading').show();
        });

        // $(document).ajaxSend(function( event, jqxhr, settings ) {
        //   console.log('ajaxSend');
        //   console.log(settings.url);
        // });

        $(document).ajaxStop(function () {
            // console.log('ajaxStop');
            $('.loading').hide();
            $('[data-toggle="popover"]').each(function (idx) {
                $(this).attr('tabindex', idx).popover();
            });
            $('[data-toggle="tooltip"]').tooltip();
        });

    });

    $(window).load( function() {

        // Reposition header if logged in (especially important on mobile as admin menu bunches up (really should have mobile-friendly admin menu))

        if($('body').hasClass('logged-in')) {
            $('header').css({'top': $('#admin-menu').height()}) // Should re-check on window resize
        }

        // Special handling for group/sub-group menus

        if ($('#navigation').hasClass('group')) {
            var originalOffset = $('header').offset().top; // Admin menu pushes header down...
            $('body').on("scroll", function() {
                var vertScrollPos = $(this).scrollTop() - originalOffset;
                var menuOffsetPos = ( $('#global-alert').height() + $('#toolbox').height() + $('#navigation .uc-logo-container').height() ) - originalOffset;
                var y = ( vertScrollPos <= menuOffsetPos ) ? vertScrollPos : menuOffsetPos;
                $('header').css({'top': -y});
            });
        }

    });

})(jQuery);
