<?php

class Mey_UrlRedirects_Model_Url_Url extends Mage_Core_Model_Url {
    public function getUrl($routePath = null, $routeParams = null) {
        $url = parent::getUrl($routePath, $routeParams);

        if(substr($url, -1) != "/") {
            if(strpos($url, "?") !== false) {
                if(substr($url, strpos($url, "?") - 1, 1) != "/") {
                   $url = substr_replace($url, "/?", strpos($url, "?"), 1);
                }
            } else {
                $url .= "/";
            }
        }

        return $url;
    }
}
