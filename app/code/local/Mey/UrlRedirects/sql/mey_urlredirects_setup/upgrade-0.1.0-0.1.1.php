<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$_categoryCache = array();
$_oldUrls = array(
    "de/shop/" => "",
    "de/shop/abteilung/herren/" => "herren",
    "de/shop/damen/" => "damen",
    "de/shop/damen/kategorie/bhs/207,208,227,229,230,275,602/" => "damen/bhs",
    "de/shop/damen/kategorie/bodies/231,232/" => "damen/bodies",
    "de/shop/damen/kategorie/body-dress/315,316/" => "damen/body-dress",
    "de/shop/damen/kategorie/long-pants/221,222/" => "damen/long-pants-hosen/long-pants",
    "de/shop/damen/kategorie/long-pants_hosen/221,222/" => "damen/long-pants-hosen",
    "de/shop/damen/kategorie/nachthemden/198,199/" => "damen/nachthemden",
    "de/shop/damen/kategorie/schlafanzuege/200,201,202,313/" => "damen/schlafanzuege",
    "de/shop/damen/kategorie/shirts/209,216,217,218,273/" => "damen/shirts",
    "de/shop/damen/kategorie/slips/219,220,223,224,225,226,233,279,297,318,401,411,672/" => "damen/slips",
    "de/shop/damen/kategorie/slips/219,220,223,224,225,226,233,279,297,318,401,672/" => "damen/slips",
    "de/shop/damen/kategorie/tops/213,214,215,228/" => "damen/tops",
    "de/shop/damen/kollektion/mey_basics/3/" => "damen/mey-basics",
    "de/shop/damen/kollektion/mey_dessous/6/" => "damen/mey-dessous",
    "de/shop/damen/kollektion/mey_fashion/4/" => "damen/mey-fashion",
    "de/shop/damen/kollektion/mey_night/2/" => "damen/mey-night",
    "de/shop/damen/kollektion/mey_sports/5/" => "damen/mey-night",
    "de/shop/damen/serie/mey_lights/3501/" => "damen/mey-lights",
    "de/shop/herren/" => "herren",
    "de/shop/herren/kategorie/long-pants/221,222/" => "herren/long-pants-hosen/long-pants",
    "de/shop/herren/kategorie/long-pants_hosen/221,222/" => "herren/long-pants-hosen",
    "de/shop/herren/kategorie/nachthemden/198,199/" => "herren/nachthemden",
    "de/shop/herren/kategorie/schlafanzuege/200,201,202/" => "herren/schlafanzuege",
    "de/shop/herren/kategorie/shorties/235,236,237,238,239/" => "herren/shorties",
    "de/shop/herren/kategorie/slips/219,224,225,226,234,462,702,712,2511/" => "herren/slips",
    "de/shop/herren/kategorie/slips/219,224,225,226,234,462,702,712/" => "herren/slips",
    "de/shop/herren/kollektion/mey_fashion/4/" => "herren/mey-fashion",
    "de/shop/herren/kollektion/mey_night/2/" => "herren/mey-night",
    "de/shop/herren/kollektion/mey_sports/5/" => "herren/mey-sports",
    "de/shop/herren/sale/schlafanzuege/200,201,202/" => "herren/schlafanzuege",
    "de/shop/herren/sale/shirts/209,216,217/" => "herren/shirts",
    "de/shop/herren/sale/shorties/235,236,237,239/" => "herren/shorties",
    "de/shop/herren/sale/slips/219,224,249,462/" => "herren/slips",
    "de/shop/herren/serie/dry_cotton/55/" => "herren/dry-cotton",
    "de/shop/home/" => "",
    "de/shop/kategorien/bh-hemden/275/" => "damen/bhs/bh-hemden",
    "de/shop/kategorien/boxer-shorts/238/" => "herren/shorties/boxer-shorts",
    "de/shop/kategorien/bustiers/229/" => "damen/bhs/bustiers",
    "de/shop/sale/" => "sale",
    "de/shop/sale/damen/" => "sale/damen",
    "de/shop/sale/herren" => "sale/herren",
    "de/index.php?sxx_page=online.store.home.men&sxx_call[5a8f68bafa][reset]=true&sxx_call[70adc3bfc9][DID]=3&sxx_call[b315d79ee1][id]=b315d79ee1_2&sxx_call[b315d79ee1][id]=b315d79ee1_2" => "herren",
    "de/index.php?sxx_page=online.store.home.sale&sxx_call[5a8f68bafa][reset]=true&sxx_call[15c2c7fc38][DID]=211&sxx_call[b315d79ee1][id]=b315d79ee1_4&sxx_call[b315d79ee1][id]=b315d79ee1_4" => "sale",
    "de/index.php?sxx_page=online.store.home.women&sxx_call[5a8f68bafa][reset]=true&sxx_call[70adc3bfc9][DID]=2&sxx_call[b315d79ee1][id]=b315d79ee1_0&sxx_call[b315d79ee1][id]=b315d79ee1_0" => "damen",
    "de/shop/sale/herren/" => "sale/herren",
    "nl/shop/herren/?sxx_language=nl" => "herren",
    "nl/shop/damen/?sxx_language=nl" => "damen",
    "de/shop/kategorien/bhs/207,208,227,229,230,275,602/" => "damen/bhs",
    "de/shop/kategorien/long-pants_hosen/221,222/" => "catalogsearch/result/?q=long-pants",
    "de/shop/kategorien/molding-bhs/602/" => "damen/bhs/molding-bhs",
    "de/shop/kategorien/nachthemden/198,199/" => "catalogsearch/result/?q=nachthemd",
    "de/shop/kategorien/schlafanzuege/200,201,202,313/" => "catalogsearch/result/?q=anzug",
    "de/shop/kategorien/shirts/209,216,217,218,273,274,2491/" => "catalogsearch/result/?q=shirt",
    "de/shop/kategorien/shorties/237/" => "herren/slips/shorties",
    "de/shop/kategorien/slips/219,220,223,224,225,226,233,234,249,279,297,318,401,411,462,672,702,712/" => "catalogsearch/result/index/?q=slip",
    "de/shop/serie/Emotion/18/" => "catalogsearch/result/?q=emotion",
    "de/shop/serie/Noblesse/66/" => "catalogsearch/result/?q=noblesse",
    "de/shop/kategorien/bhs/207,208,227,229,230,275,602" => "damen/bhs",
    "nl/shop/kategorien/pyjama_s/200,201,202,313/" => "catalogsearch/result/?q=pyjama",
);

foreach($_oldUrls as $oldUrl => $newUrl) {
    $storeCode = "de";
    if(preg_match('/^en\//', $oldUrl) === 1) {
        $storeCode = "en";
    } elseif(preg_match('/^nl\//', $oldUrl) === 1) {
        $storeCode = "nl";
    }
    $oldUrl = preg_replace('/^' . $storeCode . '\//', "", $oldUrl);

    $storeId = Mage::getModel('core/store')->load($storeCode, 'code')->getId();

    $rewrite = Mage::getModel("core/url_rewrite")
        ->setIsSystem(0)
        ->setData('store_id', $storeId)
        ->setOptions("RP")
        ->setIdPath($oldUrl)
        ->setTargetPath($newUrl)
        ->setRequestPath($oldUrl);
    $rewrite->save();
}

$installer->endSetup();

