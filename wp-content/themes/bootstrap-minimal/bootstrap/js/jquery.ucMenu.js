/**
 * ucMenu Based on:
 *
 * jquery.dlmenu.js v1.0.1
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright 2013, Codrops
 * http://www.codrops.com
 */

(function($, window, undefined) {

  'use strict';

  // global
  var Modernizr = window.Modernizr;

  $.DLMenu = function(options, element) {
    this.$el = $(element);
    this._init(options);
  };

  // the options
  $.DLMenu.defaults = {
    // classes for the animation effects
    animationClasses : { classin : 'uc-animate-in-1', classout : 'uc-animate-out-1' },
    // callback: click a link that has a sub menu
    // el is the link element (li); name is the level name
    onLevelClick : function(el, name) { return false; },
    // callback: click a link that does not have a sub menu
    // el is the link element (li); ev is the event obj
    onLinkClick : function(el, ev) { return false; }
  };

  $.DLMenu.prototype = {
    _init : function(options) {

      // options
      this.options = $.extend(true, {}, $.DLMenu.defaults, options);
      // cache some elements and initialize some variables
      this._config();

      var animEndEventNames = {
          'WebkitAnimation' : 'webkitAnimationEnd',
          'OAnimation' : 'oAnimationEnd',
          'msAnimation' : 'MSAnimationEnd',
          'animation' : 'animationend'
        },
        transEndEventNames = {
          'WebkitTransition' : 'webkitTransitionEnd',
          'MozTransition' : 'transitionend',
          'OTransition' : 'oTransitionEnd',
          'msTransition' : 'MSTransitionEnd',
          'transition' : 'transitionend'
        };
      // animation end event name
      this.animEndEventName = animEndEventNames[ Modernizr.prefixed('animation') ] + '.dlmenu';
      // transition end event name
      this.transEndEventName = transEndEventNames[ Modernizr.prefixed('transition') ] + '.dlmenu',
      // support for css animations and css transitions
      this.supportAnimations = Modernizr.cssanimations,
      this.supportTransitions = Modernizr.csstransitions;

      this._initEvents();
    },
    _config : function() {
      this.open = false;
      this.$trigger = this.$el.children('.uc-trigger');
      this.$menu = this.$el.children('ul.uc-menu');
      this.$menuitems = this.$menu.find('li:not(.uc-back)');

      this.$el.find('ul.uc-submenu').each(function() {
        // Get the text of the parent menu item for the 'back' button
        $(this).prepend('<li class="uc-back"><a href="#">' + $(this).prev('a').text() + '</a></li>');
      })
      this.$back = this.$menu.find('li.uc-back');

      this.menuBreakpoint = 992;
      this.currentViewportSize = this._getViewportSize();
      this.currentViewportType = this._getViewportType();
    },
    _initEvents : function() {

      var self = this;
      var resizeTimer;

      $(window).on('resize', function() {
          // Throttle resize events
          clearTimeout(resizeTimer);
          resizeTimer = setTimeout(function() {
            self._adjustMenuType(self);
          }, 250);
      });

      // Hamburger
      this.$trigger.on('click.dlmenu', function() {

        if (self.open) {
          self._closeMenu();
        }
        else {
          self._openMenu();
        }
        return false;
      });

      // Menu items
      this.$menuitems.on('click.dlmenu', function(event) {

        event.stopPropagation();

        var $item = $(this),
            $submenu = $item.children('ul.uc-submenu');

        if ($submenu.length > 0) {

          if ($(event.target).attr('href') == '#') {
            event.preventDefault(); // Prevent hash (#) from appearing in address bar
          }

          // Test if displaying dekstop or mobile menu

          if (self._getViewportSize().width >= self.menuBreakpoint) { // Desktop Menu

            // Clicking on main menu item?
            if ($item.parent().hasClass('level1')) {
              if ($item.hasClass('active')) {
                // Close this item's dropdown
                self._closeDesktopMenu();
              }
              else if ($('.uc-mainmenu').hasClass('active')) {
                // Close existing dropdown and open this item's dropdown
                self._closeDesktopMenu(function () {
                   self._openDesktopMenu($item, $submenu);
                });
              }
              else {
                // Open this item's dropdown
                self._openDesktopMenu($item, $submenu);
              }
            }
            // Clicking on tabbed menu item?
            else if ($item.parent().hasClass('level2')) {

              // Disable all currently active tab content
              $('ul.level2 .active').removeClass('active');

              // Display the desired tab content
              $item.addClass('active'); // Add highlight - tabbed nav
              $('ul.uc-submenu',$item).first().addClass('active'); // Enable visibility
            }

            return;
          }
          else { // Mobile Menu

            var $flyin = $submenu.clone().css('opacity', 0).insertAfter(self.$menu),
              onAnimationEndFn = function() {
                self.$menu.off(self.animEndEventName).removeClass(self.options.animationClasses.classout).addClass('uc-subview');
                $item.addClass('uc-subviewopen').parents('.uc-subviewopen:first').removeClass('uc-subviewopen').addClass('uc-subview');
                $flyin.remove();
              };

            setTimeout(function() {
              $flyin.addClass(self.options.animationClasses.classin);
              self.$menu.addClass(self.options.animationClasses.classout);
              if (self.supportAnimations) {
                self.$menu.on(self.animEndEventName, onAnimationEndFn);
              }
              else {
                onAnimationEndFn.call();
              }

              self.options.onLevelClick($item, $item.children('a:first').text());
            });

            return false;
          }

        }
        else {
          self.options.onLinkClick($item, event);
        }
      });

      // 'Back' links
      this.$back.on('click.dlmenu', function(event) {

        var $this = $(this),
          $submenu = $this.parents('ul.uc-submenu:first'),
          $item = $submenu.parent(),

          $flyin = $submenu.clone().insertAfter(self.$menu);

        var onAnimationEndFn = function() {
          self.$menu.off(self.animEndEventName).removeClass(self.options.animationClasses.classin);
          $flyin.remove();
        };

        setTimeout(function() {
          $flyin.addClass(self.options.animationClasses.classout);
          self.$menu.addClass(self.options.animationClasses.classin);
          if (self.supportAnimations) {
            self.$menu.on(self.animEndEventName, onAnimationEndFn);
          }
          else {
            onAnimationEndFn.call();
          }

          $item.removeClass('uc-subviewopen');

          var $subview = $this.parents('.uc-subview:first');
          if ($subview.is('li')) {
            $subview.addClass('uc-subviewopen');
          }
          $subview.removeClass('uc-subview');
        });

        return false;
      });

    },
    closeMenu : function() {
      if (this.open) {
        this._closeMenu();
      }
    },
    _closeMenu : function() {
      var self = this,
        onTransitionEndFn = function() {
          self.$menu.off(self.transEndEventName);
          self._resetMenu();
        };

      this.$menu.removeClass('uc-menuopen');
      this.$menu.addClass('uc-menu-toggle');
      this.$trigger.removeClass('is-active');

      if (this.supportTransitions) {
        this.$menu.on(this.transEndEventName, onTransitionEndFn);
      }
      else {
        onTransitionEndFn.call();
      }

      this._removeCloseListener();
      this.open = false;
    },
    _closeDesktopMenu : function(callBack) {
      this.open = false;

      $('.level2, #uc-menu-dropdown > .row').css('height', '0').on(this.transEndEventName, function(event) {
        $('.level1 .active').removeClass('active'); // Reset all active links
        $('.level2, #uc-menu-dropdown > .row').off(this.transEndEventName); // Remove transition event listener
        if (callBack) {
          callBack(); // Will open a new menu
        }
      }).css('height', ''); // Nullify the height value
    },
    openMenu : function() {
      if (!this.open) {
        this._openMenu();
      }
    },
    _openMenu : function() {
      this.$menu.addClass('uc-menuopen uc-menu-toggle').on(this.transEndEventName, function() {
        $(this).removeClass('uc-menu-toggle');
      });
      this.$trigger.addClass('is-active');

      this._addCloseListener();
      this.open = true;

      // Automatically open with current location (if deeper than main menu)
      if ($('ul.level2, ul.level3').hasClass('current')) {
        $('li.current', '#navigation').last().parents('ul.level1, li').addClass('uc-subview');
        $('li.current', '#navigation').last().parent().closest('li.current').removeClass('uc-subview').addClass('uc-subviewopen');
      }
    },
    _openDesktopMenu : function($menu, $submenu) {
      this.open = true;

      $menu.addClass('active'); // Add highlight to main nav (li.uc-mainmenu)
      $submenu.addClass('active'); // Enable dropdown's visibility (ul.level2.uc-submenu)
      // Check if current location specified
      if ($('li.children.current',$submenu).length) {
        // Currently active tab
        $('li.children.current',$submenu).addClass('active'); // Add highlight to current tabbed nav
        $('li.children.current ul.uc-submenu',$submenu).addClass('active'); // Enable tab's visibility
      }
      else {
        // Default first tab
        $('li.children:first',$submenu).addClass('active'); // Add highlight to first tabbed nav
        $('li.children ul.uc-submenu:first',$submenu).addClass('active'); // Enable tab's visibility
      }
      // Set the height of the background 'drawer' and the menu list item (for CSS animation)
      $('.level2, #uc-menu-dropdown > .row').css('height', this._getDropdownHeight($submenu.children('li.children')));
      this._addCloseListener();
    },
    _addCloseListener : function () {
      var self = this;
      $('body').on('click', function(e) { // Add touchstart? Would mess with mobile scrolling with window open...
        var container = self.$el; // clicking somewhere else makes the menu close
        if (!container.is(e.target) // if the target of the click isn't the container...
            && container.has(e.target).length === 0) // ... nor a descendant of the container
        {
            self._closeMenu();
            if (self._getViewportSize().width >= self.menuBreakpoint) {
              // Additional menu resets required
              self._closeDesktopMenu();
            }
        }
      });
    },
    _removeCloseListener : function () {
      $('body').off('click'); // Add touchstart? Would mess with mobile scrolling with window open...
    },
    _adjustMenuType : function() {
      var self = this;
      var changedViewportSize = self._getViewportSize();
      // Check if size has actually changed
      if (changedViewportSize.width != this.currentViewportSize.width) {
        // Store the new value
        this.currentViewportSize = changedViewportSize;
        // Size has changed, so now see if it has changed enough to force change in menu style
        var changedViewportType = self._getViewportType();
        if (this.currentViewportType == 'mobile' && changedViewportType == 'desktop') {
          if (this.open) {
            // Transition display from mobile to desktop
            $('.uc-nav .uc-subview').removeClass('uc-subview');
            $('.uc-nav .uc-subviewopen').removeClass('uc-subviewopen');
            $('.level2, #uc-menu-dropdown > .row').css('height', this._getDropdownHeight($('ul.level2.active li.children')));
          }
          // Store the new value
          this.currentViewportType = changedViewportType;
        }
        else if (this.currentViewportType == 'desktop' && changedViewportType == 'mobile') {
          if (this.open) {
            // Transition display from desktop to mobile
            $('.uc-nav .uc-subview').addClass('uc-subview');
            $('.uc-nav .uc-subviewopen').addClass('uc-subviewopen');
            $('.level2').css('height', 'auto'); // #uc-menu-dropdown is hidden at this point
          }
          // Store the new value
          this.currentViewportType = changedViewportType;
        }
      }
    },
    // get the base height for all of the desktop links
    _getDropdownHeight : function($submenus) {
      var minHeight = 260;
      var totalHeight = 0;
      // Find tallest tabbed list section
      $submenus.each(function (index, value) {
        var height = $(this).find('ul.level3.uc-submenu:first').outerHeight(true);
        if (height > totalHeight) {
          totalHeight = height;
        }
      });
      // Add top position of tabbed list to the height
      totalHeight += $submenus.find('ul.level3.uc-submenu:first').css('top');
      return (totalHeight > minHeight) ? totalHeight : minHeight;
    },
    // get the viewport size (closest to CSS media query values as we can get)
    _getViewportSize : function() {
      var e = window, a = 'inner';
      if (!('innerWidth' in window)) {
        a = 'client';
        e = document.documentElement || document.body;
      }
      return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
    },
    // get the viewport type (mobile or desktop)
    _getViewportType : function() {
      return (this._getViewportSize().width < this.menuBreakpoint) ? 'mobile' : 'desktop';;
    },
    // resets the menu to its original state (first level of options)
    _resetMenu : function() {
      this.$menu.removeClass('uc-subview');
      this.$menuitems.removeClass('uc-subview uc-subviewopen');
    }
  };

  var logError = function(message) {
    if (window.console) {
      window.console.error(message);
    }
  };

  $.fn.dlmenu = function(options) {
    if (typeof options === 'string') {
      var args = Array.prototype.slice.call(arguments, 1);
      this.each(function() {
        var instance = $.data(this, 'dlmenu');
        if (!instance) {
          logError("cannot call methods on dlmenu prior to initialization; attempted to call method '" + options + "'");
          return;
        }
        if (!$.isFunction(instance[options]) || options.charAt(0) === "_") {
          logError("no such method '" + options + "' for dlmenu instance");
          return;
        }
        instance[options].apply(instance, args);
      });
    }
    else {
      this.each(function() {
        var instance = $.data(this, 'dlmenu');
        if (instance) {
          instance._init();
        }
        else {
          instance = $.data(this, 'dlmenu', new $.DLMenu(options, this));
        }
      });
    }

    return this;
  };

})(jQuery, window);
