<?php
require_once 'Autoloader.php';
Autoloader::register();
new Api();

class Api
{
	private static $db;

	public static function getDb()
	{
		return self::$db;
	}

	public function __construct()
	{
		self::$db = (new Database())->init();

		$uri = strtolower(trim((string)$_SERVER['PATH_INFO'], '/'));
		$httpVerb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';

		$wildcards = [
			':any' => '[^/]+',
			':num' => '[0-9]+',
		];
		$routes = [
			'get constructionStages' => [
				'class' => 'ConstructionStages',
				'method' => 'getAll',
			],
			'get constructionStages/(:num)' => [
				'class' => 'ConstructionStages',
				'method' => 'getSingle',
			],
			'post constructionStages' => [
				'class' => 'ConstructionStages',
				'method' => 'post',
				'bodyType' => 'ConstructionStagesCreate'
			],
			'patch constructionStages/(:num)' => [
				'class' => 'ConstructionStages',
				'method' => 'patch',
				'bodyType' => 'ConstructionStagesCreate'
			],
			'delete constructionStages/(:num)' => [
				'class' => 'ConstructionStages',
				'method' => 'delete',
			],
			'options constructionStages' => [
				'class' => 'APIDoc',
				'method' => 'options',
			],
		];

		$response = [
			'error' => 'No such route',
		];

		if ($uri) {

			foreach ($routes as $pattern => $target) {
				$pattern = str_replace(array_keys($wildcards), array_values($wildcards), $pattern);
				if (preg_match('#^'.$pattern.'$#i', "{$httpVerb} {$uri}", $matches)) {
					$params = [];
					array_shift($matches);
					if (in_array($httpVerb, ['post', 'patch'])) {
						$data = json_decode(file_get_contents('php://input'));
						if (json_last_error() !== JSON_ERROR_NONE) {
							$response = ['error' => 'Invalid JSON'];
							break;
						}
						$params = [new $target['bodyType']($data)];
						try {
							$params[0] = (new Validation($params[0]))->validate();
							$params[0]->duration = Calculate::duration($params[0]);
						} catch (Exception $e) {
							$response = ['error' => $e->getMessage()];
							break;
						}
					} else if ($httpVerb === 'options') {
						$params[0] = $routes;
					}
					$params = array_merge($params, $matches);
					$response = call_user_func_array([new $target['class'], $target['method']], $params);
					break;
				}
			}

			echo json_encode($response, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		}
	}
}