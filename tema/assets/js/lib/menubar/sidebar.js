
/*
 *
 * sidebar sidebar
 *
 */
!function($) {
  "use strict";

  var sidebar = function(element, options) {
    this.options    = options;
    this.$sidebar       = $(element);
    this.$content   = this.$sidebar.find('~ .content-wrap');
    this.$nano      = this.$sidebar.find(".nano");
    this.$html      = $('html');
    this.$body      = $('body');
    this.$window    = $(window);

    // set in true when first time were clicked on toggle button
    this.changed    = false;

    this.init();
  };

  sidebar.DEFAULTS = {
    // duration od animations
    duration: 300,

    // set small sidebar when window width < resizeWnd
    resizeWnd: 1000
  };

  sidebar.prototype.init = function() {
    var _this = this;

    // no transition enable
    _this.$body.addClass('sidebar-notransition');

    // init Nano Scroller
    _this.$nano.nanoScroller({ preventPageScrolling: true });

    // sidebar toggle
    $('.sidebar-toggle').on( 'click', function(e) {
      e.preventDefault();
      _this.togglesidebar();
    });

    // hide sidebar when push content overlay
    _this.$content.on( 'click', function() {
      if( _this.isHideOnContentClick() ) {
        _this.hidesidebar();
      }
    })

    // toggle sub menus
    _this.$sidebar.on('click', 'li a.sidebar-sub-toggle', function(e) {
      e.preventDefault();
      _this.toggleSub($(this));
    });

    if( _this.showType() == 'push' && _this.isShow()) {
      _this.$body.css('overflow', 'hidden');
    }

    // init gesture swipes
    if( _this.$sidebar.hasClass('sidebar-gestures') ) {
      _this.useGestures();
    }

    // on window resize - set small sidebar
    _this.$window.on('resize', function() {
      _this.windowResize();
    });

    _this.windowResize();

    // no transition disable
    setTimeout(function() {
      _this.$body.removeClass('sidebar-notransition');
    }, 1);
  }

  sidebar.prototype.isShow = function() {
    return !this.$body.hasClass('sidebar-hide');
  }

  // check show type
  sidebar.prototype.showType = function() {
    if(this.$sidebar.hasClass('sidebar-overlay')) return 'overlay';
    if(this.$sidebar.hasClass('sidebar-push')) return 'push';
    if(this.$sidebar.hasClass('sidebar-shrink')) return 'shrink';
  };


  // check if hide on content click
  sidebar.prototype.isHideOnContentClick = function() {
    return this.$sidebar.hasClass('sidebar-overlap-content');
  }

  // check if sidebar static position
  sidebar.prototype.isStatic = function() {
    return this.$sidebar.hasClass('sidebar-static');
  }


  sidebar.prototype.togglesidebar = function(type) {
    var _this = this;
    var show = !_this.isShow();

    if(type) {
      if(
        (type=='show' && !show)
        || (type=='hide' && show)) {
        return;
      }
    }

    _this.options.changed = true;

    if( show ) {
      _this.showsidebar();
    } else {
      _this.hidesidebar();
    }
  }

  sidebar.prototype.showsidebar = function() {
    var _this = this;

    _this.$body.removeClass('sidebar-hide');

    if( _this.showType() == 'push'/* && !_this.isStatic() */) {
      _this.$body.css('overflow', 'hidden');
    }

    setTimeout(function() {
      // restore scroller on normal sidebar after end animation (300ms)
      _this.$nano.nanoScroller();

      // resize for charts reinit
      _this.$window.resize();
    }, _this.options.duration);
  }

  sidebar.prototype.hidesidebar = function() {
    var _this = this;

    _this.$body.addClass('sidebar-hide');

    // destroy scroller on hidden sidebar
    _this.$nano.nanoScroller({ destroy: true });

    // resize for charts reinit
    setTimeout(function() {
      if( _this.showType() == 'push'/* && !_this.isStatic() */) {
        _this.$body.css('overflow', 'visible');
      }
      _this.$window.resize();
    }, _this.options.duration);
  }


  // toggle submenu [open or close]
  sidebar.prototype.toggleSub = function(toggle) {
    var _this = this;

    var toggleParent = toggle.parent();
    var subMenu = toggleParent.find('> ul');
    var opened = toggleParent.hasClass('open');

    if(!subMenu.length) {
      return;
    }

    // close
    if(opened) {
      _this.closeSub(subMenu);
    }

    // open
    else {
      _this.openSub(subMenu, toggleParent);
    }
  }

  // close submenus
  sidebar.prototype.closeSub = function(subMenu) {
    var _this = this;

    subMenu.css('display', 'block').stop()
      .slideUp(_this.options.duration, 'swing', function() {
      // close child dropdowns
      $(this).find('li a.sidebar-sub-toggle').next().attr('style', '');

      // reinit nano scroller
      _this.$nano.nanoScroller();
    });

    subMenu.parent().removeClass('open');
    subMenu.find('li a.sidebar-sub-toggle').parent().removeClass('open');
  }

  // open submenus
  sidebar.prototype.openSub = function(subMenu, toggleParent) {
    var _this = this;

    subMenu
      .css('display', 'none').stop()
      .slideDown(_this.options.duration, 'swing', function() {
        // reinit nano scroller
        _this.$nano.nanoScroller();
      });
    toggleParent.addClass('open');

    _this.closeSub( toggleParent.siblings('.open').find('> ul') );
  }

  // use gestures for show / hide menu
  sidebar.prototype.useGestures = function() {
    var _this = this;
    var touchStart = 0;
    var startPoint = 0; // x position
    var endPoint = 0; // x position

    // on touch start
    _this.$window.on('touchstart', function(e) {
      startPoint = (e.originalEvent.touches?e.originalEvent.touches[0]:e).pageX;
      endPoint = (e.originalEvent.touches?e.originalEvent.touches[0]:e).pageX;
      touchStart = 1;
    });

    // on swipe start
    _this.$window.on('touchmove', function(e) {
      if( touchStart ) {
        endPoint = (e.originalEvent.touches?e.originalEvent.touches[0]:e).pageX;
      }
    });

    // on swipe end
    _this.$window.on('touchend', function(e) {
      if( touchStart ) {
        var resultSwipe = startPoint - endPoint,
            rtl = _this.$html.hasClass('rtl');

        touchStart = 0;

        // swipe min width 100px
        if( Math.abs( resultSwipe ) < 100 ) {
          return;
        }

        // change values if rtl
        if( rtl ) {
          resultSwipe *= -1;
          startPoint = _this.$window.width() - startPoint;
        }

        // from left to right
        if(resultSwipe < 0) {
          // show only when touch started from left corner
          if( startPoint < 40 ) {
            _this.showsidebar();
          }
        }

        // from right to left
        else {
          _this.hidesidebar();
        }
      }
    });
  }

  // on resize window and on start
  var resizeTimer;
  sidebar.prototype.windowResize = function() {
    var _this = this;

    // if user currently changed size of sidebar, stop change it
    if(!_this.options.changed) {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function() {
        if(_this.$window.width() < _this.options.resizeWnd) {
          _this.togglesidebar('hide');
        }
      }, 50);
    }
  };




  // init
  $('.sidebar').each(function() {
    var options = $.extend({}, sidebar.DEFAULTS, $(this).data(), typeof option == 'object' && option);
    var cursidebar = new sidebar(this, options);
  });

}(jQuery);
