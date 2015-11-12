<?php

final class LanguageMapper
{

	private $languageMap = [
			"de" => [
				"storeId"        => 1,
				"label"          => "DE_de",
				"deliveryText"   => "Lieferbar innerhalb von 1-3 Werktagen"
			],
			"en" => [
				"storeId"        => 2,
				"label"          => "EN_en",
				"deliveryText"   => "Shipping within 1-3 business days"
			],
			"nl" => [
				"storeId"        => 3,
				"label"          => "NL_nl",
				"deliveryText"   => "Levertijd: 1-3 werkdagen"
			]
		];

	private $lang;

	public function __construct( $lang="de" )
	{
		if(!array_key_exists($lang, $this->languageMap))
		{
			throw new Exception("unknown language $lang given...");
		}
		$this->lang = $lang;
	}


	public function getStoreId()
	{
		return $this->languageMap[$this->lang]["storeId"];
	}

	public function getDeliveryText()
	{
		return $this->languageMap[$this->lang]["deliveryText"];
	}

	public function getLabel()
	{
		return $this->languageMap[$this->lang]["label"];
	}

}