<?php

namespace Item;

use CoreWine\DataBase\DB;
use CoreWine\Router;
use CoreWine\Request as Request;

use CoreWine\SourceManager\Controller as SourceController;

use Item\Repository;
use Item\Response as Response;

abstract class Controller extends SourceController{

	/**
	 * Retrieve result as array
	 */
	const RESULT_ARRAY = 0;

	/**
	 * Retrieve results as object
	 */
	const RESULT_OBJECT = 1;

	/**
	 * Name of obj in url
	 */
	public $url;

	/**
	 * Item\Schema
	 */
	public $__schema = 'Item\Schema';

	/**
	 * Item\Repository
	 */
	public $__repository = 'Item\Repository';

	/**
	 * Item\Schema
	 */
	public $schema;

	/**
	 * Item\Repository
	 */
	public $repository;

	/**
	 * Routers
	 */
	public function __routes(){

		$url = $this -> url;

		$this -> route('all') -> url("/api/{$url}") -> get();
		$this -> route('add') -> url("/api/{$url}") -> post();
		$this -> route('copy') -> url("/api/{$url}/{id}") -> post();
		$this -> route('get') -> url("/api/{$url}/{id}") -> get();
		$this -> route('edit') -> url("/api/{$url}/{id}") -> put();
		$this -> route('delete') -> url("/api/{$url}/{id}") -> delete();
	}

	/**
	 * Get api url
	 */
	public function getFullApiURL(){

		return Request::getDirUrl()."api/{$this -> url}";
	}

	/**
	 * Check
	 */
	public function __check(){
		$this -> schema = new $this -> __schema();
		$this -> repository = new $this -> __repository($this -> schema);
		$this -> __alterSchema();
	}

	/**
	 * Alter schema
	 */
	public function __alterSchema(){
		$this -> getRepository() -> __alterSchema();
	}

	/**
	 * Get schema
	 *
	 * @return Schema
	 */
	public function getSchema(){
		return $this -> schema;
	}

	/**
	 * Get repository
	 *
	 * @return Repository
	 */
	public function getRepository(){
		return $this -> repository;
	}

	/**
	 * Get all the result
	 */
	public function all(){

		return $this -> json($this -> __all(Controller::RESULT_ARRAY));
	}


	/**
	 * Retrieve a record
	 */
	public function get($id){

		$first = $this -> __first($id,Controller::RESULT_ARRAY);

		switch(Request::get('filter')){
			case 'edit':

			break;
			default:

			break;
		}


		$response = new Response\ApiGetSuccess($id,$first);

		return $this -> json($response);
	}

	/**
	 * Add new record
	 */
	public function add(){
		return $this -> json($this -> __add());
	}

	/**
	 * Edit a record
	 */
	public function edit($id){
		return $this -> json($this -> __edit($id,Controller::RESULT_ARRAY));
	}

	/**
	 * Delete a record
	 */
	public function delete($id){
		return $this -> json($this -> __delete($id));
	}


	/**
	 * Copy a record
	 */
	public function copy($id){
		return $this -> json($this -> __copy($id));
	}

	/**
	 * Get all records
	 *
	 * @param int $type type of result (Array|Object)
	 * @return results
	 */
	public function __all($type){

		try{
			$repository = $this -> getRepository() -> table($type);

			$sort = Request::get('desc',null);
			$sort = Request::get('asc',$sort);
			$direction = $sort == Request::get('desc') ? 'desc' : 'asc';

			# SORTING
			if($sort){

				# If the not exists the field
				if(!$this -> schema -> hasField($sort))
					return Response\ApiAllErrorParamSortNotExists();
				

				$field = $this -> schema -> getField($sort);

				# If the field isn't enabled to sorting
				if(!$field -> isSort())
					return Response\ApiAllErrorParamSortNotValid();
				

				$repository = $repository -> orderBy($field -> getColumn(),$direction);
			}else{
				$repository = $repository -> orderBy($this -> schema -> getSortDefaultField() -> getColumn(),$this -> schema -> getSortDefaultDirection());
			}

			# COUNT ALL THE RESULTS
			$count = $repository -> count();


			# SHOWING
			$show = Request::get('show',null);
			if($show){

				if($show <= 0){
					
					return new Response\ApiAllErrorParamShow();
				}

				$repository = $repository -> take($show);

			}else{
				$show = 100;
			}

			# GET PAGES
			$pages = ceil($count / $show);


			# PAGINATION
			$page = Request::get('page',1);
			if($page !== 1){

				if($page > $pages)
					$page = $pages;

				if($page <= 0){
					
					return Response\ApiAllErrorParamPage();
				}

				$skip = ($page - 1) * $show;

				$repository = $repository -> skip($skip);
			}else{
				$skip = 0;
			}


				$results = $repository -> get();


			return new Response\ApiAllSuccess([
				'results' => $results,
				'count' => $count,
				'page' => $page,
				'pages' => $pages,
				'from' => $skip + 1,
				'to' => $skip + count($results)
			]);
		}catch(\Exception $e){

			return new Response\ApiException($e);
		}

	}
	/**
	 * Get a records
	 *
	 * @param int $id
	 * @param int $type type of result (Array|Object)
	 * @return results
	 */
	public function __first($id,$type){
		return $this -> getRepository() -> firstById($id,$type);
	}

