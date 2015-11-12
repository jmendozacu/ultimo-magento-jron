<?php
	/**
	 * SocialSharePrivacy Default Helper
	 *
	 * @category   Intersales
	 * @package    Intersales_SocialSharePrivacy
	 * @author     Daniel Rose <dr@intersales.de>
	 */
	class Intersales_SocialSharePrivacy_Helper_Data extends Mage_Core_Helper_Abstract {
		const XML_PATH_GENERAL_ENABLED = 'socialshareprivacy/general_settings/enabled';
		const XML_PATH_GENERAL_INFO = 'socialshareprivacy/general_settings/info';
		const XML_PATH_GENERAL_PRIVACY_PAGE = 'socialshareprivacy/general_settings/privacy_page';

		const XML_PATH_FACEBOOK_ENABLED = 'socialshareprivacy/facebook/enabled';
		const XML_PATH_FACEBOOK_PRIVACY_INFO = 'socialshareprivacy/facebook/privacy_info';
		const XML_PATH_FACEBOOK_DUMMY_IMAGE = 'socialshareprivacy/facebook/dummy_image';
		
		const XML_PATH_TWITTER_ENABLED = 'socialshareprivacy/twitter/enabled';
		const XML_PATH_TWITTER_PRIVACY_INFO = 'socialshareprivacy/twitter/privacy_info';
		const XML_PATH_TWITTER_DUMMY_IMAGE = 'socialshareprivacy/twitter/dummy_image';

		const XML_PATH_GOOGLE_PLUS_ENABLED = 'socialshareprivacy/google_plus/enabled';
		const XML_PATH_GOOGLE_PLUS_PRIVACY_INFO = 'socialshareprivacy/google_plus/privacy_info';
		const XML_PATH_GOOGLE_PLUS_DUMMY_IMAGE = 'socialshareprivacy/google_plus/dummy_image';

		/**
		 * Is module enabled
		 *
		 * @param null $storeId
		 * @return bool
		 */
		public function isEnabled($storeId = null) {
			return Mage::getStoreConfig(self::XML_PATH_GENERAL_ENABLED, $storeId) == 1;
		}

		/**
		 * Retrieve info text
		 *
		 * @param null $storeId
		 * @return mixed
		 */
		public function getInfoText($storeId = null) {
			return Mage::getStoreConfig(self::XML_PATH_GENERAL_INFO, $storeId);
		}

		/**
		 * Retrieve privacy page code
		 *
		 * @param null $storeId
		 * @return mixed
		 */
		public function getPrivacyPageCode($storeId = null) {
			return Mage::getStoreConfig(self::XML_PATH_GENERAL_PRIVACY_PAGE, $storeId);
		}

		/**
		 * Is Facebook enabled
		 *
		 * @param null $storeId
		 * @return bool
		 */
		public function isFacebookEnabled($storeId = null) {
			return Mage::getStoreConfig(self::XML_PATH_FACEBOOK_ENABLED, $storeId) == 1;
		}

		/**
		 * Retrieve privacy info of Facebook
		 *
		 * @param null $storeId
		 * @return mixed
		 */
		public function getFacebookPrivacyInfo($storeId = null) {
			return Mage::getStoreConfig(self::XML_PATH_FACEBOOK_PRIVACY_INFO, $storeId);
		}

		/**
		 * Retrieve dummy image of Facebook
		 *
		 * @param null $storeId
		 * @return mixed
		 */
		public function getFacebookDummyImage($storeId = null) {
			return Mage::getStoreConfig(self::XML_PATH_FACEBOOK_DUMMY_IMAGE, $storeId);
		}

		/**
		 * Is Twitter enabled
		 *
		 * @param null $storeId
		 * @return bool
		 */
		public function isTwitterEnabled($storeId = null) {
			return Mage::getStoreConfig(self::XML_PATH_TWITTER_ENABLED, $storeId) == 1;
		}

		/**
		 * Retrieve privacy info of Twitter
		 *
		 * @param null $storeId
		 * @return mixed
		 */
		public function getTwitterPrivacyInfo($storeId = null) {
			return Mage::getStoreConfig(self::XML_PATH_TWITTER_PRIVACY_INFO, $storeId);
		}

		/**
		 * Retrieve dummy image of Twitter
		 *
		 * @param null $storeId
		 * @return mixed
		 */
		public function getTwitterDummyImage($storeId = null) {
			return Mage::getStoreConfig(self::XML_PATH_TWITTER_DUMMY_IMAGE, $storeId);
		}

		/**
		 * Is Google+ enabled
		 *
		 * @param null $storeId
		 * @return bool
		 */
		public function isGooglePlusEnabled($storeId = null) {
			return Mage::getStoreConfig(self::XML_PATH_GOOGLE_PLUS_ENABLED, $storeId) == 1;
		}

		/**
		 * Retrieve privacy info of Google+
		 *
		 * @param null $storeId
		 * @return mixed
		 */
		public function getGooglePlusPrivacyInfo($storeId = null) {
			return Mage::getStoreConfig(self::XML_PATH_GOOGLE_PLUS_PRIVACY_INFO, $storeId);
		}

		/**
		 * Retrieve dummy image of Google+
		 *
		 * @param null $storeId
		 * @return mixed
		 */
		public function getGooglePlusDummyImage($storeId = null) {
			return Mage::getStoreConfig(self::XML_PATH_GOOGLE_PLUS_DUMMY_IMAGE, $storeId);
		}
	}
?>