<?php

class APIDoc
{

	private $apiDoc = [
		"info" => [
			"name" => "vero-api-tests",
			"schema" => "https://schema.getpostman.com/json/collection/v2.0.0/collection.json",
		],
		"item" => [],
	];


	public function options($routes)
	{
		$host = "http://$_SERVER[HTTP_HOST]/";

		foreach ($routes as $pattern => $target) {
			$arr = explode(' ', $pattern);
			$method = $arr[0];
			$url = $host . $arr[1];
			$item = [
				"name" => $pattern,
				"request" => [
					"method" => $method,
					"url" => $url,
				],
			];
			if (isset($target["bodyType"])) {
				$keys = get_object_vars(new $target["bodyType"](null));
				$item["request"]["body"] = [
					"mode" => "raw",
					"raw" => json_encode($keys)
				];
			}
			$this->apiDoc['item'][] = $item;
		}

		return $this->apiDoc;
	}
}