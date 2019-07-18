// HTML5 placeholder plugin version 0.3
// Enables cross-browser* html5 placeholder for inputs, by first testing
// for a native implementation before building one.
//
// USAGE:
//$('input[placeholder]').placeholder();

        (function($){

        	  $.fn.placeholder = function(options) {
        	    return this.each(function() {
        	      if ( !("placeholder"  in document.createElement(this.tagName.toLowerCase()))) {
        	        var $this = $(this);
        	        var placeholder = $this.attr('placeholder');
        	        !$this.val() && $this.val(placeholder).data('color', $this.css('color')).css('color', '#aaa');
        	        $this
        	          .focus(function(){ if ($.trim($this.val())===placeholder){ $this.val('').css('color', $this.data('color'))} })
        	          .blur(function(){ if (!$.trim($this.val())){ $this.val(placeholder).data('color', $this.css('color')).css('color', '#aaa'); } });
        	      }
        	    });
        	  };
        	})(jQuery);

        	// detect if browser supports transition, currently checks for webkit, moz, opera, ms
        	var cssTransitionsSupported = false;
        	(function() {
        	    var div = document.createElement('div');
        	    div.innerHTML = '<div style="-webkit-transition:color 1s linear;-moz-transition:color 1s linear;-o-transition:color 1s linear;-ms-transition:color 1s linear;-khtml-transition:color 1s linear;transition:color 1s linear;"></div>';
        	    cssTransitionsSupported = (div.firstChild.style.webkitTransition !== undefined) || (div.firstChild.style.MozTransition !== undefined) || (div.firstChild.style.OTransition !== undefined) || (div.firstChild.style.MsTransition !== undefined) || (div.firstChild.style.KhtmlTransition !== undefined) || (div.firstChild.style.Transition !== undefined);
        	    delete div;
        	})();

        	// perform JavaScript after the document is scriptable.
        	$(document).ready(function() {
        		//var exp=$.cookie('exp');
        		//alert(exp);
        	    /**
        	     * Form Validators
        	     */
        	    // Regular Expression to test whether the value is valid
        	    $.tools.validator.fn("[type=time]", "请输入正确的时间格式!", function (input, value) {
        	        return(/^\d\d:\d\d$/).test(value);
        	    });

        	    $.tools.validator.fn("[data-equals]", "请输入与上次相同的信息!", function (input) {
        	        var name = input.attr("data-equals"),
        	        field = this.getInputs().filter("[name=" + name + "]");
        	        return input.val() === field.val() ? true : [name];
        	    });

        	    $.tools.validator.fn("[minlength]", function (input, value) {
        	        var min = input.attr("minlength");

        	        return value.length >= min ? true : {
        	            en : "请保证输入的字数大于 " + min + " 个!" + (min > 1 ? "s" : "")
        	        };
        	    });

        	    $.tools.validator.localizeFn("[type=time]", {
        	        en : '请输入正确的时间!'
        	    });

        	    $().UItoTop({ easingType: 'easeOutQuart' });

        	    $(window).bind('load resize', function(){
        	        var section = $('#wrapper > section > section');
        	        if (section.css('position') == 'absolute' && section.css('left') != 0) {
        	            if (location.hash != '#menu') {
        	                section.css('left',0);
        	            } else {
        	                section.css('left','100%');
        	            }
        	        } else {
        	            section.show();
        	        }
        	    });

        	    if (!location.href.match(/login\.html$/i)&&(!location.hash||location.hash=='#menu')) {
        	        location.hash = $('.drilldownMenu .current a').attr("href");
        	    } else {
        	        $(window).trigger("hashchange");
        	    }

        	    $("#wrapper > section > aside > nav").length && $("#wrapper > section > aside > nav").each(function(){
        	        var base = $(this);
        	        $(this).drillDownMenu();
        	    });

        	    $('.showmenu').click(function(){
        	        $('#wrapper > section > section').animate({left: "100%"}, 300, "easeInOutQuart", function(){
        	            $(this).hide();
        	        });
        	    });

        	    var target = ".login-box";

        	    $('input[placeholder]', target).placeholder();

        	    $("input[type=date]", target).dateinput();

        	    $("input:checkbox,input:radio,select,input:file", target).uniform();

        	    /**
        	     * setup the validators
        	     */
        	    $(".has-validation", target).validator({
        	        position : 'bottom left',
        	        offset : [5, 0],
        	        messageClass : 'form-error',
        	        message : '<div><em/></div>'// em element is the arrow
        	    }).attr('novalidate', 'novalidate');
        	});

        	$(window).bind("hashchange", function(e)  {
        	    h = location.hash;
        	    if(($('#exp').val()==0)&&(h!="#index/dashboard")){
            			return;
            		}
        	    if (h && h != '#menu') {
        	    	link=$('#path').val();
        	    	if($('#dingwei').length==0){
        	    	var strHtml="<div id='dingwei'><img src='"+$('#img').val()+"/ajax-loader-trans.gif'>&nbsp;&nbsp;加载中...</div>";
        	    	$('body').append(strHtml);
        	    	}
        	    	link=link+h.replace(/^\#/, "");
        	        id = link.replace(/[\/\.]/,"-");
        	        $('#'+id).length && $('#'+id).remove();
        	        $.ajax(link, {
        	        	type: "POST",
        	            dataType: "html",
        	            cache: false,
        	            success: function(data, textStatus, jqXHR) {
        	            	$('#dingwei').remove();
        	                return pageDownloaded(data, id);
        	            },
        	            complete: function(jqXHR, textStatus) {
        	            }
        	        });
        	    }
        	});

        	function pageDownloaded(data, id){
        		//$('#main-content').children().remove();//eva
        	    var target = "#main-content",
        	        div = $('<div style="left: 100%" id="'+id+'">'+data+'</div>').appendTo($(target));
        	        title = $(div).find("h1.page-title").html();
        	    $("#wrapper > section > section > header h1").html(title);

        	    if ($('#wrapper > section > section').css('position')=='absolute') {
        	        $("> div:last", target).css({left: 0, position: 'relative'}).siblings().remove();
        	        $('#wrapper > section > section').show().animate({left: 0}, 300, "easeInOutQuart", function(){$(this).css('left',0);});
        	    } else {
        	        $("> div", target).animate({left: "-=100%"}, "slow", "easeInOutQuart", function(){
        	            $(this).css('left',0);
        	            $("> div:last", target).css({position: 'relative'}).siblings().remove();
        	        });
        	    }

        	    $(".tabs > ul", div).tabs("section > section");
        	    $(".accordion", div).tabs(".accordion > section", {tabs: 'header', effect: 'slide', initialIndex: 0});

        	    $('input[placeholder]', div).placeholder();

        	    $("input[type=date]", div).dateinput();

        	    $("input:checkbox,input:radio,select,input:file", div).uniform();

        	    /**
        	     * fix uniform uploader
        	     */
        	    $('.uploader .filename', div).click(function(){
        	        $('input:file', $(this).parent()).click();
        	    });

        	    /**
        	     * setup the validators
        	     */
        	    $(".has-validation", div).validator({
        	        position : 'bottom left',
        	        offset : [5, 0],
        	        messageClass : 'form-error',
        	        message : '<div><em/></div>'// em element is the arrow
        	    }).attr('novalidate', 'novalidate');

        	    /**
        	     * setup messages
        	     */
        	    $('.message.closeable', div).each(function(){
        	        var message = $(this);
        	        message.prepend('<span class="message-close"></span>');
        	        $('.message-close', message).click(function(){
        	            message.fadeOut();
        	        });
        	    });

        	    /**
        	     * Setup tooltips
        	     */
        	    $('.has-tooltip', div).tooltip({
        	        effect: 'slide', offset: [-14, 0], position: 'top center', layout: '<div><em/></div>',
        	        onBeforeShow: function() {
        	            this.getTip().each(function(){
        	                if ($.browser.msie) {
        	                    PIE.attach(this);
        	                }
        	            });
        	        },
        	        onHide: function() {
        	            this.getTip().each(function(){
        	                if ($.browser.msie) {
        	                    PIE.detach(this);
        	                }
        	            });
        	        }
        	    }).dynamic({
        	            bottom: { direction: 'down', bounce: true }
        	    });

        	    $(window).trigger('drilldown');
        	}