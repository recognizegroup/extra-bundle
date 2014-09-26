<?php

namespace Recognize\ExtraBundle\Service;

/**
 * Class CsvService
 * @package Recognize\ExtraBundle\Service
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class CsvService {

	/**
	 * @param array $data
	 */
	protected function convertValuesEncoding(array &$data) {
		foreach($data as $key => $value) {
			$value = iconv(mb_detect_encoding($value, mb_detect_order(), true), 'UTF-8', $value);
			$value = trim($value);
			$data[$key] = $value;
		}
	}

	/**
	 * @param string $fileName
	 * @param string $delimiter
	 * @param callback $callback
	 * @param null|string $enclosure
	 * @param null|string $escape
	 * @throws \Exception
	 * @internal param bool $convert
	 */
	public function import($fileName, $delimiter, $callback, $enclosure = '"', $escape = '\\') {
		try {
			ini_set("auto_detect_line_endings", true);
			if(!is_callable($callback)) throw new \Exception(sprintf('expected callable but got %s', gettype($callback)));
			if(($handle = fopen($fileName, 'r')) !== false) {
				while (($data = fgetcsv($handle, null, $delimiter, $enclosure, $escape)) !== false) {
					$this->convertValuesEncoding($data); // Convert to UTF-8
					call_user_func($callback, $data);
				}
			}
			fclose($handle);
			ini_set("auto_detect_line_endings", false);
		} catch(\Exception $e) {
			throw new \Exception(sprintf('Unable to import CSV file: %s', $e->getMessage()));
		}
	}

}