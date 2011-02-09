<?php
/**
 *	Main base class.
 *
 *	Class is used as a base for most of the classes throughout the platform.
 *	It includes all the functions that are necessary to work
 *	using the, "Impact formula".
 *		
 *	@author Stephen Simpson <me@simpo.org>
 *	@version 0.1
 *	@license http://www.gnu.org/licenses/lgpl.html LGPL
 *	@package Impact
 */
class ImpactBase {
	
	/**
	 *	Factory method
	 *
	 *	The factory method is used to generate new classes according to the
	 *	standard rules of the Impact Platform.  The class is sometimes overridden
	 *	in base-classes if the class has it's own sub-classes that are loaded
	 *	on-the-fly.  Classes are only included if they are used,
	 *	saving load over-heads.  The factory method knows where to find the files.
	 *
	 *	@public
	 *	@param String $className The name of the class to load.
	*/
	public function factory($className) {
		$classFileName = str_replace('_',DIRECTORY_SEPARATOR,$className).'.php';
		
		if (!include_once MODELS_DIRECTORY.DIRECTORY_SEPARATOR.$classFileName) {
			if (I::contains($classFileName,'Base.php')) {
				$classFileName = str_replace(
					DIRECTORY_SEPARATOR.'Base.php',
					'.php',
					$classFileName
				);
				if (!include_once MODELS_DIRECTORY.DIRECTORY_SEPARATOR.$classFileName) {
					throw new Exception($className.' Class not found');
				}
			}
		}
		
		try {
			$class = new $className;
			return $class;
		} catch (Exception $e) {
			throw new Exception($className.' Class not found');
		}
		
	}
}