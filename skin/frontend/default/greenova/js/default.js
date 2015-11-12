/*
CSS Browser Selector v0.7.0 (April 01, 2013)
Rafael Lima (http://rafael.adm.br)
http://rafael.adm.br/css_browser_selector
License: http://creativecommons.org/licenses/by/2.5/
Contributors: http://rafael.adm.br/css_browser_selector#contributors
*/
function css_browser_selector(a, b) {
    var c = document.documentElement, d = [];
    b = b ? b : "";
    uaInfo.ua = a.toLowerCase();
    d = d.concat(uaInfo.getBrowser());
    d = d.concat(uaInfo.getPlatform());
    d = d.concat(uaInfo.getMobile());
    d = d.concat(uaInfo.getIpadApp());
    d = d.concat(uaInfo.getLang());
    d = d.concat([ "js" ]);
    d = d.concat(screenInfo.getPixelRatio());
    d = d.concat(screenInfo.getInfo());
    var e = function() {
        c.className = c.className.replace(/ ?orientation_\w+/g, "").replace(/ [min|max|cl]+[w|h]_\d+/g, "");
        c.className = c.className + " " + screenInfo.getInfo().join(" ");
    };
    window.addEventListener("resize", e);
    window.addEventListener("orientationchange", e);
    var f = dataUriInfo.getImg();
    f.onload = f.onerror = function() {
        c.className += " " + dataUriInfo.checkSupport().join(" ");
    };
    d = d.filter(function(a) {
        return a;
    });
    d[0] = b ? b + d[0] : d[0];
    c.className = d.join(" " + b);
    return c.className;
}

