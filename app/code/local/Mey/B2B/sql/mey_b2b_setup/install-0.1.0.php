<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// Creating the new website, store and proper store views

// add website
/** @var $website Mage_Core_Model_Website */
$website = Mage::getModel('core/website');
$website->setCode('b2b')
    ->setName('Mey B2B')
    ->save();

// add store group
/** @var $storeGroup Mage_Core_Model_Store_Group */
$storeGroup = Mage::getModel('core/store_group');
$storeGroup->setWebsiteId($website->getId())
    ->setName('Mey B2B')
    ->setRootCategoryId(2)
    ->save();

// add store
/** @var $store Mage_Core_Model_Store */
$store = Mage::getModel('core/store');
$store->setCode('b2b_de')
    ->setWebsiteId($storeGroup->getWebsiteId())
    ->setGroupId($storeGroup->getId())
    ->setName('Deutsch')
    ->setIsActive(1)
    ->save();

$websiteId = $website->getId();
$storeId = $store->getId();


// copy CMS Blocks and CMS Pages for new website

$cmsModel = Mage::getModel('cms/page');
$blockModel = Mage::getModel('cms/block');
$blockCollection = $blockModel->getCollection()->addStoreFilter(1, false);
$cmsCollection = $cmsModel->getCollection()->addStoreFilter(1);

// change global cms block to only show up in stores 1, 2
// block_footer_row2_column1
$blockModel->load(40)->setData('stores', array(1,2))->save();

foreach ($blockCollection as $block) {
    $newBlock = Mage::getModel('cms/block');
    $data = $block->getData();

    if (array_key_exists("identifier", $data)) {
        if ($data["identifier"] == "block_footer_column2") {
            // 5.8.3
            $data["content"] = str_replace('<li><a href="{{store url="partnerprogramm"}}">Partnerprogramm</a></li>', '', $data["content"]);
        } else if ($data["identifier"] == "block_footer_column1") {
            // 5.8.5
            $data["content"] = str_replace('Mey Service', 'Mey B2B', $data["content"]);
        } else if ($data["identifier"] == "block_footer_payment") {
            // 5.8.8
            $data["content"] = "alle Preise ohne gesetzliche Umsatzsteuer";
        } else if ($data["identifier"] == "header_text_slider") {
            $data["content"] = '{{block type="ultraslideshow/slideshow"
template="infortis/ultraslideshow/slideshow.phtml"
slides="header_text_slide_element1,header_text_slide_element2"
timeout="8000"
effect="backSlide"
}}';
        } else if ($data["identifier"] == "header_text_slide_element1") {
            $data["content"] = 'Mindestbestellwert 150,00 &euro;';
        } else if ($data["identifier"] == "header_text_slide_element2") {
            $data["content"] = '<a href="{{store url="newsletter"}}">Jetzt für den B2B-Newsletter anmelden</a>';
        } else if ($data["identifier"] == "block_footer_column4") {
            // 5.8.6 / 5.8.2
            $data["content"] = '<div class="collapsible mobile-collapsible">
	<h5 class="block-title heading">Auszeichnungen</h5>
	<div class="block-content">
		<p style="color: red;">TODO</p>
	</div>
</div>';
        } else if ($data["identifier"] == "block_footer_column5") {
            // 5.8.1
            $data["content"] = str_replace('<p>Jetzt anmelden und <br /><span style="font-size: medium;"><strong>5 &euro; Gutschein</strong></span> erhalten</p>', '', $data["content"]);
        } else if($data["identifier"] == "block_home_left1") {
            $data["content"] = '<img class="desktop" src="{{media url="wysiwyg/HomeBoxen/desktop/b2b/women.png"}}" alt="Damen" />
<img class="mobile" src="{{media url="wysiwyg/HomeBoxen/mobile/b2b/women.png"}}" alt="Damen" />
<div class="caption with-background">
    <h3 class="heading ucase">Damen</h3>
    <hr>
    <div class="buttons-set">
        <a href="{{store_url="damen/kollektionen"}}" class="button light">Jetzt ordern</a>
    </div>
</div>';
        } else if($data["identifier"] == "block_home_right1") {
            $data["content"] = '<img class="desktop" src="{{media url="wysiwyg/HomeBoxen/desktop/b2b/men.png"}}" alt="Herren" />
<img class="mobile" src="{{media url="wysiwyg/HomeBoxen/mobile/b2b/men.png"}}" alt="Herren" />
<div class="caption with-background">
    <h3 class="heading ucase">Herren</h3>
    <hr>
    <div class="buttons-set">
        <a href="{{store_url="herren/kollektionen"}}" class="button light">Jetzt ordern</a>
    </div>
</div>';
        }
        // skip certain cms blocks completely
        // 5.8.7, 5.6
        $skipIdentifier = array(
            "block_footer_primary_bottom_middle",
            "block_footer_primary_bottom_left",
            "block_footer_primary_bottom_right",
            "newsletter_subscribe_box",
            "block_footer_row2_column1"
        );
        if (in_array($data["identifier"], $skipIdentifier)) {
            continue;
        }
    }

    unset($data["block_id"]);
    $data["stores"] = array($storeId);
    $newBlock->setData($data);
    $newBlock->save();
}

