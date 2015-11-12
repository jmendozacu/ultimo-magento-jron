//Helperfunction String Replace with Arrays
String.prototype.replaceArray = function(find, replace) {
  var replaceString = this;
  for (var i = 0; i < find.length; i++) {
    replaceString = replaceString.replace(find[i], replace[i]);
  }
  return replaceString;
};


/*
    Some of these override earlier varien/product.js methods, therefore
    varien/product.js must have been included prior to this file.
*/

Product.Config.prototype.getMatchingSimpleProduct = function(){
    var inScopeProductIds = this.getInScopeProductIds();
    if ((typeof inScopeProductIds != 'undefined') && (inScopeProductIds.length == 1)) {
        return inScopeProductIds[0];
    }
    return false;
};

/*
    Find products which are within consideration based on user's selection of
    config options so far
    Returns a normal array containing product ids
    allowedProducts is a normal numeric array containing product ids.
    childProducts is a hash keyed on product id
    optionalAllowedProducts lets you pass a set of products to restrict by,
    in addition to just using the ones already selected by the user
*/
Product.Config.prototype.getInScopeProductIds = function(optionalAllowedProducts) {

    var childProducts = this.config.childProducts;
    var allowedProducts = [];

    if ((typeof optionalAllowedProducts != 'undefined') && (optionalAllowedProducts.length > 0)) {
       // alert("starting with: " + optionalAllowedProducts.inspect());
        allowedProducts = optionalAllowedProducts;
    }

    for(var s=0, len=this.settings.length-1; s<=len; s++) {
        if (this.settings[s].selectedIndex <= 0){
            break;
        }
        var selected = this.settings[s].options[this.settings[s].selectedIndex];
        if (s==0 && allowedProducts.length == 0){
            allowedProducts = selected.config.allowedProducts;
        } else {
           // alert("merging: " + allowedProducts.inspect() + " with: " + selected.config.allowedProducts.inspect());
            allowedProducts = allowedProducts.intersect(selected.config.allowedProducts).uniq();
           // alert("to give: " + allowedProducts.inspect());
        }
    }

    //If we can't find any products (because nothing's been selected most likely)
    //then just use all product ids.
    if ((typeof allowedProducts == 'undefined') || (allowedProducts.length == 0)) {
        productIds = Object.keys(childProducts);
    } else {
        productIds = allowedProducts;
    }
    return productIds;
};


Product.Config.prototype.getProductIdOfCheapestProductInScope = function(priceType, optionalAllowedProducts) {

    var childProducts = this.config.childProducts;
    var productIds = this.getInScopeProductIds(optionalAllowedProducts);

    var minPrice = Infinity;
    var lowestPricedProdId = false;

    //Get lowest price from product ids.
    for (var x=0, len=productIds.length; x<len; ++x) {
        var thisPrice = Number(childProducts[productIds[x]][priceType]);
        if (thisPrice < minPrice) {
            minPrice = thisPrice;
            lowestPricedProdId = productIds[x];
        }
    }
    return lowestPricedProdId;
};


Product.Config.prototype.getProductIdOfMostExpensiveProductInScope = function(priceType, optionalAllowedProducts) {

    var childProducts = this.config.childProducts;
    var productIds = this.getInScopeProductIds(optionalAllowedProducts);

    var maxPrice = 0;
    var highestPricedProdId = false;

    //Get highest price from product ids.
    for (var x=0, len=productIds.length; x<len; ++x) {
        var thisPrice = Number(childProducts[productIds[x]][priceType]);
        if (thisPrice >= maxPrice) {
            maxPrice = thisPrice;
            highestPricedProdId = productIds[x];
        }
    }
    return highestPricedProdId;
};



