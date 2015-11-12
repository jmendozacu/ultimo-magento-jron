// check if visitor is using a mobile device
var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) === true;

jQuery(document).ready(function($) {
    if (!isMobile) {
        /**
         * Category Slider
         */

        var slider = $('#slider');
        var sliderList = slider.children()[0];
        var sliderWidth = slider.parent().width();
        var slideWidth = Math.round(sliderWidth / 3);
        sliderWidth = slideWidth * 3;
        slider.width(sliderWidth);

        var slideIdCounter = 0;
        $(sliderList).children().each(function () {
            $(this).prepend('<div class="slide-' + slideIdCounter + '-overlay overlay"></div>');
            $(this).width(slideWidth);
            slideIdCounter++;
        });

        $('.overlay').show();

        var currentSlide = 1;
        slider.find('.slide-' + currentSlide + '-overlay').hide();

        if (sliderList) {
            var increment = $(sliderList).children().outerWidth(true),
                elmnts = $(sliderList).children(),
                numElmts = elmnts.length,
                sizeFirstElmnt = increment,
                shownInViewport = Math.round(slider.width() / sizeFirstElmnt),
                firstElementOnViewPort = 1,
                isAnimating = false;

            for (var i = 0; i < shownInViewport; i++) {
                $(sliderList).css('width', (numElmts + shownInViewport) * increment + increment + "px");
                $(sliderList).append($(elmnts[i]).clone());
            }

            // init center element (pullup)
            var element = $('.slide-1-overlay').parent().find('.pullup');
            var height = getContentHeight(element);
            jQuery('.pullup').stop(true, true);
            jQuery(element).animate({ "top": "-=" + height + "px" }, "slow" );

            $('a.control_prev').bind("click", function (event) {
                if (!isAnimating) {
                    isAnimating = true;
                    if (firstElementOnViewPort == 1) {
                        $(sliderList).css('left', "-" + numElmts * sizeFirstElmnt + "px");
                        firstElementOnViewPort = numElmts;
                    } else {
                        firstElementOnViewPort--;
                    }

                    // pull down
                    var element = jQuery('.overlay:hidden').parent().find('.pullup');
                    var height = getContentHeight(element);
                    jQuery('.pullup').stop(true, true);
                    element.animate({ "top": "+=" + height + "px" }, "slow" );

                    $('.overlay').show();

                    $(sliderList).animate({
                        left: "+=" + increment,
                        y: 0
                    }, "swing", function () {
                        if (currentSlide > 0) {
                            currentSlide--;
                        } else {
                            currentSlide = numElmts - 1;
                        }
                        var overlay = $('.slide-' + currentSlide + "-overlay");
                        overlay.fadeOut('fast');

                        // pullup
                        var element = overlay.parent().find('.pullup');
                        var height = getContentHeight(element);
                        jQuery('.pullup').stop(true, true);
                        jQuery(element).animate({ "top": "-=" + height + "px" }, "slow", function() {
                            isAnimating = false;
                        });
                    });
                }
            });

            $('a.control_next').bind("click", function (event) {
                if (!isAnimating) {
                    isAnimating = true;
                    if (firstElementOnViewPort > numElmts) {
                        firstElementOnViewPort = 2;
                        $(sliderList).css('left', "0px");
                    }
                    else {
                        firstElementOnViewPort++;
                    }

                    // pull down
                    var element = jQuery('.overlay:hidden').parent().find('.pullup');
                    var height = getContentHeight(element);
                    jQuery('.pullup').stop(true, true);
                    element.animate({ "top": "+=" + height + "px" }, "slow" );

                    $('.overlay').show();

                    $(sliderList).animate({
                        left: "-=" + increment,
                        y: 0
                    }, "swing", function () {
                        if (currentSlide == numElmts - 1) {
                            currentSlide = 0;
                        } else {
                            currentSlide++;
                        }

                        var overlay = $('.slide-' + currentSlide + "-overlay");
                        overlay.fadeOut('fast');

                        // pullup
                        var element = overlay.parent().find('.pullup');
                        var height = getContentHeight(element);
                        jQuery('.pullup').stop(true, true);
                        jQuery(element).animate({ "top": "-=" + height + "px" }, "slow", function() {
                            isAnimating = false;
                        });
                    });
                }
            });
        }
    } else {
        // mobile device
        jQuery('#slider > a').hide();
        jQuery('#slider li').css('float', 'none');
        var elements = jQuery('.pullup');
        elements.each(function(){
            var height = getContentHeight(jQuery(this));
            jQuery(this).animate({ "top": "-=" + height + "px" }, "slow" );
        });
    }
});
