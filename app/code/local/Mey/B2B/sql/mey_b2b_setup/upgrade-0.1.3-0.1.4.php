<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$storeId = Mage::getResourceModel("core/store_collection")->addFieldToFilter("code", "b2b_de")->getFirstItem()->getId();

$homepage = Mage::getModel("cms/page")->setStoreId($storeId)->load("home", "identifier");
$homepage->setContent('<div class="home-blocks-static">
    <div class="no-gutter grid12-6	left">
        <div class="box long">{{block type="cms/block" block_id="block_home_left1"}}</div>
    </div>
    <div class="no-gutter grid12-6	right">
        <div class="box long">{{block type="cms/block" block_id="block_home_right1"}}</div>
    </div>
</div>
<br style="clear:both"/>');
$homepage->setStores(array($storeId));
$homepage->save();

$blockLeft = Mage::getModel("cms/block")->setStoreId($storeId)->load("block_home_left1", "identifier");
$blockLeft->setContent('<a href="{{store_url="damen/kollektionen"}}"><img class="desktop" src="{{media url="wysiwyg/HomeBoxen/desktop/b2b/women.png"}}" alt="Damen" /></a>
 <a href="{{store_url="damen/kollektionen"}}"><img class="mobile" src="{{media url="wysiwyg/HomeBoxen/mobile/b2b/women.png"}}" alt="Damen" /></a>
<div class="caption with-background">
    <h3 class="heading ucase">Damen</h3>
    <hr>
    <div class="buttons-set">
        <a href="{{store_url="damen/kollektionen"}}" class="button light">Jetzt ordern</a>
    </div>
</div>');
$blockLeft->save();

$blockRight = Mage::getModel("cms/block")->setStoreId($storeId)->load("block_home_right1", "identifier");
$blockRight->setContent('<a href="{{store_url="herren/kollektionen"}}"><img class="desktop" src="{{media url="wysiwyg/HomeBoxen/desktop/b2b/men.png"}}" alt="Herren" /></a>
<a href="{{store_url="herren/kollektionen"}}"><img class="mobile" src="{{media url="wysiwyg/HomeBoxen/mobile/b2b/men.png"}}" alt="Herren" /></a>
<div class="caption with-background">
    <h3 class="heading ucase">Herren</h3>
    <hr>
    <div class="buttons-set">
        <a href="{{store_url="herren/kollektionen"}}" class="button light">Jetzt ordern</a>
    </div>
</div>');
$blockRight->save();

$blockTopLinks = Mage::getModel("cms/block")->setStoreId($storeId)->load("block_header_top_links", "identifier");
$blockTopLinks->setContent('<ul>
    <li class="first link-contact">
        <a href="{{store url="kontakt"}}" title="Kontakt">Kontakt</a>
    </li>
    <li class="link-newsletter">
        <a href="{{store url="newsletter"}}" title="Zum Mey Newsletter">Newsletter</a>
    </li>
    <li class="link-customer">
        <a href="{{store url="customer/account/"}}" title="Mein Konto">Mein Konto</a>
        <div class="customer-service-wrapper">
            <span class="caret"></span>
            {{block type="customer/form_login" name="mini_login" template="customer/form/mini.login.phtml" }}
        </div>
    </li>
    <li class="link-wishlist last">
        <a href="{{store url="wishlist"}}" title="Merkliste">Merkliste</a>
    </li>
</ul>');
$blockTopLinks->save();

$installer->endSetup();