Product.Config.prototype.updateFormProductId = function(productId){
    if (!productId) {
        return false;
    }
    var currentAction = $('product_addtocart_form').action;
    newcurrentAction = currentAction.sub(/product\/\d+\//, 'product/' + productId + '/');
    $('product_addtocart_form').action = newcurrentAction;
    $('product_addtocart_form').product.value = productId;
};


Product.Config.prototype.addParentProductIdToCartForm = function(parentProductId) {
    if (typeof $('product_addtocart_form').cpid != 'undefined') {
        return; //don't create it if we have one..
    }
    var el = document.createElement("input");
    el.type = "hidden";
    el.name = "cpid";
    el.value = parentProductId.toString();
    $('product_addtocart_form').appendChild(el);
};



Product.OptionsPrice.prototype.updateSpecialPriceDisplay = function(price, finalPrice) {

    var prodForm = $('product_addtocart_form');

    var specialPriceBox = prodForm.select('p.special-price');
    var oldPricePriceBox = prodForm.select('p.old-price, p.was-old-price');
    var magentopriceLabel = prodForm.select('span.price-label');

    if (price == finalPrice) {
        specialPriceBox.each(function(x) {x.hide();});
        magentopriceLabel.each(function(x) {x.hide();});
        oldPricePriceBox.each(function(x) {
            x.removeClassName('old-price');
            x.addClassName('was-old-price');
        });
    }else{
        specialPriceBox.each(function(x) {x.show();});
        magentopriceLabel.each(function(x) {x.show();});
        oldPricePriceBox.each(function(x) {
            x.removeClassName('was-old-price');
            x.addClassName('old-price');
        });
    }
};

//This triggers reload of price and other elements that can change
//once all options are selected
Product.Config.prototype.reloadPrice = function() {
    var childProductId = this.getMatchingSimpleProduct();
    var childProducts = this.config.childProducts;
    var usingZoomer = false;
    if(this.config.imageZoomer){
        usingZoomer = true;
    }

    if(childProductId){
        var price = childProducts[childProductId]["price"];
        var finalPrice = childProducts[childProductId]["finalPrice"];
        optionsPrice.productPrice = finalPrice;
        optionsPrice.productPriceBeforeRedemptions = finalPrice;
        optionsPrice.productOldPrice = price;
        optionsPrice.reload();
        optionsPrice.reloadPriceLabels(true);
        optionsPrice.updateSpecialPriceDisplay(price, finalPrice);
        this.updateProductShortDescription(childProductId);
        this.updateProductDescription(childProductId);
        this.updateProductName(childProductId);
        this.updateProductStock(childProductId);
        this.updateProductSku(childProductId);
        this.updateProductAttributes(childProductId);
        /* FG Modify BEGIN */
            this.updateProductSellingpoints(childProductId);
            this.updateProductNumber(childProductId);
            this.updateProductColorCode(childProductId);
            this.updateProductIid(childProductId);
            this.updateProductEan(childProductId);
            this.updateProductCareInstructions(childProductId);
            this.updateProductStorefinder(childProductId);
            this.updateProductBadge(childProductId);
            this.updateProductStyle(childProductId);
            this.updateProductUpsell(childProductId);

            linkbadgeicons();
        /* FG Modify END */
        this.updateFormProductId(childProductId);
        this.addParentProductIdToCartForm(this.config.productId);
        this.showCustomOptionsBlock(childProductId, this.config.productId);
        if (usingZoomer) {
            this.showFullImageDiv(childProductId, this.config.productId);
        }else{
            this.updateProductImage(childProductId);
        }

    } else {
        /*
        var cheapestPid = this.getProductIdOfCheapestProductInScope("finalPrice");
        //var mostExpensivePid = this.getProductIdOfMostExpensiveProductInScope("finalPrice");
        var price = childProducts[cheapestPid]["price"];
        var finalPrice = childProducts[cheapestPid]["finalPrice"];
        optionsPrice.productPrice = finalPrice;
        optionsPrice.productOldPrice = price;
        optionsPrice.reload();
        optionsPrice.reloadPriceLabels(false);
        optionsPrice.updateSpecialPriceDisplay(price, finalPrice);
        this.updateProductShortDescription(false);
        this.updateProductDescription(false);
        this.updateProductName(false);
        this.updateProductStock(false);
        this.updateProductSku(false);
        this.updateProductAttributes(false);
        /* FG Modify BEGIN */ /*
            this.updateProductSellingpoints(false);
            this.updateProductNumber(false);
            this.updateProductColorCode(false);
            this.updateProductIid(false);
            this.updateProductEan(false);
            this.updateProductCareInstructions(false);
            this.updateProductStorefinder(false);
            this.updateProductBadge(false);
            this.updateProductStyle(false);
            this.updateProductUpsell(false);

            linkbadgeicons();
        /* FG Modify BEGIN */ /*
        this.showCustomOptionsBlock(false, false);
        if (usingZoomer) {
            this.showFullImageDiv(false, false);
        }else{
            this.updateProductImage(false);
        }
        if(jQuery('.cloud-zoom, .cloud-zoom-gallery').length){
            buildmedia();
        }
        */
    }
};



Product.Config.prototype.updateProductImage = function(productId) {
    //var imageUrl = this.config.imageUrl;
    if(productId && this.config.childProducts[productId].imageUrl) {
        var imageUrl = this.config.childProducts[productId].imageUrl;
    }a

    if (!imageUrl) {
        return;
    }

    // Galleria update
    var gal = Galleria.get(0);
    var dataArr = new Array();
    for(var i=0;i<imageUrl.length;i++)
    {
        dataArr.push({image: imageUrl[i]});
    }
    gal.load(dataArr);
};

Product.Config.prototype.updateProductName = function(productId) {
    var productName = this.config.productName;
    if (productId && this.config.childProducts[productId].productName) {
        productName = this.config.childProducts[productId].productName;
    }
    $$('#product_addtocart_form div.product-name h1').each(function(el) {
        el.innerHTML = productName;
    });
};

Product.Config.prototype.updateProductShortDescription = function(productId) {
    var shortDescription = this.config.shortDescription;
    if (productId && this.config.childProducts[productId].shortDescription) {
        shortDescription = this.config.childProducts[productId].shortDescription;
    }
    $$('#product_addtocart_form div.short-description div').each(function(el) {
        el.innerHTML = shortDescription;
    });
};

Product.Config.prototype.updateProductDescription = function(productId) {
    var description = this.config.description;
    if (productId && this.config.childProducts[productId].description) {
        description = this.config.childProducts[productId].description;
    }
    $$('.tabs-panels .panel > div.std').each(function(el) {
        el.innerHTML = description;
    });
};

Product.Config.prototype.updateProductStock = function(productId) {
    var stockStatusHtml = this.config.stockStatus;
    if (productId && this.config.childProducts[productId].stockStatus) {
        stockStatusHtml = this.config.childProducts[productId].stockStatus;
    }
    var addToCartHtml = this.config.addToCart;
    if (productId && this.config.childProducts[productId].addToCart) {
        addToCartHtml = this.config.childProducts[productId].addToCart;
    }
    //If config product doesn't already have an additional information section,
    //it won't be shown for associated product either. It's too hard to work out
    //where to place it given that different themes use very different html here
    $$('p.availability').each(function(el) { 
        el.replace(stockStatusHtml);
    });
    $$('div.add-to-box').each(function(el) { 
        el.innerHTML=addToCartHtml;
    });
};

Product.Config.prototype.updateProductSku = function(productId) {
    var skuHtml = this.config.sku;
    if (productId && this.config.childProducts[productId].sku) {
        skuHtml = this.config.childProducts[productId].sku;
    }
    //If config product doesn't already have an additional information section,
    //it won't be shown for associated product either. It's too hard to work out
    //where to place it given that different themes use very different html here
    if($('product-sku')){ $('product-sku').innerHTML=skuHtml } ;
};

Product.Config.prototype.updateProductAttributes = function(productId) {
    var productAttributes = this.config.productAttributes;
    if (productId && this.config.childProducts[productId].productAttributes) {
        productAttributes = this.config.childProducts[productId].productAttributes;
    }
    //If config product doesn't already have an additional information section,
    //it won't be shown for associated product either. It's too hard to work out
    //where to place it given that different themes use very different html here
    $$('div.product-collateral div.box-additional').each(function(el) {
        el.innerHTML = productAttributes;
        decorateTable('product-attribute-specs-table');
    });
};

/* FG Modify BEGIN */

    Product.Config.prototype.updateProductSellingpoints = function(productId) {
        var sellingpoints = this.config.sellingpoints;
        if (productId && this.config.childProducts[productId].sellingpoints) {
            sellingpoints = this.config.childProducts[productId].sellingpoints;
        }
        $$('#product-tabs .detail-panel').each(function(el) {
            el.innerHTML = sellingpoints;
        });
    };

    Product.Config.prototype.updateProductNumber = function(productId) {
        var number = this.config.number;
        if (productId && this.config.childProducts[productId].number) {
            number = this.config.childProducts[productId].number;
        }
        $$('.product-art-nr .art-nr.article').each(function(el) {
            el.innerHTML = number;
        });
    };

    Product.Config.prototype.updateProductColorCode = function(productId) {
        var colorcode = this.config.colorcode;
        if (productId && this.config.childProducts[productId].colorcode) {
            colorcode = this.config.childProducts[productId].colorcode;
        }
        $$('.product-art-nr .art-nr.colorcode').each(function(el) {
            el.innerHTML = colorcode;
        });
    };

    Product.Config.prototype.updateProductIid = function(productId) {
        var iid = this.config.iid;
        if (productId && this.config.childProducts[productId].iid) {
            iid = this.config.childProducts[productId].iid;
        }
        $$('.product-art-nr .iid').each(function(el) {
            el.innerHTML = iid;
        });
    };

    Product.Config.prototype.updateProductEan = function(productId) {
        var ean = this.config.ean;
        if (productId && this.config.childProducts[productId].ean) {
            ean = this.config.childProducts[productId].ean;
        }
        $$('#productean').each(function(el) {
            changeStorelocatorProductEan(ean);
            el.value = ean;
        });
    };

    Product.Config.prototype.updateProductCareInstructions = function(productId) {
        var care_instructions = this.config.careinsandmaterials;
        if (productId && this.config.childProducts[productId].careinsandmaterials) {
            care_instructions = this.config.childProducts[productId].careinsandmaterials;
        }
        $$('.materialcare-panel').each(function(el) {
            el.innerHTML = care_instructions;
        });
    };

    Product.Config.prototype.updateProductBadge = function(productId) {
        var badge = this.config.badge;
        if (productId && this.config.childProducts[productId].badge) {
            badge = this.config.childProducts[productId].badge;
        }
        $$('.desc-badge-panel').each(function(el) {
            el.innerHTML = badge;
        });
    };

    Product.Config.prototype.updateProductStorefinder = function(productId) {
        var storefinder = this.config.storefinder;
        if (productId && this.config.childProducts[productId].storefinder) {
            storefinder = this.config.childProducts[productId].storefinder;
        }
        $$('.box-frame-storefinder').each(function(el) {
            if(storefinder == 0){
                if(!el.hasClassName('hide')){
                    el.addClassName('hide');
                }
            }else{
                if(el.hasClassName('hide')){
                    el.removeClassName('hide');
                }
            }
        });
    };

    Product.Config.prototype.updateProductStyle = function(productId) {
        var style = this.config.stylee;
        if (productId && this.config.childProducts[productId].stylee) {
            style = this.config.childProducts[productId].stylee;
        }
        $$('.product-art-nr .product-style').each(function(el) {
            el.innerHTML = style;
        });
    };

    Product.Config.prototype.updateProductUpsell = function(productId) {
        var upsell = this.config.upsell
        if(productId && this.config.childProducts[productId].upsell) {
            upsell = this.config.childProducts[productId].upsell;
        }

        $$('.box-up-sell').each(function(el) {
            el.update(upsell);
        });
    }
/* FG Modify End */

Product.Config.prototype.showCustomOptionsBlock = function(productId, parentId) {
    var coUrl = this.config.ajaxBaseUrl + "co/?id=" + productId + '&pid=' + parentId;
    var prodForm = $('product_addtocart_form');

   if ($('SCPcustomOptionsDiv')==null) {
      return;
   }
    // For NotLive
    //Effect.Fade('SCPcustomOptionsDiv', { duration: 0.2, from: 1, to: 0.1 });
    //For Live
    Effect.Fade('SCPcustomOptionsDiv', { duration: 0.01, from: 1, to: 0.1 });
    if(productId) {
        //Uncomment the line below if you want an ajax loader to appear while any custom
        //options are being loaded.
        $$('span.scp-please-wait').each(function(el) {el.show()});

        //prodForm.getElements().each(function(el) {el.disable()});
        new Ajax.Updater('SCPcustomOptionsDiv', coUrl, {
          method: 'get',
          evalScripts: true,
          onComplete: function() {
                // For NotLive
                //Effect.Fade('SCPcustomOptionsDiv', { duration: 0.2, from: 0.1, to: 1 });
                //For Live
                Effect.Fade('SCPcustomOptionsDiv', { duration: 0.01, from: 0.1, to: 1 });
                //prodForm.getElements().each(function(el) {el.enable()});

                // Check if Color has Changed
                var lokalchecker = jQuery('.product-view .super-attribute-select.color-selector option:selected').text();
                lokalchecker = createurlstring(lokalchecker)
                if(lokalchecker == customchecker){
                    $$('span.scp-please-wait').each(function(el) {el.hide()});
                }
                if(parentId){
                    customchecker = lokalchecker;
                }
          }
        });
    } else {
        $('SCPcustomOptionsDiv').innerHTML = '';
        try{window.opConfig = new Product.Options([]);} catch(e){}
    }
};

Product.Config.prototype.showFullImageDiv = function(productId, parentId) {
    var imgUrl = this.config.ajaxBaseUrl + "image/?id=" + productId + '&pid=' + parentId;

    var prodForm = $('product_addtocart_form');
    var destElement = false;
    var defaultZoomer = this.config.imageZoomer;

    prodForm.select('.product-img-column').each(function(el) {
        destElement = el;
    });
    // Check if Color has Changed
    var lokalchecker = jQuery('.product-view .super-attribute-select.color-selector option:selected').text();
    lokalchecker = createurlstring(lokalchecker)
    if(lokalchecker == colorchecker){
        return;
    }
    if(parentId){
        colorchecker = lokalchecker;
    }
    setTimeout(function(){
        if(productId) {
            new Ajax.Updater(destElement, imgUrl, {
                method: 'get',
                evalScripts: false,
                onComplete: function(gallery) {
                    destElement.innerHTML = gallery.responseText;
                    if(jQuery('.cloud-zoom, .cloud-zoom-gallery').length){
                        buildmedia();
                        buildcloudzoomlightbox();
                        jQuery('.cloud-zoom, .cloud-zoom-gallery').CloudZoom();
                    }
                    $$('span.scp-please-wait').each(function(el) {el.hide()});
              }
            });
        } else {
            /*
            destElement.innerHTML = defaultZoomer;
            if(jQuery('.cloud-zoom, .cloud-zoom-gallery').length){
                buildmedia();
                buildcloudzoomlightbox();
                jQuery('.cloud-zoom, .cloud-zoom-gallery').CloudZoom();
            }
            */
        }
    },300);
};



Product.OptionsPrice.prototype.reloadPriceLabels = function(productPriceIsKnown) {
    var priceFromLabel = '';
    var prodForm = $('product_addtocart_form');

    if (!productPriceIsKnown && typeof spConfig != "undefined") {
        priceFromLabel = spConfig.config.priceFromLabel;
    }

    var priceSpanId = 'configurable-price-from-' + this.productId;
    var duplicatePriceSpanId = priceSpanId + this.duplicateIdSuffix;

    if($(priceSpanId) && $(priceSpanId).select('span.configurable-price-from-label'))
        $(priceSpanId).select('span.configurable-price-from-label').each(function(label) {
        label.innerHTML = priceFromLabel;
    });

    if ($(duplicatePriceSpanId) && $(duplicatePriceSpanId).select('span.configurable-price-from-label')) {
        $(duplicatePriceSpanId).select('span.configurable-price-from-label').each(function(label) {
            label.innerHTML = priceFromLabel;
        });
    }
};



//SCP: Forces the 'next' element to have it's optionLabels reloaded too
Product.Config.prototype.configureElement = function(element) {
    this.reloadOptionLabels(element);
    if(element.value){
        this.state[element.config.id] = element.value;
        if(element.nextSetting){
            element.nextSetting.disabled = false;
            this.fillSelect(element.nextSetting);
            this.reloadOptionLabels(element.nextSetting);
            this.resetChildren(element.nextSetting);
        }
    }
    else {
        this.resetChildren(element);
    }
    this.reloadPrice();
};


//SCP: Changed logic to use absolute price ranges rather than price differentials
Product.Config.prototype.reloadOptionLabels = function(element){
    var selectedPrice;
    var childProducts = this.config.childProducts;
    
    try {
        //Don't update elements that have a selected option
        if(element.options[element.selectedIndex].config){
            return;
        }
    } catch(e){}

    for(var i=0;i<element.options.length;i++){
        if(element.options[i].config){
            var cheapestPid = this.getProductIdOfCheapestProductInScope("finalPrice", element.options[i].config.allowedProducts);
            var mostExpensivePid = this.getProductIdOfMostExpensiveProductInScope("finalPrice", element.options[i].config.allowedProducts);
            var cheapestFinalPrice = childProducts[cheapestPid]["finalPrice"];
            var mostExpensiveFinalPrice = childProducts[mostExpensivePid]["finalPrice"];
            element.options[i].text = this.getOptionLabel(element.options[i].config, cheapestFinalPrice, mostExpensiveFinalPrice);
        }
    }
};

//SCP: Changed label formatting to show absolute price ranges rather than price differentials
Product.Config.prototype.getOptionLabel = function(option, lowPrice, highPrice){

    var str = option.label;

    if (!this.config.showPriceRangesInOptions) {
        return str;
    }

    var to = ' ' + this.config.rangeToLabel + ' ';
    var separator = ': ';

    lowPrices = this.getTaxPrices(lowPrice);
    highPrices = this.getTaxPrices(highPrice);

    if(lowPrice && highPrice){
        if (lowPrice != highPrice) {
            if (this.taxConfig.showBothPrices) {
                str+= separator + this.formatPrice(lowPrices[2], false) + ' (' + this.formatPrice(lowPrices[1], false) + ' ' + this.taxConfig.inclTaxTitle + ')';
                str+= to + this.formatPrice(highPrices[2], false) + ' (' + this.formatPrice(highPrices[1], false) + ' ' + this.taxConfig.inclTaxTitle + ')';
            } else {
                str+= separator + this.formatPrice(lowPrices[0], false);
                str+= to + this.formatPrice(highPrices[0], false);
            }
        } else {
            if (this.taxConfig.showBothPrices) {
                str+= separator + this.formatPrice(lowPrices[2], false) + ' (' + this.formatPrice(lowPrices[1], false) + ' ' + this.taxConfig.inclTaxTitle + ')';
            } else {
                str+= separator + this.formatPrice(lowPrices[0], false);
            }
        }
    }
    return str;
};


//SCP: Refactored price calculations into separate function
Product.Config.prototype.getTaxPrices = function(price) {
    var price = parseFloat(price);

    if (this.taxConfig.includeTax) {
        var tax = price / (100 + this.taxConfig.defaultTax) * this.taxConfig.defaultTax;
        var excl = price - tax;
        var incl = excl*(1+(this.taxConfig.currentTax/100));
    } else {
        var tax = price * (this.taxConfig.currentTax / 100);
        var excl = price;
        var incl = excl + tax;
    }

    if (this.taxConfig.showIncludeTax || this.taxConfig.showBothPrices) {
        price = incl;
    } else {
        price = excl;
    }

    return [price, incl, excl];
};


//SCP: Forces price labels to be updated on load
//so that first select shows ranges from the start
document.observe("dom:loaded", function() {
    //Really only needs to be the first element that has configureElement set on it,
    //rather than all.
    $('product_addtocart_form').getElements().each(function(el) {
        if(el.type == 'select-one' && jQuery('container2-wrapper').length) {
            if(el.options && (el.options.length > 1)) {
                el.options[0].selected = true;
                spConfig.reloadOptionLabels(el);
            }
        }
    });
});