var uaInfo = {
    ua: "",
    is: function(a) {
        return RegExp(a, "i").test(uaInfo.ua);
    },
    version: function(a, b) {
        b = b.replace(".", "_");
        var c = b.indexOf("_"), d = "";
        while (c > 0) {
            d += " " + a + b.substring(0, c);
            c = b.indexOf("_", c + 1);
        }
        d += " " + a + b;
        return d;
    },
    getBrowser: function() {
        var a = "gecko", b = "webkit", c = "chrome", d = "firefox", e = "safari", f = "opr", g = uaInfo.ua, h = uaInfo.is;
        return [ !/opr|webtv/i.test(g) && /msie\s(\d+)/.test(g) ? "ie ie" + (/trident\/4\.0/.test(g) ? "8" : RegExp.$1) : h("firefox/") ? a + " " + d + (/firefox\/((\d+)(\.(\d+))(\.\d+)*)/.test(g) ? " " + d + RegExp.$2 + " " + d + RegExp.$2 + "_" + RegExp.$4 : "") : h("gecko/") ? a : h("opr") ? f + (/version\/((\d+)(\.(\d+))(\.\d+)*)/.test(g) ? " " + f + RegExp.$2 + " " + f + RegExp.$2 + "_" + RegExp.$4 : /opr(\s|\/)(\d+)\.(\d+)/.test(g) ? " " + f + RegExp.$2 + " " + f + RegExp.$2 + "_" + RegExp.$3 : "") : h("konqueror") ? "konqueror" : h("chrome") ? b + " " + c + (/chrome\/((\d+)(\.(\d+))(\.\d+)*)/.test(g) ? " " + c + RegExp.$2 + (RegExp.$4 > 0 ? " " + c + RegExp.$2 + "_" + RegExp.$4 : "") : "") : h("iron") ? b + " iron" : h("applewebkit/") ? b + " " + e + (/version\/((\d+)(\.(\d+))(\.\d+)*)/.test(g) ? " " + e + RegExp.$2 + " " + e + RegExp.$2 + RegExp.$3.replace(".", "_") : / Safari\/(\d+)/i.test(g) ? "419" == RegExp.$1 || "417" == RegExp.$1 || "416" == RegExp.$1 || "412" == RegExp.$1 ? " " + e + "2_0" : "312" == RegExp.$1 ? " " + e + "1_3" : "125" == RegExp.$1 ? " " + e + "1_2" : "85" == RegExp.$1 ? " " + e + "1_0" : "" : "") : h("mozilla/") ? a : "" ];
    },
    getPlatform: function() {
        var a = "android", b = "blackberry", c = "device_", d = uaInfo.ua, e = uaInfo.version, f = uaInfo.is;
        return [ f("j2me") ? "j2me" : f("ipad|ipod|iphone") ? (/CPU( iPhone)? OS (\d+[_|\.]\d+([_|\.]\d+)*)/i.test(d) ? "ios" + e("ios", RegExp.$2) : "") + " " + (/(ip(ad|od|hone))/gi.test(d) ? RegExp.$1 : "") : f("android") ? a + (/Version\/(\d+)(\.(\d+))+/i.test(d) ? " " + a + RegExp.$1 + " " + a + RegExp.$1 + RegExp.$2.replace(".", "_") : "") + (/Android (.+); (.+) Build/i.test(d) ? " " + c + RegExp.$2.replace(/ /g, "_").replace(/-/g, "_") : "") : f("blackberry") ? b + (/Version\/(\d+)(\.(\d+)+)/i.test(d) ? " " + b + RegExp.$1 + " " + b + RegExp.$1 + RegExp.$2.replace(".", "_") : /Blackberry ?(([0-9]+)([a-z]?))[\/|;]/gi.test(d) ? " " + b + RegExp.$2 + (RegExp.$3 ? " " + b + RegExp.$2 + RegExp.$3 : "") : "") : f("playbook") ? "playbook" : f("kindle|silk") ? "kindle" : f("playbook") ? "playbook" : f("mac") ? "mac" + (/mac os x ((\d+)[.|_](\d+))/.test(d) ? " mac" + RegExp.$2 + " mac" + RegExp.$1.replace(".", "_") : "") : f("win") ? "win" + (f("windows nt 6.2") ? " win8" : f("windows nt 6.1") ? " win7" : f("windows nt 6.0") ? " vista" : f("windows nt 5.2") || f("windows nt 5.1") ? " win_xp" : f("windows nt 5.0") ? " win_2k" : f("windows nt 4.0") || f("WinNT4.0") ? " win_nt" : "") : f("freebsd") ? "freebsd" : f("x11|linux") ? "linux" : "" ];
    },
    getMobile: function() {
        var a = uaInfo.is;
        return [ a("android|mobi|mobile|j2me|iphone|ipod|ipad|blackberry|playbook|kindle|silk") ? "mobile" : "" ];
    },
    getIpadApp: function() {
        var a = uaInfo.is;
        return [ a("ipad|iphone|ipod") && !a("safari") ? "ipad_app" : "" ];
    },
    getLang: function() {
        var a = uaInfo.ua;
        return [ /[; |\[](([a-z]{2})(\-[a-z]{2})?)[)|;|\]]/i.test(a) ? ("lang_" + RegExp.$2).replace("-", "_") + ("" != RegExp.$3 ? (" " + "lang_" + RegExp.$1).replace("-", "_") : "") : "" ];
    }
};
if(typeof(html) !== "undefined"){
    var screenInfo = {
        width: (window.outerWidth || html.clientWidth) - 15,
        height: window.outerHeight || html.clientHeight,
        screens: [ 0, 768, 980, 1200 ],
        screenSize: function() {
            screenInfo.width = (window.outerWidth || html.clientWidth) - 15;
            screenInfo.height = window.outerHeight || html.clientHeight;
            var a = screenInfo.screens, b = a.length, c = [], d, e;
            while (b--) if (screenInfo.width >= a[b]) {
                if (b) c.push("minw_" + a[b]);
                if (b <= 2) c.push("maxw_" + (a[b + 1] - 1));
                break;
            }
            return c;
        },
        getOrientation: function() {
            return screenInfo.width < screenInfo.height ? [ "orientation_portrait" ] : [ "orientation_landscape" ];
        },
        getInfo: function() {
            var a = [];
            a = a.concat(screenInfo.screenSize());
            a = a.concat(screenInfo.getOrientation());
            return a;
        },
        getPixelRatio: function() {
            var a = [], b = window.devicePixelRatio ? window.devicePixelRatio : 1;
            if (b > 1) {
                a.push("retina_" + parseInt(b) + "x");
                a.push("hidpi");
            } else a.push("no-hidpi");
            return a;
        }
    };
}else{
    var screenInfo = {
        width: (window.outerWidth) - 15,
        height: window.outerHeight,
        screens: [ 0, 768, 980, 1200 ],
        screenSize: function() {
            screenInfo.width = (window.outerWidth) - 15;
            screenInfo.height = window.outerHeight;
            var a = screenInfo.screens, b = a.length, c = [], d, e;
            while (b--) if (screenInfo.width >= a[b]) {
                if (b) c.push("minw_" + a[b]);
                if (b <= 2) c.push("maxw_" + (a[b + 1] - 1));
                break;
            }
            return c;
        },
        getOrientation: function() {
            return screenInfo.width < screenInfo.height ? [ "orientation_portrait" ] : [ "orientation_landscape" ];
        },
        getInfo: function() {
            var a = [];
            a = a.concat(screenInfo.screenSize());
            a = a.concat(screenInfo.getOrientation());
            return a;
        },
        getPixelRatio: function() {
            var a = [], b = window.devicePixelRatio ? window.devicePixelRatio : 1;
            if (b > 1) {
                a.push("retina_" + parseInt(b) + "x");
                a.push("hidpi");
            } else a.push("no-hidpi");
            return a;
        }
    };
}


