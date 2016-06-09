<?php

namespace Item\Response;

use CoreWine\Request;

class ApiFieldsInvalid extends Error{

	/** 
	 * Code
	 */
	const CODE = 'fields_invalid';

	/**
	 * Message
	 */
	const MESSAGE = "The values sent aren't valid";

	/**
	 * Construct
	 *
	 * @param array $details
	 */
	public function __construct($details){

		parent::__construct(self::CODE,self::MESSAGE);
		$this -> setDetails($details);
		$this -> setRequest(Request::getCall());
	}
}

?>