<?php


class Validation
{
	/**
	 * @var string[]
	 */
	private $rules = [
		'name' => 'required|string|max:255',
		'startDate' => 'required|iso8601',
		'endDate' => 'iso8601|laterThanStartDate',
		'durationUnit' => 'defaultValue:DAYS|match:HOURS,DAYS,WEEKS',
		'color' => 'color',
		'externalId' => 'max:255',
		'status' => 'defaultValue:NEW|match:NEW,PLANNED,DELETED',
	];

	private $data;

	/**
	 * Validate ConstructionStages data
	 * If it is valid return with default values
	 * Throw an exception if the data is invalid
	 * @param ConstructionStagesCreate $data
	 * @throws Exception
	 */
	public function __construct(ConstructionStagesCreate $data)
	{
		$this->data = $data;
		return $this->validate($data);
	}

	/**
	 * Check if the value is not empty
	 * @param $field
	 * @param $value
	 * @return bool
	 */
	private function required($field, $value)
	{
		return !empty($value);
	}

	/**
	 * Check if the value is a string
	 * @param $field
	 * @param $value
	 * @return bool
	 */
	private function string($field, $value)
	{
		return is_string($value);
	}

	/**
	 * Check if the string is not longer than the max value
	 * @param $field
	 * @param $value
	 * @param $max
	 * @return bool
	 */
	private function max($field, $value, $max)
	{
		return strlen($value) <= $max;
	}

	/**
	 * Check if the date is in ISO8601 format
	 * @param $field
	 * @param $value
	 * @return bool
	 */
	private function iso8601($field, $value)
	{
		if (empty($value)) {
			return true;
		}
		return preg_match('/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/', $value) > 0;
	}

	/**
	 * Check if the end date is after the start date
	 * @param $field
	 * @param $value
	 * @return bool
	 */
	private function laterThanStartDate($field, $value)
	{
		if (!empty($value)) {
			$start = strtotime($this->data->startDate);
			return strtotime($value) > $start;
		} else {
			return true;
		}
	}

	/**
	 * Set a default value if the value is empty
	 * @param $field
	 * @param $value
	 * @param $default
	 * @return bool
	 */
	private function defaultValue($field, $value, $default)
	{
		if (empty($value)) {
			$this->data->$field = $default;
		}
		return true;
	}

	/**
	 * Check if the value is in a list of possible values
	 * @param $field
	 * @param $value
	 * @param $match
	 * @return bool
	 */
	private function match($field, $value, $match)
	{
		return in_array($value, explode(',', $match));
	}

	/**
	 * Check if the value is a color
	 * @param $field
	 * @param $value
	 * @return bool
	 */
	private function color($field, $value)
	{
		if (empty($value)) {
			return true;
		}
		return preg_match('/^#([a-f0-9]{6}|[a-f0-9]{3})$/i', $value) > 0;
	}

	/**
	 * Validate the data
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	public function validate()
	{
		foreach ($this->rules as $field => $rule) {
			$rules = explode('|', $rule);
			foreach ($rules as $part) {
				$rule = explode(':', $part);
				$method = array_shift($rule);
				$params = $rule;
				if (!$this->$method($field, $this->data->$field, ...$params)) {
					throw new Exception("Invalid value for $field");
				}
			}
		}

		return $this->data;
	}
}