var dataUriInfo = {
    data: new Image(),
    div: document.createElement("div"),
    isIeLessThan9: false,
    getImg: function() {
        dataUriInfo.data.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
        dataUriInfo.div.innerHTML = "<!--[if lt IE 9]><i></i><![endif]-->";
        dataUriInfo.isIeLessThan9 = 1 == dataUriInfo.div.getElementsByTagName("i").length;
        return dataUriInfo.data;
    },
    checkSupport: function() {
        if (1 != dataUriInfo.data.width || 1 != dataUriInfo.data.height || dataUriInfo.isIeLessThan9) return [ "no-datauri" ]; else return [ "datauri" ];
    }
};

var css_browser_selector_ns = css_browser_selector_ns || "";

css_browser_selector(navigator.userAgent, css_browser_selector_ns);

/*
 *  jQuery owlCarouselvertical v1.3.2
 *
 *  Copyright (c) 2013 Bartosz Wojciechowski
 *  http://www.owlgraphic.com/owlcarousel/
 *
 *  Licensed under MIT
 *
 */

/*JS Lint helpers: */
/*global dragMove: false, dragEnd: false, $, jQuery, alert, window, document */
/*jslint nomen: true, continue:true */

/* Catch Magento Messages */
function showMessage(txt, type) {
	var test = new Growler();
	test.warn(txt, { life: 5 });
}


/* Layered Navigation Build Akkordeon in Filter */
function buildlaynav() {

	jQuery('.block-layered-nav #narrow-by-list dt .opener').each(function(i){
		var filterclass = jQuery(this).parent().attr('class').split(' ')[0];
		var fitteddd = jQuery(this).parent().siblings("dd."+filterclass).find(".filter-navigation-wrapper");
		var fittetheight = parseInt(fitteddd.find(".ol-wrapper").css("height"), 10);
		if(fitteddd.find("input:checked").length){
			fitteddd.css({
				"opacity": 1,
				"height": fittetheight+15
			});
			jQuery(this).parent().addClass("active");
			fitteddd.find("input:checked").siblings("a").addClass("active");
		}
		jQuery(this).click(
			function(){
				if(fitteddd.css("opacity") == 0){
					fitteddd.animate({
					    opacity: 1,
					    height:fittetheight
					  }, 400);
					jQuery(this).parent().addClass("active");
				}else{
					fitteddd.animate({
					    opacity: 0,
					    height:0
					  }, 400);
					jQuery(this).parent().removeClass("active");
				}
			}
		);
	});
	jQuery('.block-layered-nav .block-title').each(function(i){
		var fitteddd = jQuery(this).siblings(".block-content");
		var fittetheight = fitteddd.css("height");
		//jQuery(this).parent().addClass( "active" );
		jQuery(this).click(
			function(){
				jQuery(this).parent().toggleClass( "active" );
			}
		);
	});
}

