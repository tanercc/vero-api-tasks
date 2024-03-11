<?php

class Calculate
{

	/**
	 * Calculate the duration of the construction stage
	 * @param $data
	 * @return null|float
	 * @throws Exception
	 */
	public static function duration(ConstructionStagesCreate $data)
	{
		if ($data->endDate === null) {
			return null;
		}

		$startDate = new DateTime($data->startDate);
		$endDate = new DateTime($data->endDate);
		$interval = $startDate->diff($endDate);

		switch ($data->durationUnit) {
			case 'HOURS':
				return $interval->h;
			case 'WEEKS':
				return $interval->days > 0 ? $interval->days / 7 : 0;
			default:
				return $interval->days;
		}
	}
}