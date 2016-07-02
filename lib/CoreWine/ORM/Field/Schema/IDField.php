<?php

namespace CoreWine\ORM\Field\Schema;

class IDField extends Field{
	
	/**
	 * Unique
	 */
	public $unique = true;


	public $primary = true;
	
	public $auto_increment = true;

	public $persist = false;

	public $add = false;
	
	public $edit = false;
	
	public $copy = false;


	/**
	 * Alter
	 */
	public function alter($table){
		$table -> id($this -> name);
	}

}

?>