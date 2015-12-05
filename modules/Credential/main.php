<?php

include dirname(__FILE__)."/Credential.class.php";

include dirname(__FILE__)."/CredentialController.class.php";
include dirname(__FILE__)."/CredentialModel.class.php";
include dirname(__FILE__)."/CredentialView.class.php";

$CredentialModel = new CredentialModel();
$CredentialController = new CredentialController($CredentialModel);

$CredentialView = new CredentialView($CredentialModel,$CredentialController);

?>