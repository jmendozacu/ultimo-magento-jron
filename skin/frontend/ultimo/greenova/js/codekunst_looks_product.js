if(typeof Codekunst_Product == 'undefined') {
    var Codekunst_Product = {};
}

Codekunst_Product.Config = Class.create(Product.Config, {
    initialize: function(config){
        this.config     = config;
        this.taxConfig  = this.config.taxConfig;

        // changes for multiple configurable products with options on one page
        var settingsClassToSelect = '.super-attribute-select_'+this.config.productId;
        this.settings   = $$(settingsClassToSelect);

        this.state      = new Hash();
        this.priceTemplate = new Template(this.config.template);
        this.prices     = config.prices;

        this.settings.each(function(element){
            Event.observe(element, 'change', this.configure.bind(this))
        }.bind(this));

        // fill state
        this.settings.each(function(element){
            var attributeId = element.id.replace(/[a-z]*/, '');
            attributeId = attributeId.replace(/_.*/, ''); // changes for multiple configurable products with options on one page
            if(attributeId && this.config.attributes[attributeId]) {
                element.config = this.config.attributes[attributeId];
                element.attributeId = attributeId;
                this.state[attributeId] = false;
            }
        }.bind(this))

        // Init settings dropdown
        var childSettings = [];
        for(var i=this.settings.length-1;i>=0;i--){
            var prevSetting = this.settings[i-1] ? this.settings[i-1] : false;
            var nextSetting = this.settings[i+1] ? this.settings[i+1] : false;
            if(i==0){
                this.fillSelect(this.settings[i])
            }
            else {
                this.settings[i].disabled=true;
            }
            $(this.settings[i]).childSettings = childSettings.clone();
            $(this.settings[i]).prevSetting   = prevSetting;
            $(this.settings[i]).nextSetting   = nextSetting;
            childSettings.push(this.settings[i]);
        }

        // Set default values - from config and overwrite them by url values
        if (config.defaultValues) {
            this.values = config.defaultValues;
        }

        var separatorIndex = window.location.href.indexOf('#');
        if (separatorIndex != -1) {
            var paramsStr = window.location.href.substr(separatorIndex+1);
            var urlValues = paramsStr.toQueryParams();
            if (!this.values) {
                this.values = {};
            }
            for (var i in urlValues) {
                this.values[i] = urlValues[i];
            }
        }

        this.configureForValues();
        document.observe("dom:loaded", this.configureForValues.bind(this));
    },
    fillSelect: function(element){
        var attributeId = element.id.replace(/[a-z]*/, '');
        attributeId = attributeId.replace(/_.*/, ''); // changes for multiple configurable products with options on one page
        var options = this.getAttributeOptions(attributeId);
        this.clearSelect(element);
        element.options[0] = new Option('', '');
        element.options[0].innerHTML = this.config.chooseText;

        var prevConfig = false;
        if(element.prevSetting){
            prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
        }

        if(options) {
            var index = 1;
            for(var i=0;i<options.length;i++){
                var allowedProducts = [];
                if(prevConfig) {
                    for(var j=0;j<options[i].products.length;j++){
                        if(prevConfig.config.allowedProducts
                            && prevConfig.config.allowedProducts.indexOf(options[i].products[j])>-1){
                            allowedProducts.push(options[i].products[j]);
                        }
                    }
                } else {
                    allowedProducts = options[i].products.clone();
                }

                if(allowedProducts.size()>0){
                    options[i].allowedProducts = allowedProducts;
                    element.options[index] = new Option(this.getOptionLabel(options[i], options[i].price), options[i].id);
                    element.options[index].config = options[i];
                    index++;
                }
            }
        }
    },
    reloadPrice: function(){
        var price    = 0;
        var oldPrice = 0;
        for(var i=this.settings.length-1;i>=0;i--){
            var selected = this.settings[i].options[this.settings[i].selectedIndex];
            if(selected.config){
                price    += parseFloat(selected.config.price);
                oldPrice += parseFloat(selected.config.oldPrice);
            }
        }



        optionsPrices[this.config.productId].changePrice('config', {'price': price, 'oldPrice': oldPrice});
        optionsPrices[this.config.productId].reload();

        // fire event to refresh total price for this look
        $(document).fire('looks:recalculate-total', {
            productId: this.config.productId
        });

        return price;

        if($('product-price-'+this.config.productId)){
            $('product-price-'+this.config.productId).innerHTML = price;
        }
        this.reloadOldPrice();
    }
});

if(typeof Codekunst_UpsellProduct == 'undefined') {
    var Codekunst_UpsellProduct = {};
}

