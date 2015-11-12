// check if visitor is using a mobile device
var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) === true;

// add event observer for checkboxes, recalculate total price when event is fired
$$('.look-checkbox input').each(function(element){
    element.observe('click', function(){
        $(document).fire('looks:recalculate-total', {
            productId: this.readAttribute('data-product-id')
        });
    });
});

$$('select.qty').each(function(element){
    element.addEventListener('change', function() {
        $(document).fire('looks:recalculate-total', {
            productId: this.readAttribute('data-product-id')
        });
    }, false);
});

jQuery('.look-checkbox input').on('click', function() {
    var productId = jQuery(this).data('product-id');
    jQuery('.minicart-'+productId).toggle();

    var emptyTextElement = jQuery('.look-mini-cart').find('p.empty');
    // check if mini-cart is empty
    if (isMiniCartEmpty()) {
        emptyTextElement.removeClass('hidden');
    } else {
        emptyTextElement.addClass('hidden');
    }
});

var productAddToCartForm = new VarienForm('product_addtocart_form');
productAddToCartForm.submit = function(button, url) {
    if (this.validator.validate()) {
        var form = this.form;
        var oldUrl = form.action;
        if (url) { form.action = url; }
        var e = null;
        try { this.form.submit(); } catch (e) { }
        this.form.action = oldUrl;
        if (e) { throw e; }
        if (button && button != 'undefined') {
            button.disabled = true;
        }
    }
}.bind(productAddToCartForm);

var isMiniCartEmpty = function () {
    var arrTr = jQuery('.look-mini-cart table').find('tr:visible');
    return arrTr.length == 0;
};

var hasSelections = function () {
    return jQuery('.look-inner-container ul li').find('.look-checkbox input').is(':checked');
};

var allSelectionsValid = function () {
    var returnValue = true;
    var listItems = jQuery('.look-inner-container ul li');
    listItems.each(function(){
        var checkbox = jQuery(this).find('.look-checkbox input');
        var sizeSelector = jQuery(this).find("[id^='attribute272']");

        if (checkbox.is(':checked') && sizeSelector[0].selectedIndex === 0) {
            returnValue = false;
            return false;
        }
    });
    return returnValue;
};

/*
 SLIDER
 */
jQuery(document).ready(function ($) {
    if (!isMobile) {
        var sliderId = '#slider';
        var slideCount = $(sliderId+' ul li').length;
        var containerWidth = $(sliderId).parent().width();
        var numSlidesPerView = 3;

        if (slideCount < 3) {
            numSlidesPerView = slideCount;
        }

        var slideWidth = containerWidth/numSlidesPerView;
        $(sliderId+' ul li').width(slideWidth);
        var sliderWidth = slideWidth * numSlidesPerView;
        //var slideHeight = $(sliderId+' ul li').height();
        var sliderUlWidth = slideCount * slideWidth;
        var currentSlide = 0;

        //$(sliderId).css({ width: sliderWidth, height: slideHeight });
        $(sliderId+' ul').css({ width: sliderUlWidth, marginLeft: 0 });
        $('a.control_prev').hide();

        if (slideCount < 4) {
            $('a.control_next').hide();
        }

        function moveLeft() {
            $('a.control_prev').hide();
            //var marginLeft = parseInt($(sliderId+' ul').css('margin-left'));
            if(currentSlide > 0) {
                $('a.control_next').show();
                $(sliderId+' ul').animate({
                    'margin-left': '+='+slideWidth
                }, 200, function () {
                    currentSlide--;
                    if(currentSlide <= 0) {
                        $('a.control_prev').hide();
                    } else {
                        $('a.control_prev').show();
                    }
                });
            } else {
                $('a.control_prev').hide();
            }
        };

        function moveRight() {
            $('a.control_next').hide();
            if(currentSlide < slideCount - numSlidesPerView) {
                $('a.control_prev').show();
                $(sliderId+' ul').animate({
                    'margin-left': '-='+slideWidth
                }, 200, function () {
                    currentSlide++;
                    if(currentSlide >= slideCount - numSlidesPerView) {
                        $('a.control_next').hide();
                    } else {
                        $('a.control_next').show();
                    }

                });
            } else {
                $('a.control_next').hide();
            }
        };

        $('a.control_prev').click(function (event) {
            event.preventDefault();
            moveLeft();
        });

        $('a.control_next').click(function (event) {
            event.preventDefault();
            moveRight();
        });
    } else {
        // mobile device
        jQuery('#slider > a').hide();

        jQuery('#slider li').css('float', 'none');

        jQuery('.look-left,.look-right')
            .css('float', 'none')
            .css('width', 'inherit')
            .css('border', 0);

        jQuery('.look-product-checkout')
            .css('padding-left', 0);

        jQuery('.look-product-col')
            .css('border-bottom', "1px solid #e5e4e2")
            .css('padding', "30px 0");
    }
});