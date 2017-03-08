<?php

namespace MakePrintable;
use MakePrintable\Exceptions\RuntimeException;

use GuzzleHttp\Client as Http;
/**
* This class makes sure to parse makeprintable's json config file into php object. 
*/
class Client
{
	const GRANT_TYPE_CLIENT_CREDENTIALS = 1;
	const GRANT_TYPE_AUTHORIZATION_CODE = 2;

	private $host;
	private $clientId;
	private $clientSecret;

	private $httpClient;
	private $accessToken;

	public function __construct(\stdClass $config)
	{
		$this->host = trim($config->host, '/');
		$this->clientId = $config->client_id;
		$this->clientSecret = $config->client_secret;

		$this->httpClient = new Http;
	}

	public function getAccessToken(int $grantType)
	{
		switch ($grantType) {
			case self::GRANT_TYPE_CLIENT_CREDENTIALS :
				$requestBody = [
					'multipart' =>
					[
						[
							'name' => 'client_id',
							'contents' => $this->clientId
						],
						[
							'name' => 'client_secret',
							'contents' => $this->clientSecret
						],
						[
							'name' => 'grant_type',
							'contents' => "client_credentials"
						]
					]
				];
				break;
			
			case self::GRANT_TYPE_AUTHORIZATION_CODE :
				// TODO: create an authorization url and return it.
				break;
			default:
				throw new RuntimeException('Invalid grant_type provided to getAccessToken method!');
				break;
		}
		try {
			$response = $this->httpClient->request('POST', $this->createEndpointUrl('oauth2/token'), $requestBody);
			$response =json_decode($response->getBody()->getContents());
		} catch (\Exception $e) {
			return $e->getResponse()->getBody()->getContents();
		}

		$this->accessToken = $response->access_token;
		return true;
	}

	public function upload($filePath = null, $fileUrl = null)
	{
		if (!empty($filePath)) {
			try {
				$response = $this->sendEndpointRequest('items', 'POST', [
					'file' => ['filename' => basename($filePath), 'contents' => fopen($filePath, 'r+')]
				]);
			} catch (Exception $e) {
				var_dump($e->getMessage());die;
			}
		}
		elseif ((!empty($fileUrl))) {
			
		}
		else
			throw new RuntimeException('You must provide either filePath or fileUrl to the upload method!');

		return $response;
	}

	public function convert($id, \Closure $progressCallback = null)
	{
		do {
			$response = $this->sendEndpointRequest('items/convert', 'POST', [
				'item_id' => $id
			]);

			if (!empty($progressCallback))
				$progressCallback($response);

			sleep(3);
		} while ($response->status == 201 || ($response->status == 200 && $response->data->progress == 'In Progress'));

		return $response->data;
	}

	public function analyze($id, \Closure $progressCallback = null)
	{
		do {
			$response = $this->sendEndpointRequest('items/analyze', 'POST', [
				'item_id' => $id
			]);

			if (!empty($progressCallback))
				$progressCallback($response);

			sleep(3);
		} while ($response->status == 201 || ($response->status == 200 && $response->data->progress == 'In Progress'));

		return $response->data;
	}

	public function repair($id, $name = null, array $fixerSettings = ['type' => '3d', 'hollow' => '0'], \Closure $progressCallback = null)
	{
		do {
			$repairRequest = ['item_id' => $id, 'fixer_settings' => json_encode($fixerSettings)];
			if ($name)
				$repairRequest['name'] = $name;

			$response = $this->sendEndpointRequest('items/fix', 'POST', $repairRequest);

			if (!empty($progressCallback))
				$progressCallback($response);

			sleep(3);
		} while ($response->status == 201 || ($response->status == 200 && $response->data->status == 'fixing'));

		return $response->data;
	}

	private function createEndpointUrl(string $endpoint)
	{
		return "$this->host/". trim($endpoint, '/');
	}

	private function sendEndpointRequest($endpoint, $method = 'GET', $requestBody = [])
	{
		$parts = [];
		foreach ($requestBody as $name => $contents) {
			$part = ['name' => $name, 'contents' => $contents];
			
			if ($name === 'file') {
				$part['filename'] = $contents['filename'];
				$part['contents'] = $contents['contents'];
			}

			$parts[] = $part;
		}

		$response = $this->httpClient->request($method, $this->createEndpointUrl("v2/$endpoint"), [
			'multipart' => $parts,
			'headers' => [
				'Authorization' => "Bearer $this->accessToken"
			]
		]);

		return json_decode($response->getBody()->getContents());
	}
}
