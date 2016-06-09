<?php

namespace Item\Response;

use CoreWine\Request;

class ApiErrorAllShow extends Error{

	/** 
	 * Code
	 */
	const CODE = 'sort_field_not_exists';

	/**
	 * Message
	 */
	const MESSAGE = "The field sent as sort field doesn't exists";

	/**
	 * Construct
	 */
	public function __construct(){

		parent::__construct(self::CODE,self::MESSAGE);
		$this -> setRequest(Request::getCall());
	}
}

?>