Codekunst_UpsellProduct.Config = Class.create(Product.Config, {
    initialize: function(config){
        this.config     = config;
        this.taxConfig  = this.config.taxConfig;

        // changes for multiple configurable products with options on one page
        var settingsClassToSelect = '.super-attribute-select_'+this.config.productId;
        this.settings   = $$(settingsClassToSelect);

        this.state      = new Hash();
        this.priceTemplate = new Template(this.config.template);
        this.prices     = config.prices;

        this.settings.each(function(element){
            Event.observe(element, 'change', this.configure.bind(this))
        }.bind(this));

        // fill state
        this.settings.each(function(element){
            var attributeId = element.id.replace(/[a-z]*/, '');
            attributeId = attributeId.replace(/_.*/, ''); // changes for multiple configurable products with options on one page
            if(attributeId && this.config.attributes[attributeId]) {
                element.config = this.config.attributes[attributeId];
                element.attributeId = attributeId;
                this.state[attributeId] = false;
            }
        }.bind(this))

        // Init settings dropdown
        var childSettings = [];
        for(var i=this.settings.length-1;i>=0;i--){
            var prevSetting = this.settings[i-1] ? this.settings[i-1] : false;
            var nextSetting = this.settings[i+1] ? this.settings[i+1] : false;
            if(i==0){
                this.fillSelect(this.settings[i])
            }
            else {
                this.settings[i].disabled=true;
            }
            $(this.settings[i]).childSettings = childSettings.clone();
            $(this.settings[i]).prevSetting   = prevSetting;
            $(this.settings[i]).nextSetting   = nextSetting;
            childSettings.push(this.settings[i]);
        }

        // Set default values - from config and overwrite them by url values
        if (config.defaultValues) {
            this.values = config.defaultValues;
        }

        var separatorIndex = window.location.href.indexOf('#');
        if (separatorIndex != -1) {
            var paramsStr = window.location.href.substr(separatorIndex+1);
            var urlValues = paramsStr.toQueryParams();
            if (!this.values) {
                this.values = {};
            }
            for (var i in urlValues) {
                this.values[i] = urlValues[i];
            }
        }

        this.configureForValues();
        document.observe("dom:loaded", this.configureForValues.bind(this));
    },
    fillSelect: function(element){
        var attributeId = element.id.replace(/[a-z]*/, '');
        attributeId = attributeId.replace(/_.*/, ''); // changes for multiple configurable products with options on one page
        var options = this.getAttributeOptions(attributeId);
        this.clearSelect(element);
        element.options[0] = new Option('', '');
        element.options[0].innerHTML = this.config.chooseText;

        var prevConfig = false;
        if(element.prevSetting){
            prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
        }

        if(options) {
            var index = 1;
            for(var i=0;i<options.length;i++){
                var allowedProducts = [];
                if(prevConfig) {
                    for(var j=0;j<options[i].products.length;j++){
                        if(prevConfig.config.allowedProducts
                            && prevConfig.config.allowedProducts.indexOf(options[i].products[j])>-1){
                            allowedProducts.push(options[i].products[j]);
                        }
                    }
                } else {
                    allowedProducts = options[i].products.clone();
                }

                if(allowedProducts.size()>0){
                    options[i].allowedProducts = allowedProducts;
                    element.options[index] = new Option(this.getOptionLabel(options[i], options[i].price), options[i].id);
                    element.options[index].config = options[i];
                    index++;
                }
            }
        }
    },
    getMatchingSimpleProduct: function(){
        var inScopeProductIds = this.getInScopeProductIds();
        if ((typeof inScopeProductIds != 'undefined') && (inScopeProductIds.length == 1)) {
            return inScopeProductIds[0];
        }
        return false;
    },
    getProductIdOfMostExpensiveProductInScope: function(priceType, optionalAllowedProducts) {

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
    },
    getInScopeProductIds: function(optionalAllowedProducts) {

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
    },
    reloadPrice: function(){
        var childProductId = this.getMatchingSimpleProduct();
        if(!childProductId) {
            childProductId = this.getProductIdOfMostExpensiveProductInScope("finalPrice")
        }
        var childProducts = this.config.childProducts;

        if(childProductId){
            var price = childProducts[childProductId]["price"];
            var finalPrice = childProducts[childProductId]["finalPrice"];
            optionsPrices[this.config.productId].productPrice = finalPrice;
            optionsPrices[this.config.productId].productPriceBeforeRedemptions = finalPrice;
            optionsPrices[this.config.productId].productOldPrice = price;
            optionsPrices[this.config.productId].changePrice('config', {'price': 0, 'oldPrice': 0});
            optionsPrices[this.config.productId].reload();
        } else {
        }
    }
});
