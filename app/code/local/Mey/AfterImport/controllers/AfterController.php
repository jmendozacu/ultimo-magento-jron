<?php

class Mey_AfterImport_AfterController extends Mage_Core_Controller_Front_Action {
    public function beginAction() {
        $key = $this->getRequest()->getParam('key');
        if($key != Mage::getStoreConfig('fg_options/messages/after_import_key')) {
            self::norouteAction();
            return;
        }

        /* @var Aoe_Scheduler_Model_Schedule $schedule */
        $schedule = Mage::getModel('cron/schedule');
        $schedule->setJobCode('mey_afterimport');
        $schedule->schedule();
        $schedule->save();

        $response = $this->getResponse();
        $response->setHttpResponseCode(201);
        $response->sendResponse();
        return;
    }
}
