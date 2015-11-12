<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$_categoryCache = array();
$_oldUrls = array(
    "de/shop/kategorien/jazz-pants/224/" => 20412,
    "nl/shop/kategorien/jazz-pants/224/" => 20412,
    "en/shop/kategorien/jazz-pants/224/" => 20412,
    "de/shop/damen/kategorie/shirts/209,216,217,218,273,274/" => 20212,
    "nl/shop/damen/kategorie/shirts/209,216,217,218,273,274/" => 20212,
    "en/shop/damen/kategorie/shirts/209,216,217,218,273,274/" => 20212,
    "nl/shop/herren/kategorie/shirts/209,216,217,218/" => 19762,
    "en/shop/herren/kategorie/shirts/209,216,217,218/" => 19762,
    "de/shop/herren/kategorie/shirts/209,216,217,218/" => 19762,
    "de/shop/kategorien/taillen-slips/223/" => 20392,
    "nl/shop/kategorien/taillen-slips/223/" => 20392,
    "en/shop/kategorien/taillen-slips/223/" => 20392,
    "de/shop/herren/kategorie/shorties/235,236,237,238,239,2501/" => 19872,
    "nl/shop/herren/kategorie/shorties/235,236,237,238,239,2501/" => 19872,
    "en/shop/herren/kategorie/shorties/235,236,237,238,239,2501/" => 19872,
    "de/shop/kategorien/tops_breiter_traeger/215/" => 20202,
    "nl/shop/kategorien/tops_breiter_traeger/215/" => 20202,
    "en/shop/kategorien/tops_breiter_traeger/215/" => 20202,
    "de/shop/kategorien/panties/220/" => 20482,
    "nl/shop/kategorien/panties/220/" => 20482,
    "en/shop/kategorien/panties/220/" => 20482,
    "de/shop/kategorien/mini-slips/225/" => 20572,
    "nl/shop/kategorien/mini-slips/225/" => 20572,
    "en/shop/kategorien/mini-slips/225/" => 20572,
    "de/shop/kategorien/schalen-bhs/227/" => 20662,
    "nl/shop/kategorien/schalen-bhs/227/" => 20662,
    "en/shop/kategorien/schalen-bhs/227/" => 20662,
    "de/shop/kategorien/spaghetti_tops/214/" => 20302,
    "nl/shop/kategorien/spaghetti_tops/214/" => 20302,
    "en/shop/kategorien/spaghetti_tops/214/" => 20302,
    "de/shop/kategorien/classic-slips/234/" => 19862,
    "nl/shop/kategorien/classic-slips/234/" => 19862,
    "en/shop/kategorien/classic-slips/234/" => 19862,
    "de/shop/kategorien/hueft-slips/672/" => 20402,
    "nl/shop/kategorien/hueft-slips/672/" => 20402,
    "en/shop/kategorien/hueft-slips/672/" => 20402,
    "de/shop/kategorien/super-mini/462/" => 20672,
    "nl/shop/kategorien/super-mini/462/" => 20672,
    "en/shop/kategorien/super-mini/462/" => 20672,
    "de/shop/kategorien/hip-pants/297/" => 20622,
    "nl/shop/kategorien/hip-pants/297/" => 20622,
    "en/shop/kategorien/hip-pants/297/" => 20622,
    "de/shop/kategorien/strings/226/" => 20552,
    "nl/shop/kategorien/strings/226/" => 20552,
    "en/shop/kategorien/strings/226/" => 20552,
    "de/shop/kategorien/american-pants/279/" => 20952,
    "nl/shop/kategorien/american-pants/279/" => 20952,
    "en/shop/kategorien/american-pants/279/" => 20952,
    "de/shop/kategorien/hip-shorts/239/" => 20692,
    "nl/shop/kategorien/hip-shorts/239/" => 20692,
    "en/shop/kategorien/hip-shorts/239/" => 20692,
    "de/shop/kategorien/push_up-bhs/230/" => 20652,
    "nl/shop/kategorien/push_up-bhs/230/" => 20652,
    "en/shop/kategorien/push_up-bhs/230/" => 20652,
);

foreach($_oldUrls as $oldUrl => $categoryId) {
    if(!array_key_exists($categoryId, $_categoryCache)) {
        $_categoryCache[$categoryId] = Mage::getModel("catalog/category")->load($categoryId);
    }

    if(is_null($_categoryCache[$categoryId]->getId())) {
        continue;
    }
}

foreach($_oldUrls as $oldUrl => $categoryId) {
    $storeCode = "de";
    if(preg_match('/^en\//', $oldUrl) === 1) {
        $storeCode = "en";
    } elseif(preg_match('/^nl\//', $oldUrl) === 1) {
        $storeCode = "nl";
    }
    $oldUrl = preg_replace('/^' . $storeCode . '\//', "", $oldUrl);

    $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();

    if(!array_key_exists($categoryId, $_categoryCache)) {
        $_categoryCache[$categoryId] = Mage::getModel("catalog/category")->load($categoryId);
    }

    if(is_null($_categoryCache[$categoryId]->getId())) {
        continue;
    }

    $rewrite = Mage::getModel("core/url_rewrite")
        ->setIsSystem(0)
        ->setData('store_id', $storeId)
        ->setOptions("RP")
        ->setIdPath($oldUrl)
        ->setTargetPath($_categoryCache[$categoryId]->getUrlPath())
        ->setRequestPath($oldUrl);
    $rewrite->save();
}

$installer->endSetup();