	/**
	 * Add a new record
	 *
	 * @return \Item\Response\Response
	 */
	public function __add(){

		try{

			list($row,$errors) = $this -> __addFields();

			# Response status error if validation is failed
			if(!empty($errors))
				return new Response\ApiFieldsInvalid($errors);
			
				$id = $this -> getRepository() -> insert($row);
				$result = $this -> __first($id[0],Controller::RESULT_ARRAY);
			return new Response\ApiAddSuccess($id[0],$result);


		}catch(\Exception $e){

			return new Response\ApiException($e);
		}

	}	
	
	/**
	 * Edit record
	 *
	 * @param int $id
	 * @return \Item\Response\Response
	 */
	public function __edit($id){

		try{

			if(!$result = $this -> __first($id,Controller::RESULT_ARRAY))
				return new Response\ApiNotFound();
			
			list($row,$errors) = $this -> __editFields($id,$result);

			if(!empty($errors))
				return new Response\ApiFieldsInvalid($errors);


			$this -> getRepository() -> update($id,$row);
			

			return new Response\ApiEditSuccess($id,$result,$this -> __first($id,Controller::RESULT_ARRAY));

		}catch(\Exception $e){

			return new Response\ApiException($e);
		}

	}

	/**
	 * Remove a new record
	 */
	public function __delete($id){

		$result = $this -> __first($id,Controller::RESULT_ARRAY);

		if(!$result)
			return new Response\ApiNotFound();
		
		$id = $this -> getRepository() -> deleteById($id);
	
		return new Response\ApiDeleteSuccess($id,$result);

	}

	/**
	 * Copy a new record
	 */
	public function __copy($id){

		$result = $this -> getRepository() -> firstById($id);

		if(!$result)
			return new Response\ApiNotFound();
		

		list($row) = $this -> __copyFields($result);

		$id = $this -> getRepository() -> insert($row);

		$resource = $this -> __first($id[0],Controller::RESULT_ARRAY);

		return new Response\ApiCopySuccess($id,$result,$resource);

	}

	/**
	 * Retrieve value of fields to add and relative errors
	 *
	 * @return array
	 */
	public function __addFields(){

		$row = [];
		$errors = []; 


		$fields = $this -> getSchema() -> getFields();

		foreach($fields as $name => $field){

			if($field -> isAdd()){

				$name = $field -> getName();
				$col = $field -> getColumn();
				$value = Request::post($name);

				if($field -> isAddNeeded($value)){

					$row[$name] = $field -> parseValueAdd($value);

					// Validate field
					$response = $field -> isValid($value);

					if(!$this -> isResponseSuccess($response)){
						$errors[$name] = $response;
					}


					if($this -> isResponseSuccess($response)){
						if($field -> isUnique()){
							if($this -> getRepository() -> exists([$col => $value])){
								$errors[$name] = new Response\ApiFieldErrorNotUnique();
							}
						}
					}
				}
			}
		}

		return [$row,$errors];
	}

	/**
	 * Retrieve value of fields to update and relative errors
	 *
	 * @param int $id
	 * @param array $result
	 * @return array
	 */
	public function __editFields($id,$result){

		$row = [];
		$errors = [];
		$fields = $this -> getSchema() -> getFields();

		foreach($fields as $name => $field){

			if($field -> isEdit()){

				$name = $field -> getName();
				$col = $field -> getColumn();
				$value = Request::put($name);

				if($field -> isEditNeeded($value)){
				
					$row[$name] = $field -> parseValueEdit($value);

					$response = $field -> isValid($value);

					if(!$this -> isResponseSuccess($response)){
						$errors[$name] = $response;
					}

					if($this -> isResponseSuccess($response)){
						if($field -> isUnique()){
							if($this -> getRepository() -> existsExceptId($id,[$col => $value])){
								$errors[$name] = new Response\ApiFieldErrorNotUnique($field -> getLabel(),$value);
							
							}
						}
					}
				}
			}
		}

		return [$row,$errors];
	}

	/**
	 * Retrieve value of fields to copy and relative errors
	 *
	 * @return array
	 */
	public function __copyFields($result){

		$row = [];
		$fields = $this -> getSchema() -> getFields();

		foreach($fields as $name => $field){

			if($field -> isCopy()){

				$col = $field -> getColumn();
				$value = $result[$col];

				if($field -> isUnique()){
					$n = 0;
					do{
						$value_copied = $field -> parseValueCopy($value,$n++);
						$exists = $this -> getRepository() -> exists([$col => $value_copied]);
					}while($exists);
					$value = $value_copied;
				}

				$row[$field -> getName()] = $value;

			}
		}

		return [$row];

	}

	/**
	 * Return if a response is success or not
	 *
	 * @param \Item\Response\Response $response
	 *
	 * @return bool
	 */
	public function isResponseSuccess(\Item\Response\Response $response){
		return ($response instanceof \Item\Response\Success);
	}

	/**
	 * Return if a response is error or not
	 *
	 * @param \Item\Response\Response $response
	 *
	 * @return bool
	 */
	public function isResponseError(\Item\Response\Response $response){
		return ($response instanceof \Item\Response\Error);
	}

}


?>