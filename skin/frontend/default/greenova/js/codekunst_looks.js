function getContentHeight(element) {
	var clone = element.clone().css('position', 'static').appendTo('body');
	var height = clone.height();
	clone.remove();

	return height;
}

// Register event and associated call back
Event.observe(document, 'looks:recalculate-total', recalculateTotalForLook);

// Callback function to handle the event
function recalculateTotalForLook(event) {
    var eventProductId = event.memo.productId;

    // iterate through global variable (optionsPrices) to calculate total
    if (typeof optionsPrices != 'undefined') {
        var sum = 0.0;
        var optionPrice, basePrice;

        // calculate total price
        for(var productId in optionsPrices) {
            if(optionsPrices.hasOwnProperty(productId)) {
                // get bool value for checkbox of the current product
                var selectors = $$('input[id="look-checkbox-' + productId + '"]');
                if (selectors.length > 0) {
                    var isChecked = selectors[0].checked;
                    if (isChecked) {
                        // get base price for configurable product
                        if(optionsPrices[productId].hasOwnProperty('productPrice')) {
                            basePrice = optionsPrices[productId].productPrice;
                        } else {
                            basePrice = 0.0;
                        }
                        // get price for selected option
                        if(optionsPrices[productId].optionPrices.hasOwnProperty('config')) {
                            optionPrice = optionsPrices[productId].optionPrices.config.price;
                        } else {
                            optionPrice = 0.0;
                        }

                        var qtyElement = $$('#qty-'+productId);
                        var quantity = parseFloat(qtyElement[0].getValue());

                        var price = ((basePrice + optionPrice) * quantity);

                        sum += price;

                        // update mini-cart price
                        var miniCartPriceElement = $$('.minicart-'+productId+' .price');
                        miniCartPriceElement[0].update(optionsPrices[productId].formatPrice(price));
                    }
                }
            }
        }

        // disable validation for non-selected products, enable for selected products
        $$('.look-checkbox input').each(function(checkbox) {
            var productId = checkbox.readAttribute('data-product-id');
            var input = $$('.super-attribute-select_' + productId)[0];
            var savedValidators = (null !== input.readAttribute('data-validators')) ? input.readAttribute('data-validators') : '';
            if(checkbox.checked && input.hasAttribute('data-validators')) {
                input.addClassName(savedValidators);
                input.removeAttribute('data-validators');
            } else {
                input.classNames().each(function(className, index) {
                    if(className.match(/^validate/) || className.match(/^required-entry$/)) {
                        input.writeAttribute('data-validators', savedValidators + ' ' + className);
                        input.removeClassName(className);
                    }
                });
            }
        });

        // update total price
        if(optionsPrices.hasOwnProperty(eventProductId)) {
            var formattedPrice = optionsPrices[eventProductId].formatPrice(sum);
            $$('#total-price > span').each(function(element){
                element.update(formattedPrice);
            });
        }
    }
}
