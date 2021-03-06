<?php

/**
 * A decorator for File or a subclass that provides a method for extracting full-text from the file's external contents.
 * @author mstephens
 *
 */
abstract class FileTextExtractor extends Object {
	/**
	 * Set priority from 0-100.
	 * The highest priority extractor for a given content type will be selected.
	 *
	 * @config
	 * @var int
	 */
	private static $priority = 50;

	protected static $sorted_extractor_classes = null;

	/**
	 * @param  String $path
	 * @return FileTextExtractor
	 */
	static function for_file($path) {
		$extension = pathinfo($path, PATHINFO_EXTENSION);

		if (!self::$sorted_extractor_classes) {
			// Generate the sorted list of extractors on demand.
			$classes = ClassInfo::subclassesFor("FileTextExtractor");
			array_shift($classes);
			$sortedClasses = array();
			foreach($classes as $class) $sortedClasses[$class] = Config::inst()->get($class, 'priority');
			arsort($sortedClasses);

			self::$sorted_extractor_classes = $sortedClasses;
		}
		foreach(self::$sorted_extractor_classes as $className => $priority) {
			$formatter = new $className();
			$matched = array_filter($formatter->supportedExtensions(), function($compare) use($extension) {
				return (strtolower($compare) == strtolower($extension));
			});
			if($matched) return $formatter;
		}
	}

	/**
	 * Checks if the extractor is supported on the current environment,
	 * for example if the correct binaries or libraries are available.
	 * 
	 * @return boolean
	 */
	abstract function isAvailable();

	/**
	 * Return an array of content types that the extractor can handle.
	 * @return unknown_type
	 */
	abstract function supportedExtensions();

	/**
	 * Given a file path, extract the contents as text.
	 * 
	 * @param $path
	 * @return unknown_type
	 */
	abstract function getContent($path);
}

class FileTextExtractor_Exception extends Exception {}