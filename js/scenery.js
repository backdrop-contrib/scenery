(function ($) {

  'use strict';

  Backdrop.behaviors.scenery = {

    adjustOffset: function () {
      var headerHeight = $('.l-header').height();
      var menuHeight = $('.l-header .block-system-main-menu').height();
      var offset = Math.round(-1 * (headerHeight - menuHeight));
      if ($('html.admin-bar.admin-bar-sticky').length) {
        offset += 33;
      }
      if (menuHeight > $(window).height()) {
        $('.l-header').css({'position': 'relative', 'top': 'auto'});
        var top = $('.l-header .block-system-main-menu').offset().top + window.screenTop;
        $(window).scrollTop(top);
      }
      else {
        $('.l-header').css({'position': 'sticky', 'top': offset + 'px'});
      }
    },
    attach: function (context, settings) {
      $('html').removeClass('no-jscript');

      var menuInsideHeader = $('.l-header .block-system-main-menu').length;
      if (menuInsideHeader === 1) {
        Backdrop.behaviors.scenery.adjustOffset();

        var wResizeTimer;
        var mResizeTimer;
        $(window).resize(function () {
          clearTimeout(wResizeTimer);
          wResizeTimer = setTimeout(Backdrop.behaviors.scenery.adjustOffset, 250);
        });

        $('.l-header .sm .sub-arrow, .l-header .menu-toggle-button').click(function (event) {
          clearTimeout(mResizeTimer);
          mResizeTimer = setTimeout(Backdrop.behaviors.scenery.adjustOffset, 350);
        });

      }
    }

  }

})(jQuery);
