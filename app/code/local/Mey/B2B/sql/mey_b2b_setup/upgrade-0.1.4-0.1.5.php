<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$storeId = Mage::getResourceModel("core/store_collection")->addFieldToFilter("code", "b2b_de")->getFirstItem()->getId();

$shopLinks = Mage::getModel("cms/block")->setStoreId($storeId)->load("block_footer_shop_links", "identifier");
$shopLinks->setContent('<div class="block-content">
    <ul>
        <li class="first"><a href="{{store url="konditionen"}}">Konditionen</a></li>
        <li><a href="{{store url="datenschutz"}}">Datenschutz</a></li>
        <li class="last"><a href="{{store url="impressum"}}">Impressum</a></li>
    </ul>
</div>');
$shopLinks->save();

$footerBlock1 = Mage::getModel("cms/block")->setStoreId($storeId)->load("block_footer_column1", "identifier");
$footerBlock1->setContent('<div class="collapsible mobile-collapsible">
	<h5 class="block-title heading">Mey B2B</h5>
	<div class="block-content">
        <ul>
            <li><a href="{{store url="faq"}}">FAQ</a></li>
            <li><a href="{{store url="konditionen"}}">Konditionen</a></li>
            <li><a href="{{store url="kontakt"}}">Kontakt</a></li>
            <li><a href="{{store url="lieferbedingungen"}}">Lieferbedingungen</a></li>
        </ul>
	</div>
</div>');
$footerBlock1->save();

$footerBlock3 = Mage::getModel("cms/block")->setStoreId($storeId)->load("block_footer_column3", "identifier");
$footerBlock3->setIsActive(0);
$footerBlock3->save();

$footerBlock4 = Mage::getModel("cms/block")->setStoreId($storeId)->load("block_footer_column4", "identifier");
$footerBlock4->setIsActive(0);
$footerBlock4->save();

$contactsBlockInfo = Mage::getModel("cms/block")->setStoreId($storeId)->load("mey_info_menu", "identifier");
$contactsBlockInfo->setContent('<ul id="info-menu">
    <li class="info-link"><a href="{{store url="kontakt"}}">Kontakt</a></li>
    <li class="info-link"><a href="{{store url="konditionen"}}">Konditionen</a></li>
    <li class="info-link"><a href="{{store url="lieferbedingungen"}}">Lieferbedingungen</a></li>
    <li class="info-link"><a href="{{store url="faq"}}">FAQ</a></li>
</ul>');
$contactsBlockInfo->save();

$kontakt = Mage::getModel("cms/page")->setStoreId($storeId)->load("kontakt", "identifier");
$kontakt->setContent('<div class="box info-image">{{block type="cms/block" block_id="mey_info_pages"}}</div>
<div class="box info-navi">{{block type="cms/block"  block_id="mey_info_menu"}}</div>
<div class="info content">
    <h1>Kundenhotline / Kontakt</h1>
    <p>
        Kundenservice (erreichbar von Montag bis Donnerstag zwischen 08:00 und 18:00 Uhr, freitags zwischen 08:00 und 16:00 Uhr)
    </p>
    <p>
        <table>
        <tbody>
            <tr>
                <td style="width: 30px;">
                    <span class="phone">Tel.:</span>
                </td>
                <td>
                    <span class="phone">07431 / 706-0</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="phone">Fax:</span>
                </td>
                <td>
                    <span class="phone">07431 / 706-100</span>
                </td>
            </tr>
        </tbody>
        </table>
    </p>
    <p>
        <span class="email"><a href="mailto:service@meyb2b.com">service@meyb2b.com</a></span>
    </p>
{{block type="core/template" name="contactForm" template="contacts/form.phtml" form_action="contacts/index/post"}}
</div>');
$kontakt->save();

$contactsBlockInfo = Mage::getModel("cms/block")->setStoreId($storeId)->load("contacts_block_info", "identifier");
$contactsBlockInfo->setContent('<p>
    Der Mey Online-Service ist<br />
    von <strong>Montag bis Donnerstag zwischen 08:00 und 18:00 Uhr</strong><br />
    und <strong>freitags zwischen 08:00 und 16:00 Uhr</strong><br />
    unter der <strong>Service-Nummer 07431 / 706-0</strong> gerne f&uuml;r Sie da.<br />
    Haben Sie Fragen rund um Ihre Bestellung im Mey Online-Shop?<br />
    Unser Service Team freut sich auf Ihre E-Mail an <a href="mailto:service@meyb2b.com">service@meyb2b.com</a>.
</p>');
$contactsBlockInfo->save();

$faq = Mage::getModel("cms/page")->setStoreId($storeId)->load("faq", "identifier");
$faq->setContent('<div class="box info-image">{{block type="cms/block" block_id="mey_info_pages"}}</div>
<div class="box info-navi">{{block type="cms/block" block_id="mey_info_menu"}}</div>
<div class="info content"><a name="faq-top"></a>
<h1>FAQ</h1>
<div class="faq-navigation">
<ul>
<li><a href="#faq-block1">Was passiert, wenn mehrere Auftr&auml;ge an einem Tag im Shop geordert werden?</a></li>
<li><a href="#faq-block2">K&ouml;nnen &Auml;nderungen eines bereits durchgegebenen Auftrags gemacht werden?</a></li>
</ul>
</div>
<div class="faq-block"><a name="faq-block1"></a>
    <h2>Was passiert, wenn mehrere Auftr&auml;ge an einem Tag im Shop geordert werden?</h2>
    <p>Die Bestellungen werden separat bearbeitet und nicht geb&uuml;ndelt. Aus diesem Grund wird jedes Paket einzeln zugestellt. Mit Hilfe des Warenkorbs besteht die Funktion, alle Artikel &uuml;ber den Tag hinweg zu sammeln und vor Ladenschluss die Bestellung durchzugeben. Am Ende des Bestellvorgangs wird die Verf&uuml;gbarkeit der Artikel, die sich &uuml;ber den Tagesverlauf angesammelt haben, gepr&uuml;ft.</p>
</div>
<div class="faq-block"><a name="faq-block2"></a>
    <h2>Wie hoch sind die Kosten f&uuml;r die Lieferung?</h2>
    <p>Im Fall einer Auftragsbest&auml;tigung per E-Mail k&ouml;nnen Mengen&auml;nderungen oder die Aufnahme weiterer Artikel nur telefonisch, per Fax oder per E-Mail bearbeitet werden, sofern die Bestellung noch nicht an unser Logistikzentrum &uuml;bergeben wurde.</p>
</div>
</div>');
$faq->save();

// Koditionen CMS page
$cmsPageData = array(
    'title' => 'Konditionen',
    'root_template' => 'layout_mey_info_page',
    'meta_keywords' => '',
    'meta_description' => '',
    'identifier' => 'konditionen',
    'content_heading' => '',
    'stores' => array($storeId),
    'content' => '<div class="box info-image">{{block type="cms/block" block_id="mey_info_pages"}}</div>
<div class="box info-navi">{{block type="cms/block" block_id="mey_info_menu"}}</div>
<div class="info content">
<h1>Konditionen</h1>
<p>
    <a href="{{media url="wysiwyg/Einheitsbedingungen_deutsch_ab_2015.pdf"}}">Einheitsbedingungen ab 2015 (DE) als PDF</a>
</p>
<p>
In Schriftform geregelte Zusatzvereinbarungen k&ouml;nnen von den Einheitsbedingungen abweichen. Sind diese gegengezeichnet und nicht befristet, finden diese Anwendung.
</p>
</div>'
);
Mage::getModel('cms/page')->setData($cmsPageData)->save();

// Lieferbedingungen CMS page
$cmsPageData = array(
    'title' => 'Lieferbedingungen',
    'root_template' => 'layout_mey_info_page',
    'meta_keywords' => '',
    'meta_description' => '',
    'identifier' => 'lieferbedingungen',
    'content_heading' => '',
    'stores' => array($storeId),
    'content' => '<div class="box info-image">{{block type="cms/block" block_id="mey_info_pages"}}</div>
<div class="box info-navi">{{block type="cms/block" block_id="mey_info_menu"}}</div>
<div class="info content">
<h1>Lieferbedingungen</h1>
    <div>
        <h2>Bestellbare/Lieferbare Artikel</h2>
        <p>Die Nachorder von verf&uuml;gbaren NOS (Basics) Produkten ist m&ouml;glich. Mey liefert, solange der Vorrat reicht und die Bestellung akzeptiert wird. </p>
    </div>
    <div>
        <h2>Im Shop angezeigte Preise</h2>
        <p>Auf der Produkt&uuml;bersicht werden die EK-Einzelpreise netto dargestellt. Auf der Produktseite ist der EK-Einzelpreis netto sowie der VK-Einzelpreis brutto dargestellt.</p>
    </div>
    <div>
        <h2>Mindestbestellwert</h2>
        <p>Der Mindestbestellwert liegt bei 150,00&euro;. Der Vorteil f&uuml;r den Fachh&auml;ndler besteht darin, dass Damen- und Herrenartikel zusammen bestellt werden k&ouml;nnen. Dabei ist egal, wie hoch der jeweilige Anteil an Damen- oder Herrenartikeln ist, solange der Mindestbestellwert erreicht wird. </p>
    </div>
    <div>
        <h2>Lieferung der Bestellung</h2>
        <p>Die Bestellung wird innerhalb von 1-4 Werktagen nach Bestelleingang versendet. Bei R&uuml;ckfragen zum Lieferstatus der Bestellung, gibt der zust&auml;ndige Sachbearbeiter gerne Auskunft dar&uuml;ber, wo sich das Paket befindet.</p>
    </div>
</div>'
);
Mage::getModel('cms/page')->setData($cmsPageData)->save();

// remove B2C CMS pages from B2B store
$cmsPagesToRemove = array(
    "lieferung",
    "retoure",
    "zahlung",
);
foreach ($cmsPagesToRemove as $cmsPageId) {
    $cmsPage = Mage::getModel("cms/page")->setStoreId($storeId)->load($cmsPageId, "identifier");
    $cmsPage->delete();
}

$installer->endSetup();