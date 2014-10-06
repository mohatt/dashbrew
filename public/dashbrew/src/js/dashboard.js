;(function ($, window, document, undefined) {

  // Initiate sidebar
  $(document).ready(function(){
    var minwidth = 992,
        $toggler = $('#toggle-sidebar'),
        $wrapper = $('#page-wrapper');

    $toggler.click(function () {
      $wrapper.toggleClass('active');
      if($wrapper.hasClass('active'))
        $.cookie('sb-active', '1');
      else
        $.cookie('sb-active', '0');
    });

    if($.cookie('sb-active') == undefined)
      $.cookie('sb-active', '1');

    var $rs = function () {
      if($(window).width() <= minwidth)
        $wrapper.removeClass('active');
      else
        $.cookie('sb-active') == "1" && $wrapper.addClass('active');
    };

    $rs();
    $(window).resize($rs);
  });

  function Widget(el, options) {

    var defaults = {
      title: null,
      url: '',
      size: null,
      class: null,
      onSuccess: null
    };

    var $opts = $.extend( {}, defaults, options);
    var $el  = $(el);

    $el.addClass('widget');

    if($opts.title != null){
      $('<div class="widget-header">' +
           '<button class="btn btn-sm btn-default pull-right">' +
             '<span class="glyphicon glyphicon-refresh"></span>' +
           '</button>' +
           $opts.title +
        '</div>')
        .appendTo($el);
    }

    $('<div class="widget-body">' +
         '<div class="widget-data"></div>' +
         '<div class="loading">' +
            '<div class="line"></div>' +
            '<div class="break dot1"></div>' +
            '<div class="break dot2"></div>' +
            '<div class="break dot3"></div>' +
         '</div>' +
      '</div>')
      .addClass($opts.size)
      .addClass($opts.class)
      .appendTo($el);

    var $elBody = $el.children('.widget-body'),
        $elData = $elBody.children('.widget-data'),
        $elLoading = $elBody.children('.loading');

    var $load = function (url){
      $.ajax({
        url: url || $opts.url,
        type: 'GET',
        dataType: 'html',
        beforeSend: function(xhr, settings) {
          $elLoading.show();
        },
        success: function(data, textStatus, xhr) {
          $elData.html(data);
          if(typeof $opts.onSuccess == 'function'){
            $opts.onSuccess($el);
          }
        },
        error: function(xhr, textStatus, errorThrown) {
          $elData.html(errorThrown);
        },
        complete: function(xhr, textStatus) {
          $elLoading.hide();
        }
      });
    };

    $load();

    $el.children('.widget-header').children('button').click(function(){
      $load();
    });
  }

  function Window(url, options) {

    var defaults = {
      header: true,
      title: '',
      size: '',
      fade: true,
      onSuccess: null
    };

    var $opts = $.extend( {}, defaults, options);
    var $id   = Math.floor(Math.random() * 101);

    $('<div class="modal" tabindex="-1" role="dialog">' +
        '<div class="modal-dialog ' + $opts.size + '">' +
          '<div class="modal-content">' +
            '<div class="modal-body">' +
              '<div class="modal-data"></div>' +
              '<div class="loading">' +
                '<div class="line"></div>' +
                '<div class="break dot1"></div>' +
                '<div class="break dot2"></div>' +
                '<div class="break dot3"></div>' +
              '</div>' +
            '</div>' +
            '<div class="modal-footer">' +
              '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>' +
            '</div>' +
          '</div>' +
        '</div>' +
      '</div>')
      .attr('id', 'window-' + $id)
      .appendTo('body');

    var $el = $('#window-' + $id),
        $elBody = $el.find('.modal-body'),
        $elData = $elBody.children('.modal-data'),
        $elLoading = $elBody.children('.loading');

    if($opts.header){
      $('<div class="modal-header">' + $opts.title + '</div>').prependTo($el.find('.modal-content'));
    }

    if($opts.fade){
      $el.addClass('fade');
    }

    var $load = function (){
      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        beforeSend: function(xhr, settings) {
          $elLoading.show();
        },
        success: function(data, textStatus, xhr) {
          $elData.html(data);
          if(typeof $opts.onSuccess == 'function'){
            $opts.onSuccess($el);
          }
        },
        error: function(xhr, textStatus, errorThrown) {
          $elData.html(errorThrown);
        },
        complete: function(xhr, textStatus) {
          $elLoading.hide();
        }
      });
    };

    $el.on('shown.bs.modal', function (e) {
          $load();
        })
        .on('hidden.bs.modal', function (e) {
          $el.remove();
        })
        .modal();
  }

  $.fn.widget = function ( options ) {
    return this.each(function () {
      if (!$.data(this, 'widget')) {
        $.data(this, 'widget', new Widget(this, options));
      }
    });
  };

  $.window = function(url, opts){
    return new Window(url, opts);
  };

  $.openProjectInfo = function(url){
    return $.window(url, {
      header: false
    });
  };

  $.switchListItemExtra = function (el){
    $el = $(el);
    $el.siblings().not($el).removeClass('active');
    $el.toggleClass('active');
  };

})(jQuery, window, document);