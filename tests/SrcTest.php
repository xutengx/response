<?php
declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Xutengx\Response\Response;
use Xutengx\Tool\Tool;
use Xutengx\Response\Component\{
	Header, Body
};

final class SrcTest extends TestCase {

	public function setUp() {
	}

	public function testObject() {
		$this->assertInstanceOf(Tool::class, $Tool = new Tool);
		$this->assertInstanceOf(Header::class, $Header = new Header);
		$this->assertInstanceOf(Body::class, $Body = new Body);
		$this->assertInstanceOf(Response::class, $response = new Response($Tool, $Header, $Body));

	}

}