$newBlock = Mage::getModel("cms/block");
$newBlock->setIdentifier("block_home_campaigns")
    ->setStores(array($storeId))
    ->setContent('<h2>Aktionen / Specials</h2>
<div class="campaign-blocks-static home-blocks-static">
    <div class="no-gutter grid12-3 left">
        <div class="box long"><img class="desktop" src="{{media url="wysiwyg/HomeBoxen/desktop/b2b/women-basics.png"}}" alt="Basics Damen">
            <img class="mobile" src="{{media url="wysiwyg/HomeBoxen/mobile/b2b/women-basics.png"}}" alt="Basics Damen">
            <div class="caption with-background">
                <h3 class="heading ucase">Basics Damen</h3>
                <hr>
                <div class="buttons-set">
                    <a href="{{store_url="damen/kollektionen/"}}" class="button light">Jetzt ordern</a>
                </div>
            </div>
        </div>
    </div>
    <div class="no-gutter grid12-3 middle">
        <div class="box long"><img class="desktop" src="{{media url="wysiwyg/HomeBoxen/desktop/b2b/sale.png"}}" alt="Sale">
            <img class="mobile" src="{{media url="wysiwyg/HomeBoxen/mobile/b2b/sale.png"}}" alt="Sale">
            <div class="caption with-background">
                <h3 class="heading ucase">Sale</h3>
                <hr>
                <div class="buttons-set">
                    <a href="{{store_url="damen/kollektionen/"}}" class="button light">Jetzt ordern</a>
                </div>
            </div>
        </div>
    </div>
    <div class="no-gutter grid12-3 middle">
        <div class="box long"><img class="desktop" src="{{media url="wysiwyg/HomeBoxen/desktop/b2b/mey-247.png"}}" alt="MEY 24/7">
            <img class="mobile" src="{{media url="wysiwyg/HomeBoxen/mobile/b2b/mey-247.png"}}" alt="MEY 24/7">
            <div class="caption with-background">
                <h3 class="heading ucase">MEY 24/7</h3>
                <hr>
                <div class="buttons-set">
                    <a href="{{store_url="damen/kollektionen/"}}" class="button light">Jetzt ordern</a>
                </div>
            </div>
        </div>
    </div>
    <div class="no-gutter grid12-3 right">
        <div class="box long"><img class="desktop" src="{{media url="wysiwyg/HomeBoxen/desktop/b2b/dry-cotton.png"}}" alt="Dry Cotton">
            <img class="mobile" src="{{media url="wysiwyg/HomeBoxen/mobile/b2b/dry-cotton.png"}}" alt="Dry Cotton">
            <div class="caption with-background">
                <h3 class="heading ucase">Dry Cotton</h3>
                <hr>
                <div class="buttons-set">
                    <a href="{{store_url="damen/kollektionen/"}}" class="button light">Jetzt ordern</a>
                </div>
            </div>
        </div>
    </div>
</div>')
    ->setTitle("Startseite Aktionen-Blöcke unten DE B2B")
    ->setIsActive(true)
    ->save();

foreach ($cmsCollection as $page) {
    $newPage = Mage::getModel('cms/page');
    $data = $page->getData();
    unset($data["page_id"]);
    $data["stores"] = array($storeId);
    $newPage->setData($data);

    if($newPage->getIdentifier() == "home") {
        $newPage->setContent('<div class="home-blocks-static">
    <div class="no-gutter grid12-6	left">
        <div class="box long">{{block type="cms/block" block_id="block_home_left1"}}</div>
    </div>
    <div class="no-gutter grid12-6	right">
        <div class="box long">{{block type="cms/block" block_id="block_home_right1"}}</div>
    </div>
</div>
<br style="clear:both"/>
{{block type="cms/block" block_id="block_home_campaigns"}}
');
    }

    $newPage->save();
}

// Change specific config data for new website

$_config = new Mage_Core_Model_Config();
$_options = array(
    'web/url/use_store' => $storeId,
    'design/theme/template' => 'fg_mey_b2b',
    'design/theme/skin' => 'fg_mey_b2b',
    'design/theme/layout' => 'fg_mey_b2b',
    'design/theme/locale' => 'fg_mey_b2b',
    'design/theme/default' => 'fg_mey_b2b',
    'web/secure/base_url' => 'https://www.meyb2b.com/',
    'web/unsecure/base_url' => 'http://www.meyb2b.com/',
    'general/store_information/name' => 'Mey B2B',
    'general/country/default' => 'DE',
    'general/country/allow' => 'DE',
    'sales/minimum_order/active' => 1,
    'sales/minimum_order/amount' => 150,
    'sales/minimum_order/description' => 'Der Mindestbestellwert beträgt 150 €. Dieser Wert wurde bislang noch nicht erreicht.',
    "customerprices/settings/isenable" => 1,
    "customerprices/settings/hide_price" => 0,
    "payment/mey_b2bpayment/active" => 1,
    "payment/bankpayment/active" => 0,
    "payment/saferpaycw_creditcard/active" => 0,
    "payment/paypal_standard/active" => 0,
    "payment/phoenix_cashondelivery/active" => 0,
    "payment/sofort_ideal/active" => 0,
    "payment/saferpaycw_ideal/active" => 0,
    "payment/paypal_billing_agreement/active" => 0,
    "payment/free/active" => 0,
    "payment/codekunst_payglobe/active" => 0,
    "checkoutbyamazon/general/active" => 0,
    "carriers/tablerate/active" => 0,
    "carriers/freeshipping/active" => 1,
    "carriers/freeshipping/title" => "Lieferung nach Vereinbarung",
    "carriers/freeshipping/name" => "",
    "tax/display/type" => 1,
    "tax/display/shipping" => 1,
    "tax/cart_display/price" => 1,
    "tax/cart_display/subtotal" => 1,
    "tax/cart_display/grandtotal" => 1,
    "tax/sales_display/price" => 1,
    "tax/sales_display/subtotal" => 1,
    "tax/sales_display/grandtotal" => 1,
    "checkout/options/guest_checkout" => 0,
    "checkout/options/customer_must_be_logged" => 1,
);

$turpentineBlacklist = Mage::getStoreConfig("turpentine_vcl/urls/url_blacklist", 0);
$_globalOptions = array(
    "customerprices/settings/isenable" => 0,
    "customerprices/settings/hide_price" => 0,
    "sublogin/general/restrict_customer_list_sublogins" => 1,
    "sublogin/form_fields/frontend" => "active",
    "turpentine_vcl/urls/url_blacklist" => $turpentineBlacklist . "\nsublogin",
    "mey_b2b/general/website_id" => $website->getId(),
);

foreach( $_options as $_path => $_value ) {
    $_config->saveConfig( $_path, $_value, 'websites', $websiteId );
}

foreach( $_globalOptions as $_path => $_value ) {
    $_config->saveConfig( $_path, $_value, 'default', 0 );
}

// Add Color Option Values
$installer->run("
# DEFINE
SET @to_website := $websiteId; # the websites_id of the recipient store
SET @to_store := $storeId; # the store_id of the recipient store

INSERT INTO `eav_attribute_label` (`attribute_id`, `store_id`, `value`)
VALUES
    (73, @to_store, 'Details'),
    (136, @to_store, 'In die Topseller?'),
    (137, @to_store, 'Position in Topseller'),
    (153, @to_store, 'Pflege'),
    (182, @to_store, 'Material'),
    (272, @to_store, 'Wäsche Grössen'),
    (282, @to_store, 'Cupgröße'),
    (292, @to_store, 'Brustumfang'),
    (133, @to_store, 'Lieferzeit'),
    (172, @to_store, 'Farbe'),
    (262, @to_store, 'Farbe'),
    (120, @to_store, 'UVP');

INSERT INTO `eav_attribute_option_value` (`option_id`, `store_id`, `value`)
VALUES
    (18187, @to_store, 'Schwarz'),
    (18232, @to_store, 'Schwarz/Weiss'),
    (16892, @to_store, 'Deep Blue'),
    (16542, @to_store, 'Juwel'),
    (18038, @to_store, 'Marine'),
    (18292, @to_store, 'Indigo'),
    (15622, @to_store, 'Shadow'),
    (15612, @to_store, 'Indigo'),
    (15952, @to_store, 'Black Diamond'),
    (18290, @to_store, 'Blue Velvet'),
    (18244, @to_store, 'Neptun'),
    (18287, @to_store, 'Licorice'),
    (15862, @to_store, 'Caribbean Green'),
    (16262, @to_store, 'Espresso'),
    (18249, @to_store, 'Havana'),
    (15892, @to_store, 'Night Sky'),
    (15932, @to_store, 'True Navy'),
    (18251, @to_store, 'Dark Slate Melange'),
    (15652, @to_store, 'Anthracite Melange'),
    (15902, @to_store, 'Ebony'),
    (18247, @to_store, 'Dark Shadow'),
    (16082, @to_store, 'Lilas'),
    (15682, @to_store, 'Anthracite'),
    (15942, @to_store, 'Arctic Blue'),
    (16692, @to_store, 'Seal'),
    (15852, @to_store, 'Dark Slate'),
    (16912, @to_store, 'Mulberry'),
    (16712, @to_store, 'Mahagoni'),
    (16672, @to_store, 'Tulip'),
    (18250, @to_store, 'Schilf Melange'),
    (18104, @to_store, 'Stone Melange'),
    (18286, @to_store, 'Lupine'),
    (16552, @to_store, 'Fuchsia'),
    (15662, @to_store, 'Light Blue'),
    (15642, @to_store, 'Aqua'),
    (15722, @to_store, 'Riviera'),
    (16352, @to_store, 'Flame'),
    (18245, @to_store, 'Ginger'),
    (15922, @to_store, 'Mineral Grey'),
    (18248, @to_store, 'Caffe Macchiato'),
    (18246, @to_store, 'Ruby'),
    (15742, @to_store, 'Red Flame'),
    (16742, @to_store, 'Scarlet'),
    (15912, @to_store, 'Salsa'),
    (15692, @to_store, 'Volcano'),
    (16862, @to_store, 'Aloe'),
    (18289, @to_store, 'Soft Blue'),
    (18243, @to_store, 'Clear Water'),
    (18267, @to_store, 'Hellgrau'),
    (18285, @to_store, 'Hellgrau Melange'),
    (18089, @to_store, 'Pink'),
    (15882, @to_store, 'Light Grey Melange'),
    (18288, @to_store, 'Light Violet'),
    (16872, @to_store, 'Light Phlox'),
    (16902, @to_store, 'Romance'),
    (16652, @to_store, 'Skin'),
    (16702, @to_store, 'Lilac'),
    (18291, @to_store, 'Sandstone'),
    (16922, @to_store, 'Chayenne'),
    (16562, @to_store, 'Ice'),
    (18090, @to_store, 'Bailey'),
    (15872, @to_store, 'Mars Red'),
    (16612, @to_store, 'Lollipop'),
    (18091, @to_store, 'Caramel'),
    (16322, @to_store, 'Soft Skin'),
    (16572, @to_store, 'Limette'),
    (16622, @to_store, 'Spring'),
    (18054, @to_store, 'Bisquit'),
    (16342, @to_store, 'Daisy'),
    (18092, @to_store, 'Perlweiss'),
    (16732, @to_store, 'Milky'),
    (18071, @to_store, 'Champagner'),
    (16882, @to_store, 'Hibiscus'),
    (16092, @to_store, 'Aperol'),
    (18087, @to_store, 'Puder'),
    (16682, @to_store, 'Off White'),
    (18032, @to_store, 'Weiss'),
    (18344, @to_store, 'Oxyd'),
    (18345, @to_store, 'Red Purple'),
    (18346, @to_store, 'Aubergine'),
    (18362, @to_store, 'Jade'),
    (18380, @to_store, 'Dark Cedar'),
    (18381, @to_store, 'Sansibar'),
    (18383, @to_store, 'Salmon'),
    (18384, @to_store, 'Wollweiss'),
    (18385, @to_store, 'Midnight Blue'),
    (18386, @to_store, 'Wild Berry'),
    (18387, @to_store, 'Dunkel Graumelange'),
    (18388, @to_store, 'Grey Melange'),
    (18389, @to_store, 'Sapphire'),
    (18390, @to_store, 'Royal Blue'),
    (18391, @to_store, 'Brandy'),
    (18392, @to_store, 'Graphit'),
    (18393, @to_store, 'Taupe Light'),
    (18394, @to_store, 'Anthrazit Melange'),
    (18395, @to_store, 'Wine'),
    (18396, @to_store, 'Taupe'),
    (18397, @to_store, 'Metallic Silver'),
    (18429, @to_store, 'Earth');
");

$installer->endSetup();