// Update Storefinder on Detail Page
function getLiveStores(iid){
  
  jQuery.ajax({
	type  : 'GET',
	  url: 'https://s01.shop.fortuneglobe.com/mey/alvine/api/yellowpagesstock/index.php',
	data  : {
	  sxx_iid	: iid,
	  apikey	: 'yellowpagesstock',
	  format	: 'jsonp'
	},
	dataType: 'JSONP',
	jsonpCallback: 'callback',
	success: function(data) { 
	  showStores('.storefinder-detail iframe',data);
	},
	error: function() {
	},
	jsonp : 'jsonp'
  });
 
 }

function showStores(selector,data){
 
  var frames  = jQuery(selector),
	  select  = jQuery('#attribute150'),
	  data	  = data;
  
  changeStorefinderSRC(data,'',frames);
  
  select.on('change', function(){
	
	var val		  = jQuery(this).val(),
		itemSize  = jQuery(this).find('option[value="'+val+'"]').text();
	
	changeStorefinderSRC(data,itemSize,frames);

  });
}
function changeStorefinderSRC(data,itemSize,frames){
  frames.each(function(){
	var frame = jQuery(this);
	itemSize = (itemSize)? itemSize : frame.data('size');
	var src   = frame.attr('src'),
		id	= frame.attr('id'),
		iid	= frame.data('iid'),
		addSrc = (data[itemSize]) ? data[itemSize] : '',
		mainSrc = src.split('?');
	frame.attr('src', mainSrc[0]+'?s='+addSrc);	  
  });
}

function changeStorelocatorProductEan(ean){
	if(jQuery(".storefinder-detail").css("display") != "none"){
		jQuery(".storefinder-detail > div > iframe").each(function(){
			var oldurl = jQuery(this).attr("src");
			var newurltemp = oldurl.split("?ean=");
			jQuery(this).attr("src",newurltemp[0]+"?ean="+ean);
		});
	}
}


// Create an Colorbox Fake to Show Additional Information on Detail Page
function linkbadgeicons(){
	if(jQuery(".infoblocks").length){
		jQuery(".infoblocks .info-block-item > img").each(function( index ) {
			jQuery(this).click(function(){
				var text = jQuery(this).parent().find(".hidden-info-block").html();
				var imgsrc = jQuery(this).attr('src');
				var imgalt = jQuery(this).attr('alt');
				var overlay = jQuery("#badge-overlay");
				overlay.find("> img").attr("src",imgsrc);
				overlay.find("> img").attr("alt",imgalt);
				overlay.find("> .overlay-content").html(text);
				var overlayheight = overlay.find(".overlay-content").outerHeight();
				overlay.parent().animate({"min-height":overlayheight},400);
				overlay.toggleClass("hide");
			});
		});
		jQuery("#badge-overlay .closer").click(function(){
			jQuery("#badge-overlay").parent().animate({"min-height":0},400,function(){jQuery("#badge-overlay").toggleClass("hide");});
		});
	}
}

// Select Product Option for Color
function updatecoloroption(valueid,attid){
	jQuery(".product-options-wrapper").addClass("highlight");
	jQuery(".color-selector").find("option:selected").removeAttr("selected");
	jQuery(".color-selector").find("option[value="+valueid+"]").attr("selected", "selected");
	var val = jQuery(".color-selector").find("option[value="+valueid+"]").val();
	jQuery(".color-option-list").find("li").removeClass("active");
	jQuery(".color-option-list").find("li."+val).addClass("active");
	var element=$('attribute'+attid);
	spConfig.configureElement(element);
	jQuery(".color-option-list").parent().parent().siblings('dd').each( function(){
	  var atttr = parseInt(jQuery(this).find('select').attr("id").replace ( /[^\d.]/g, '' ));
	  jQuery(this).find('select option:nth-child(2)').attr("selected", "selected");
	  element=$('attribute'+atttr);
	  spConfig.configureElement(element);
	});
	jQuery(".product-options-wrapper").removeClass("highlight");
}

