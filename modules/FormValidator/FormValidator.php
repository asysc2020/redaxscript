<?php
namespace Redaxscript\Modules\FormValidator;

use Redaxscript\Head;
use Redaxscript\Module;

/**
 * javascript powered form validation
 *
 * @since 4.0.0
 *
 * @package Redaxscript
 * @category Modules
 * @author Henry Ruhs
 */

class FormValidator extends Module\Module
{
	/**
	 * array of the module
	 *
	 * @var array
	 */

	protected static $_moduleArray =
	[
		'name' => 'Form Validator',
		'alias' => 'FormValidator',
		'author' => 'Redaxmedia',
		'description' => 'JavaScript powered form validation',
		'version' => '4.3.0'
	];

	/**
	 * renderStart
	 *
	 * @since 4.0.0
	 */

	public function renderStart() : void
	{
		$script = Head\Script::getInstance();
		$script
			->init('foot')
			->appendFile(
			[
				'modules/FormValidator/assets/scripts/init.js',
				'modules/FormValidator/dist/scripts/form-validator.min.js'
			]);
	}
}
