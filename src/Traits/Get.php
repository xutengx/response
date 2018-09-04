<?php

declare(strict_types = 1);
namespace Xutengx\Response\Traits;

trait Get {

	/**
	 * 得到http描述
	 * @param int $code
	 * @return string
	 */
	public function getMessageByHttpCode(int $code): string {
		return static::$httpStatus[$code];
	}

}
