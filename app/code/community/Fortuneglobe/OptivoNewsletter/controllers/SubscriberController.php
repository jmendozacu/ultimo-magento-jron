<?php
require_once 'Mage/Newsletter/controllers/SubscriberController.php';
class Fortuneglobe_OptivoNewsletter_SubscriberController extends Mage_Newsletter_SubscriberController
{
    /**
     * Overload confirm action
     */
    public function confirmAction()
    {
        $id    = (int) $this->getRequest()->getParam('id');
        $code  = (string) $this->getRequest()->getParam('code');

        if ($id && $code) {
            $subscriber = Mage::getModel('newsletter/subscriber')->load($id);
            $session = Mage::getSingleton('core/session');

            if($subscriber->getId() && $subscriber->getCode()) {
                if($subscriber->confirm($code)) {
                    $session->addSuccess($this->__('Your subscription has been confirmed.'));
                    $subscriber->setSubscriberActivateip($_SERVER['REMOTE_ADDR']);
                    $date = new DateTime();
                    $subscriber->setSubscriberActivatetimestamp($date->format('Y-m-d H:i:s'));
                    $subscriber->save();
                } else {
                    $session->addError($this->__('Invalid subscription confirmation code.'));
                }
            } else {
                $session->addError($this->__('Invalid subscription ID.'));
            }
        }

        $this->_redirectUrl(Mage::getBaseUrl());
    }
}
