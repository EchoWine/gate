<?php

namespace Serie\Model;

use CoreWine\DataBase\ORM\Model;
use CoreWine\DataBase\ORM\Field\Schema as Field;

class Serie extends Resource{

	/**
	 * Table name
	 *
	 * @var
	 */
	public static $table = 'series';

	/**
	 * Set schema fields
	 *
	 * @param Schema $schema
	 */
	public static function setSchemaFields($schema){

		parent::setSchemaFields($schema);

		$schema -> toMany(Season::class,'seasons','serie_id');

	}
}

?>