//Change Product-List Image
function updatelistimage(prodid,valueid){
	// Selecotr with 2 Images in it
	var selector = jQuery(this).parent().parent().parent().parent().find('.product-image-wrapper .product-image');
	//store bluesign img src
	var bluesign_img = jQuery('.bluesign-image').attr('src');
	
	jQuery(this).parent().parent().find("li").removeClass("active");
	jQuery(this).parent().addClass('active');
	jQuery(this).parent().parent().parent().parent().find('.product-infos h2 a').attr("href",productimagelist[prodid][valueid][0]);
	jQuery(this).parent().parent().parent().parent().find('.product-infos .price-box').replaceWith(productimagelist[prodid][valueid][4]);
	selector.attr("href",productimagelist[prodid][valueid][0]);
	selector.find('img:first-child').attr("src",productimagelist[prodid][valueid][1]);
	selector.find('img.alt-img').attr("src",productimagelist[prodid][valueid][2]);

	//reset bluesign img src
	jQuery('.bluesign-image').attr('src', bluesign_img);
	
	if(productimagelist[prodid][valueid][5] == "No" && selector.find(".sticker-wrapper.top-right").is(':visible')){
		selector.find(".sticker-wrapper.top-right").hide();
	}else if(productimagelist[prodid][valueid][5] == "Yes" && selector.find(".sticker-wrapper.top-right").is(':hidden')){
		selector.find(".sticker-wrapper.top-right").show();
	}
	if(productimagelist[prodid][valueid][6] == "No" && selector.find(".sticker-wrapper.top-left").is(':visible')){
		selector.find(".sticker-wrapper.top-left").hide();
	}else if(productimagelist[prodid][valueid][6] == "Yes" && selector.find(".sticker-wrapper.top-left").is(':hidden')){
		selector.find(".sticker-wrapper.top-left").show();
	}
	if (productimagelist[prodid][valueid][7] == "No" && selector.find("#bluesign").is(':visible')) {
		selector.find("#bluesign").hide();
	} else if (productimagelist[prodid][valueid][7] == "Yes" && selector.find("#bluesign").is(':hidden')) {
		selector.find("#bluesign").show();
	}
}

// Get URL Parameter Function
// Read a page's GET URL variables and return them as an associative array.
function getUrlVars()
{
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) != -1) return c.substring(name.length,c.length);
    }
    return "";
}

// Close Outside Banners and set Cookie for 
function hideoutsider(selector, initials) {
	jQuery('.'+selector).fadeOut("slow");
	var daate = new Date();
	var oldtime = daate.getTime();
	daate.setTime(oldtime+172800000);
	document.cookie=initials+"deactivate=true; expires="+daate;
}

function createurlstring(string){
	var find = ['Š', "š", "Đ","đ","Ž","ž","Č","č","Ć","ć","À","Á","Â","Ã","Ä","Å","Æ","Ç","È","É","Ê","Ë","Ì","Í","Î","Ï","Ñ","Ò","Ó","Ô","Õ","Ö","Ø","Ù","Ú","Û","Ü","Ý","Þ","ß","à","á","â","ã","ä","å","æ","ç","è","é","ê","ë","ì","í","î","ï","ð","ñ","ò","ó","ô","õ","ö","ø","ù","ú","û","ý","ý","þ","ÿ","Ŕ","ŕ","/"," ","ü"];
    var replace = ["S","s","Dj","dj","Z","z","C","c","C","c","A","A","A","A","Ae","A","A","C","E","E","E","E","I","I","I","I","N","O","O","O","O","Oe","O","U","U","U","Ue","Y","B","Ss","a","a","a","a","ae","a","a","c","e","e","e","e","i","i","i","i","o","n","o","o","o","o","oe","o","u","u","u","y","y","b","y","R","r","-","-","ue"];
    string = string.replaceArray(find, replace).toLowerCase();
    return string;
}



