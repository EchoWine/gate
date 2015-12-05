<?php

class CredentialView extends View{
	
	public $model;
	public $controller;
	
	public function __construct($model,$controller){
		$this -> model = $model;
		$this -> controller = $controller;
	}

	public function printFormLogin(){
		$controller -> cfg['user'];
	}

	
	public function template($path){

		$path = $path."/templates";
		TemplateEngine::overwrite('content','Credential.page','$pageCredential');

		TemplateEngine::aggregate('nav',$path,'Credential.nav',30);

		// TemplateEngine::aggregate('style',$path,'auth.style');
		
	}
}

?>