<?php

return [
	"images_to_create" => [ // Bilder die erstellt werden sollen
		[ "type" => "small_image",  "height" => 135,    "width" => 115,     "name" => "size0" ],
		[ "type" => "small_image",  "height" => 295,    "width" => 295,     "name" => "size1" ],
		[ "type" => "thumbnail",    "height" => 350,    "width" => 350,     "name" => "size2" ],
		[ "type" => "image",        "height" => 600,    "width" => 415,     "name" => "size3" ],
		[ "type" => "image",        "height" => 2000,   "width" => 1400,    "name" => "size4" ]
	],

    "images_dir"  => "feed_images",
    "images_path" => "/wamp/www/ultimo/media/prodimages/",
    "feed_dir" => "/wamp/www/ultimo/media/productexport", // hier wird der Feed gespeichert
    "default_language" => "de" // wird verwendet wenn beim Aufruf keine Sprache übergeben wird
];