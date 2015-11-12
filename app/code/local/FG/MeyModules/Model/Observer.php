<?php
class FG_MeyModules_Model_Observer
{

    public function addToTopmenu()
    {
        /*  FUNKTIONEN SCHREIBEN, welches Kategorie Sonstiges in Damen und Herren als Link hinzufügt ohne nächste Ebene */
        /* FUNKTION SCHREIBEN, wo nach einem Import oder anlegen einer neuen Kategorie diese Methode wieder aufgerufen wird. */

        $cache = Mage::app()->getCache();
        $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("TopMenu_Create"));
        $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("Navigation_Tree"));

        // Erstelle Nested Array von Kategorie
        $navids = array(2);
        $navigationtrees = array();
        $keys = array();

        for($i = 0;$i < count($navids);$i++) {
            $category_ids = $navids[$i];
            $category = Mage::getModel('catalog/category')->load($category_ids);
            $uniqekey = $category->getId().$category->getName();
            if( ! $data = $cache->load($uniqekey)){
                array_push($navigationtrees, $this->createnavigateTreebyCategory($category_ids));
                $data = serialize($navigationtrees[$i]);
                $cache->save($data, $uniqekey, array("TopMenu_Create"));
            }else{
                $navigationitems = unserialize($data);
                array_push($navigationtrees, $navigationitems);
            }
        }
        //print_r($navigationtrees);

        // Create an Array with all active Categories
        $uniqekey = "NavigationArray";
        if( ! $data = $cache->load($uniqekey)){
            foreach ($navigationtrees as $navigationtree) {
                $keys = $this->createArrayWithActiveCategories($navigationtree,$keys);
            }
            array_push($keys,2);
            $data = serialize($keys);
            $cache->save($data, $uniqekey, array("TopMenu_Create"));
        }else{
            $keys = unserialize($data);
        }
        //Deactivate all Categories, which are not in the Array
        //$this->createActiveNavigation($keys);
        //print_r("Fertig");
    }

    //Function to Create a Navigation-Tree
    public function createnavigateTreebyCategory($category_id){

        $category = Mage::getModel('catalog/category')->load($category_id);
        $subCat = explode(',',$category->getChildren());
        $navigationitems = array();
        if($subCat != ''){
            $navigationitems[$category_id] = $this->getChildrenIds($category_id);
        }else{
            array_push($navigationitems,$category_id);
            array_push($navigationitems,$category->getName());
        }
        //print_r("Ausgabe Navigation Nr: ".$category_id."<br>");
        //print_r($navigationitems);
        //print_r("<br>");
        return $navigationitems;
    }

    //Recursive Function for creation of nested Arrays
    public function getChildrenIds ($productid){
            $category = Mage::getModel('catalog/category')->load($productid);
            $subCats = explode(',',$category->getChildren());
            //print_r($category->getName()." Kategorie mit ID: ".$category->getId()." hat # Kinder ".count($subCats).". <br>");
            if($subCats[0] != ""){
                $array = array();
                foreach ($subCats as $subCat) {
                    $categoryy = Mage::getModel('catalog/category')->load($subCat);
                    $products = Mage::getResourceModel('catalog/product_collection'); 
                    $products ->addCategoryFilter($categoryy) //category filter 
                                ->addAttributeToFilter('status',1) //only enabled product
                                ->addAttributeToFilter('visibility', 4)   
                                ->addStoreFilter();
                    if($products->getSize() > 0){
                        $array[$subCat." = ".$categoryy->getName()] = $this->getChildrenIds($subCat);
                    }
                }
            }else{
                $array = "";
            }
        return $array;
    }

    // Recursive Function for creation of an Array of active Categories
    public function createArrayWithActiveCategories(array $categories,array $ids){
        foreach($categories as $key => $value)
        {
            array_push($ids, $key);
            if( is_array($value) ) { 
                $ids = $this->createArrayWithActiveCategories($value, $ids);
            }
        }
        return $ids;
    }

    public function createActiveNavigation(array $keys){
        $category = Mage::getModel('catalog/category');
        $tree = $category->getTreeModel();
        $tree->load();
        $ids = $tree->getCollection()->getAllIds();
        //Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
        if ($ids){
            //print_r($keys);
            foreach ($ids as $id){
                $cat = Mage::getModel('catalog/category')->load($id);
                if(in_array($id,$keys)){
                    //print_r("Kat gefunden<br>");
                    //print_r($id);
                    //print_r("<br>");
                    $cat->setIncludeInMenu(1);
                    
                }else{
                    $cat->setIncludeInMenu(1);
                }
                $cat->setIsActive(1);
                $cat->save();
            }
        }
        //Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(1));
    }

    // Observer für zuweisung der Produkte in die Speziellen Kategorien
    public function checkforSpecialTreatment(Varien_Event_Observer $observer)
    {
        // Funktion schreiben, für Import-Abfragenden Observer
        // Funktion schreiben, was jeden Tag prüft, ob welche rausfallen

        /*
        $productid = $observer->getProduct();
        $product = Mage::getModel('catalog/product')->load($productid);
        $relatedcategoriestoproduct = $product->getCategoryIds();

        $parentids = array(3,4);
        $specialid = 186;
        $newid = 216;
        $rootid = 2;

        $todayDate = date('m/d/y');
        $tomorrow = mktime(0, 0, 0, date('m'), date('d'), date('y'));
        $tomorrowDate = date('m/d/y', $tomorrow);
        //$teststring = "";
        $is_special = false;
        $is_new = false;

        //Check for Special Price

        if(($product->getSpecialPrice() != '') && (Mage::getModel('core/date')->date('m/d/y', strtotime($product->getSpecialFromDate())) <= $todayDate) && (Mage::getModel('core/date')->date('m/d/y', strtotime($product->getSpecialToDate())) >= $tomorrowDate) ){
            //$teststring .= " Es wäre ein Special";
            $is_special = true;
        }

        // Check for New Product
        if((Mage::getModel('core/date')->date('m/d/y', strtotime($product->getNewsFromDate())) <= $todayDate) && (Mage::getModel('core/date')->date('m/d/y', strtotime($product->getNewsToDate())) >= $tomorrowDate) ){
            //$teststring .= " Es wäre ein New";
            $is_new = true;
        }

        // If check = true then sort in categories
        if($is_new || $is_special){
            foreach ($relatedcategoriestoproduct as $category_id) {
                $cat = Mage::getModel('catalog/category')->load($category_id);
                $cat_tail = array();
                $check = true;
                // Hole den Tail der Kategorie in Herren Damen
                do {
                    array_push($cat_tail, $cat->getName());
                    if(!in_array($catparent = $cat->getParentId(), $parentids)){
                        $cat = Mage::getModel('catalog/category')->load($catparent);
                        if($catparent == $rootid){
                            $check = false;
                        }
                    }else{
                        $check = false;
                        $cat = Mage::getModel('catalog/category')->load($catparent);
                        array_push($cat_tail, $cat->getName());
                    }
                } while ($check);
                // TO-DO Sort in Categories
                if($is_special){
                    $cat = Mage::getModel('catalog/category')->load($specialid);
                    for ($i=count($cat_tail)-1; $i >= 0 ; $i--) { 
                        foreach (explode(',',$cat->getChildren()) as $key => $childcat) {
                            $cat = Mage::getModel('catalog/category')->load($childcat);
                            if($cat->getName() == $cat_tail[$i]){
                                if($i == 0){
                                    //$teststring .= " Ich würde es darein kopieren: ".$cat->getName()." mit der Id: ".$cat->getId()." ";
                                    if(!in_array($cat->getId(), $relatedcategoriestoproduct)){
                                        array_push($relatedcategoriestoproduct, $cat->getId());
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
                if($is_new){
                    $cat = Mage::getModel('catalog/category')->load($newid);
                    for ($i=count($cat_tail)-1; $i >= 0 ; $i--) { 
                        foreach (explode(',',$cat->getChildren()) as $key => $childcat) {
                            $cat = Mage::getModel('catalog/category')->load($childcat);
                            if($cat->getName() == $cat_tail[$i]){
                                if($i == 0){
                                    //$teststring .= " Ich würde es darein kopieren: ".$cat->getName()." mit der Id: ".$cat->getId()." ";
                                    if(!in_array($cat->getId(), $relatedcategoriestoproduct)){
                                        array_push($relatedcategoriestoproduct, $cat->getId());
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }

        $product->setCategoryIds($relatedcategoriestoproduct);
        //$product->setShortDescription($teststring);
        $product->save();
        $cache = Mage::app()->getCache();
        $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("TopMenu_Create"));
        $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("Navigation_Tree"));

        */
    }



    // Observer für erneute Überprüfung der Kategorien
    public function checkCatgoriesAfterAlter(Varien_Event_Observer $observer)
    {
        
        //$cache = Mage::app()->getCache();
        //$cat = $observer->getCategory();
        //$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("TopMenu_Create"));
        //$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("Navigation_Tree"));
        //$this->addToTopmenu();
        

    }

    // Observer für das Bearbeiten von eben erfolgten Bestellungen
    public function addGender(Varien_Event_Observer $observer)
    {

    }

    //Observer to Delete Caching for altered Products
    public function deletecachedproduct(Varien_Event_Observer $observer)
    {
        $cache = Mage::app()->getCache();
        $productid = $observer->getProduct()->getId();
        $productnumber = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('number')
            ->addAttributeToFilter('entity_id', array('eq' => $productid))
            ->getFirstItem()
            ->getNumber();
        $storeids = array_keys(Mage::app()->getStores());
        $count = count($storeids);
        for ($i=0; $i < $count; $i++) { 
            $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($productnumber.'-'.$storeids[$i]));
        }
    }
}
?>