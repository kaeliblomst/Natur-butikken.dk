<?php

namespace morningtrain\laravelcvr\lib;

use GuzzleHttp\Client;

class cvr {


	protected $client = null;
	protected $searchEndpoint = 'http://distribution.virk.dk/cvr-permanent/_search';

	public function __construct($username, $password) {
		$this->client = new Client([
			'auth' => [$username, $password]
		]);
	}

	public static function instance() {
		$instance = (new self(config('cvr.userid'), config('cvr.password')));
		return $instance;
	}

	protected function parseResponse($response)
	{
		if($response->getStatusCode() === 200)
		{
			$body = $response->getBody()->getContents();
			$data = json_decode($body);
			if(isset($data->hits) && isset($data->hits->hits)){
				$hits = $data->hits->hits;
				$results = [];
				if(count($hits) > 0){
					foreach($hits as $hit){
						$results[] = $hit->_source->Vrvirksomhed;
					}
				}
				if(count($results) === 1){
					return reset($results);
				}
				return $results;
			}
		}
		return null;
	}

	protected function post($endpoint, $parameters)
	{
		$response = $this->client->request('POST', $endpoint, $parameters);
		return $this->parseResponse($response);
	}

	public function findByCVR($cvrNumber)
	{
		$query = [
			'size' => 1,
			'query' => [
				'term' => [
					'cvrNummer' => $cvrNumber
				]
			]
		];
		return $this->post($this->searchEndpoint, [
			'json' => $query
		]);
	}

	public function search($query)
	{
		$query = [
			'size' => 10,
			'query' => [
				'filtered' => [
					'query' => [
						'multi_match' => [
							'query' => $query,
							'type' => 'phrase_prefix',
							'max_expansions' => 1000,
							'fields' => [
								'Vrvirksomhed.cvrNummer',
								'Vrvirksomhed.navne.navn',
								'Vrvirksomhed.binavne.navn'
							]
						]
					]
				]
			]
		];
		return $this->post($this->searchEndpoint, [
			'json' => $query
		]);
	}

}