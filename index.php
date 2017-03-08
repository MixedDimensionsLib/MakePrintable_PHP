<?php

require_once __DIR__ . "/vendor/autoload.php";

use MakePrintable\ConfigProvider;
use MakePrintable\Client;

/**
* Sample MakePrintable Consumer Class
* 
* @author Mumen Yassin
*/
class MakePrintableSample
{
	/**
	* @method void This method runs MakePrintable sample app. 
	*/
	public static function runSample()
	{
		// Authorizing MakePrintable client.
		try {
			$config = (new ConfigProvider)->getConfig();
			$makePrintableClient = new Client($config);
			$makePrintableClient->getAccessToken(Client::GRANT_TYPE_CLIENT_CREDENTIALS);
		} catch (\Exception $e) {
			die($e->getMessage());
		}

		// Uploading model to MakePrintable.
		try {
			echo "Uploading Model...";
			$uploadedFile = $makePrintableClient->upload(__DIR__ . '/3DModel.3ds');
			echo PHP_EOL;
		} catch (\Exception $e) {
			die($e->getMessage());
		}

		// Converting model.
		try {
			echo "Converting Model.";
			$convertedModel = $makePrintableClient->convert($uploadedFile->id, function($update){
				echo ".";
			});
			echo PHP_EOL;
		} catch (\Exception $e) {
			die($e->getMessage());
		}

		// Analyzing model.
		try {
			echo "Analyzing Model.";
			$analyzedModel = $makePrintableClient->analyze($uploadedFile->id, function($update){
				echo ".";
			});
			echo PHP_EOL;
		} catch (\Exception $e) {
			die($e->getMessage());
		}

		// Repairing model.
		try {
			echo "Repairing Model.";
			$fixedModel = $makePrintableClient->repair($uploadedFile->id, 'Cool Fixed Model', ['type' => '3d', 'hollow' => '0'], function($update){
				echo ".";
			});
			echo PHP_EOL;
		} catch (\Exception $e) {
			die($e->getMessage());
		}

		// Repairing model.
		try {
			echo "Converting Model To 3MF.";
			$threeMFModel = $makePrintableClient->download($uploadedFile->id, Client::TYPE_3MF, function($update){
				echo ".";
			});
			echo PHP_EOL;
		} catch (\Exception $e) {
			die($e->getMessage());
		}

		// You can download the file using the link returned from "download" method.
		echo "Downloading Model.";
		file_put_contents(__DIR__.'/MyFixedModel.zip', fopen($threeMFModel->download_link, 'r'));
		
		echo PHP_EOL;
		echo "Model Repaired & Downloaded Successfully.";
		echo PHP_EOL;
	}
}

if (pathinfo(__FILE__)['basename'] == $_SERVER['SCRIPT_FILENAME']) {
	MakePrintableSample::runSample();
}
