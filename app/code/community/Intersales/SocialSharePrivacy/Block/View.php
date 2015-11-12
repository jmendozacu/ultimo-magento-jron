<?php
	/**
	 * SocialSharePrivacy View Block
	 *
	 * @category   Intersales
	 * @package    Intersales_SocialSharePrivacy
	 * @author     Daniel Rose <dr@intersales.de>
	 */
	class Intersales_SocialSharePrivacy_Block_View extends Mage_Core_Block_Template {
		/**
		 * Can show
		 *
		 * @return bool
		 */
		public function canShow() {
			$isEnabled = $this->isFacebookEnabled() || $this->isTwitterEnabled() || $this->isGooglePlusEnabled();

			return $isEnabled && $this->_getHelper()->isEnabled();
		}

		/**
		 * Retrieve info text
		 *
		 * @return mixed
		 */
		public function getInfoText() {
			return $this->_getHelper()->getInfoText();
		}

		/**
		 * Retrieve url of privacy page
		 *
		 * @return string
		 */
		public function getUrlOfPrivacyPage() {
			if($this->_getHelper()->getPrivacyPageCode() != '') {
				return Mage::getUrl($this->_getHelper()->getPrivacyPageCode());
			}
			return '';
		}

		/**
		 * Retrieve current url
		 *
		 * @return mixed
		 */
		public function getCurrentUrl() {
			return Mage::helper('core/url')->getCurrentUrl();
		}

		/**
		 * Is Facebook enabled
		 *
		 * @return bool
		 */
		public function isFacebookEnabled() {
			return $this->_getHelper()->isFacebookEnabled();
		}

		/**
		 * Retrieve privacy info of Facebook
		 *
		 * @return mixed
		 */
		public function getFacebookPrivacyInfo() {
			return $this->_getHelper()->getFacebookPrivacyInfo();
		}

		/**
		 * Retrive url of Facebook dummy image
		 *
		 * @return string
		 */
		public function getUrlOfFacebookDummyImage() {
			if($this->_getHelper()->getFacebookDummyImage() != '') {
				return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'socialshareprivacy/' . $this->_getHelper()->getFacebookDummyImage();
			}

			return '';
		}

		/**
		 * Is Twitter enabled
		 *
		 * @return bool
		 */
		public function isTwitterEnabled() {
			return $this->_getHelper()->isTwitterEnabled();
		}

		/**
		 * Retrieve privacy info of Twitter
		 *
		 * @return mixed
		 */
		public function getTwitterPrivacyInfo() {
			return $this->_getHelper()->getTwitterPrivacyInfo();
		}

		/**
		 * Retrive url of Twitter dummy image
		 *
		 * @return string
		 */
		public function getUrlOfTwitterDummyImage() {
			if($this->_getHelper()->getTwitterDummyImage() != '') {
				return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'socialshareprivacy/' . $this->_getHelper()->getTwitterDummyImage();
			}

			return '';
		}

		/**
		 * Is Google+ enabled
		 *
		 * @return bool
		 */
		public function isGooglePlusEnabled() {
			return $this->_getHelper()->isGooglePlusEnabled();
		}

		/**
		 * Retrieve privacy info of Google+
		 *
		 * @return mixed
		 */
		public function getGooglePlusPrivacyInfo() {
			return $this->_getHelper()->getGooglePlusPrivacyInfo();
		}

		/**
		 * Retrive url of Google+ dummy image
		 *
		 * @return string
		 */
		public function getUrlOfGooglePlusDummyImage() {
			if($this->_getHelper()->getGooglePlusDummyImage() != '') {
				return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'socialshareprivacy/' . $this->_getHelper()->getGooglePlusDummyImage();
			}

			return '';
		}

		/**
	     * Retrieve default helper of SocialSharePrivacy
	     *
	     * @return Intersales_SocialSharePrivacy_Helper_Data
	     */
	    protected function _getHelper() {
	        return Mage::helper('socialshareprivacy');
	    }
	}
?>