<?php
/**
 * This file is part of the Nella Project (http://nella-project.org).
 *
 * Copyright (c) Patrik Votoček (http://patrik.votocek.cz)
 *
 * For the full copyright and license information,
 * please view the file LICENSE.md that was distributed with this source code.
 */

namespace Nella\Forms\DI;

use Nette\DI\CompilerExtension;
use Nette\PhpGenerator;


/**
 * @author        Patrik Votoček
 */
class DateTimeExtension extends CompilerExtension
{

	public function afterCompile(PhpGenerator\ClassType $class)
	{
		$init = $class->methods['initialize'];
		$init->addBody('\Nella\Forms\DateTime\DateInput::register();');
		$init->addBody('\Nella\Forms\DateTime\DateTimeInput::register();');
	}

}