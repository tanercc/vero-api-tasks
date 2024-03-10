<?php

class ConstructionStages
{
	private $db;

	public function __construct()
	{
		$this->db = Api::getDb();
	}

	public function getAll()
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
		");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getSingle($id)
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
			WHERE ID = :id
		");
		$stmt->execute(['id' => $id]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function post(ConstructionStagesCreate $data)
	{
		$stmt = $this->db->prepare("
			INSERT INTO construction_stages
			    (name, start_date, end_date, duration, durationUnit, color, externalId, status)
			    VALUES (:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
			");
		$stmt->execute([
			'name' => $data->name,
			'start_date' => $data->startDate,
			'end_date' => $data->endDate,
			'duration' => $data->duration,
			'durationUnit' => $data->durationUnit,
			'color' => $data->color,
			'externalId' => $data->externalId,
			'status' => $data->status,
		]);
		return $this->getSingle($this->db->lastInsertId());
	}

	/**
	 * Update a construction stage
	 * @param ConstructionStagesCreate $data
	 * @param $id
	 * @return array
	 */
	public function patch(ConstructionStagesCreate $data, $id)
	{
		$proper_statuses = [
			'NEW',
			'PLANNED',
			'DELETED',
		];
		if ($data->status && !in_array($data->status, $proper_statuses)) {
			return ['error' => 'Invalid status'];
		}
		$sql_vars = [];
		$sql_values = [];
		foreach (get_object_vars($data) as $key => $value) {
			if ($value) {
				$sql_vars[] = (strpos($key, 'Date') === false ? $key : $this->camelCaseToSnakeCase($key)) . ' = :' . $key;
				$sql_values[$key] = $value;
			}
		}
		$sql = 'UPDATE construction_stages SET ' . implode(', ', $sql_vars) . ' WHERE ID = :id';
		$stmt = $this->db->prepare($sql);
		$stmt->execute(array_merge($sql_values, ['id' => $id]));
		return $this->getSingle($id);
	}

	/**
	 * Delete a construction stage
	 * @param $id
	 * @return array
	 */
	public function delete($id)
	{
		$stmt = $this->db->prepare("
            UPDATE construction_stages
            SET status = 'DELETED'
            WHERE ID = :id
        ");
		$stmt->execute(['id' => $id]);
		return $this->getSingle($id);
	}

	/**
	 * Convert camelCase to snake_case
	 * @param $str
	 * @return string
	 */
	private function camelCaseToSnakeCase($str)
	{
		return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $str));
	}
}