(function ($, window, document) {

	$( document ).ready(function() {

		// Filter Navigation get Started
		if(jQuery('.block-layered-nav .block-title').find('span').length){
			buildlaynav();
		}

		//Preselect following Productoptions
		jQuery("#product-options-wrapper dd select#attribute282").change(function() {
			var selector = jQuery("#product-options-wrapper dd select#attribute292");
			if(selector.length){
			  	var atttr = parseInt(selector.attr("id").replace ( /[^\d.]/g, '' ));
			  	selector.find('option:nth-child(2)').attr("selected", "selected");
			  	element = document.getElementById('attribute'+atttr);
			  	spConfig.configureElement(element);
			}
		});



	    /* Cut Main Nav after 10 Entries in Series*/
	    	// Main Nav
			$("#nav .level0 ul.level0 > li:nth-child(3)").each(function( index ) {
				var len = $(this).find("ul.level1 > li").length;
				if(len > 10){
					$(this).find('ul.level1 > li:gt(10)').remove();
				}
			});
			//Sale and New
			$("#nav .level0 ul.level1 > li:nth-child(3)").each(function( index ) {
				var len = $(this).find("ul.level2 > li").length;
				if(len > 10){
					$(this).find('ul.level2 > li:gt(10)').remove();
				}
			});

		//Replace standard alert/confirm-Window with Colorbox(Error)
		window.alert = function(message){
	      showMessage(message, 'error');
		};


	    // Replace Success and Error Magento Messages with Colorbox(Error/Success)
	    if( $('ul.messages .error-msg').length || $('ul.messages .success-msg').length || $('ul.messages .notice-msg').length ){
	      var messaging = $('ul.messages span').html();
	      $('ul.messages').hide();
	      if(messaging != null && messaging != ""){
	        if($('ul.messages .error-msg').length){
	          showMessage(messaging, 'error');
	        }else{
	          showMessage(messaging, 'success');
	        }
	      }
	    }

	    // Create Cookie for Partner
	    var partner = getUrlVars()["sxx_partner"];
	    if(typeof partner != 'undefined'){
	    	var daate = new Date();
	    	var oldtime = daate.getTime();
			daate.setTime(oldtime+5184000000);
	    	document.cookie="sxx_partner="+partner+"; expires="+daate+"; path=/";
	    }
        // Create Cookie for Zanox
        var zanoxId = getUrlVars()["zanpid"];
        if(typeof zanoxId != 'undefined'){
            var daate = new Date();
            var oldtime = daate.getTime();
            daate.setTime(oldtime+5184000000);
            document.cookie="zanpid="+zanoxId+"; expires="+daate+"; path=/";
            document.cookie="sxx_partner=zanox; expires="+daate+"; path=/";
        }

	    //FadeIn OutsideBanners if not clicked on X in this Session
	    var deactivate = getCookie("nladeactivate");
	    if(deactivate != "true"){
	    	jQuery('.newsletter_subscribe_boxe').fadeIn("slow");

	    }
		deactivate = getCookie("obsdeactivate");
	    if(deactivate != "true"){
	    	jQuery('.outsidebanner_slider-box').fadeIn("slow");
	    }

	    //Disable Checkout Button if Minimum Order Value ot reached
	    if($("ul.messages .notice-msg").length && $('.cart-table-wrapper').length){
	    	$(".proceed-wrapper .button-upper").attr("disabled", "disabled");
	    }
		
		var hitEvent = 'ontouchstart' in document.documentElement ? 'touchstart' : 'click';
		$('.top-links').bind(hitEvent, function() {
			return true;
		});
	});

}(jQuery, window, document));

var gaProperty = 'UA-33691295-1';
var disableStr = 'ga-disable-' + gaProperty;
if (document.cookie.indexOf(disableStr + '=true') > -1) {
    window[disableStr] = true;
}
function optOutAnalytics() {
    document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2199 23:59:59 UTC; path=/';
    window[disableStr] = true;
}
