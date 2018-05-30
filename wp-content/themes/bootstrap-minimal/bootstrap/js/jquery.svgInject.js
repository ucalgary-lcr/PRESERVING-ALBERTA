/*
  svgInject - v1.0.0
  jQuery plugin for replacing img-tags with SVG content
  by Robert Bue (@robert_bue)

  Dual licensed under MIT and GPL.
 */

;(function($, window, document, undefined) {
  var pluginName = 'svgInject';

  function Plugin(element, options) {
    this.element = element;
    this.callback = options;
    this._name = pluginName;
    this.init();
  }
  Plugin.prototype = {
    init: function() {
      $(this.element).css('visibility', 'hidden');
      this.injectSVG(this.element, this.callback);
    },
    injectSVG: function(el, callback) {
      var imgURL = $(el).attr('src');
      var imgID = $(el).attr('id');
      var imgClass = $(el).attr('class');
      var imgData = $(el).clone(true).data();
      var dimensions = {
        w: $(el).attr('width'),
        h: $(el).attr('height')
      };
      $.get(imgURL, function(data) {
        var svg = $(data).find('svg');
        if (typeof imgID !== undefined) {
          svg = svg.attr('id', imgID);
        }
        if (typeof imgClass !== undefined) {
          var cls = (svg.attr('class') !== undefined) ? svg.attr('class') : '';
          svg = svg.attr('class', imgClass + ' ' + cls + ' injected-svg');
        }
        if (typeof imgURL !== undefined) {
          svg = svg.attr('data-url', imgURL);
        }
        $.each(imgData, function(name, value) {
          svg[0].setAttribute('data-' + name, value);
        });
        svg = svg.removeAttr('xmlns:a');
        var ow = parseFloat(svg.attr('width'));
        var oh = parseFloat(svg.attr('height'));
        if (dimensions.w && dimensions.h) {
          $(svg).attr('width', dimensions.w);
          $(svg).attr('height', dimensions.h);
        } else if (dimensions.w) {
          $(svg).attr('width', dimensions.w);
          $(svg).attr('height', (oh / ow) * dimensions.w);
        } else if (dimensions.h) {
          $(svg).attr('height', dimensions.h);
          $(svg).attr('width', (ow / oh) * dimensions.h);
        }
        $(el).replaceWith(svg);
        var js = new Function(svg.find('script').text());
        js();

        if (typeof callback === 'function') {
          $(el).ready(function() {
            callback();
          });
        }
      });
    }
  };
  $.fn[pluginName] = function(options) {
    return this.each(function() {
      if (!$.data(this, 'plugin_' + pluginName)) {
        $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
      }
    });
  };
})(jQuery, window, document);