<?php
 
class Mey_Turpentine_Model_Turpentine_Dummy_Request extends Nexcessnet_Turpentine_Model_Dummy_Request {
    /**
     * Check this request against the cms, standard, and default routers to fill
     * the module/controller/action/route fields.
     *
     * TODO: This whole thing is a gigantic hack. Would be nice to have a
     * better solution.
     *
     * @return null
     */
    public function fakeRouterDispatch() {

        $className = (string)Mage::getConfig()->getNode('global/request_rewrite/model');
        /** @var Mey_Turpentine_Model_Core_Url_Rewrite_Request $rewriteModel */
        $rewriteModel = Mage::getSingleton('core/factory')->getModel($className, array(
            'routers' => Mage::app()->getFrontController()->getRouters(),
        ));
        $rewriteModel->setRequest($this);
        $rewriteModel->rewrite();

        if( $this->_cmsRouterMatch() ) {
            Mage::helper( 'turpentine/debug' )->logDebug( 'Matched router: cms' );
        } elseif( $this->_standardRouterMatch() ) {
            Mage::helper( 'turpentine/debug' )->logDebug( 'Matched router: standard' );
        } elseif( $this->_defaultRouterMatch() ) {
            Mage::helper( 'turpentine/debug' )->logDebug( 'Matched router: default' );
        } else {
            Mage::helper( 'turpentine/debug' )->logDebug( 'No router match' );
        }
    }
}
