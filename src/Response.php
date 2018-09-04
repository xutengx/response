<?php

declare(strict_types = 1);
namespace Xutengx\Response;

use Closure;
use Xutengx\Response\Component\{Body, Header};
use Xutengx\Response\Traits\{File, Format, Get, RequestInfo, Set};
use Xutengx\Tool\Tool;

/**
 * 处理系统全部响应( 输出 )
 * Class Response
 * @package Xutengx\Response
 */
class Response {

	use Set, Get, Format, RequestInfo, File;

	/**
	 * 响应状态码
	 * @var int
	 */
	protected $status;
	/**
	 * @var Tool
	 */
	protected $tool;
	/**
	 * @var Header
	 */
	protected $header;
	/**
	 * @var Body
	 */
	protected $body;
	/**
	 * @var string
	 */
	protected $char;
	/**
	 * @var string
	 */
	protected $acceptType;

	/**
	 * Response constructor.
	 * @param Tool $tool
	 * @param Header $header
	 * @param Body $body
	 * @param string $acceptType
	 * @param string $char
	 * @throws \Xutengx\Exception\Http\NotAcceptableHttpException
	 */
	public function __construct(Tool $tool, Header $header, Body $body, string $acceptType = 'html',
		string $char = 'utf-8') {
		$this->tool       = $tool;
		$this->header     = $header;
		$this->body       = $body;
		$this->acceptType = $acceptType;
		$this->char       = $char;
		$this->setContentType($this->getAcceptType())->setStatus(200);
		$this->body->setChar($this->char);
		$this->header->set('Pragma', 'no-cache')
		             ->add('Cache-Control', 'no-store')
		             ->add('Cache-Control', 'no-revalidate')
		             ->add('Cache-Control', 'no-cache')
		             ->set('Expires', '-1')
		             ->set('X-Powered-By', 'Gaara');
	}

	/**
	 * @return Header
	 */
	public function header(): Header {
		return $this->header;
	}

	/**
	 * 响应内容
	 * 不会清除最后层缓冲区, 也就不会出现响应头重复的问题
	 * 本方法易用性高, 建议业务中正常响应时使用
	 * php默认开启的输出缓冲可能不是无限大小(理论值), 而是诸如4096, 倒数第二层以及之后是gaara开启的缓冲层
	 * @return Response
	 */
	public function send(): Response {
		$this->obRestore(function() {
			$this->header()->send();
			$this->body()->send();
		}, 2);
		return $this;
	}

	/**
	 * 输出并还原缓冲区
	 * @param Closure $Closure 输出 (echo)
	 * @param int $leastLevel 输出时剩余的缓冲层
	 * @param bool $restore 是否还原其他输出
	 * @return void
	 */
	public function obRestore(Closure $Closure, int $leastLevel = 0, bool $restore = true): void {
		$output   = [];
		$MaxLevel = ob_get_level();
		for ($i = $leastLevel; $i < $MaxLevel; $i++) {
			$output[] = $restore ? ob_get_contents() : '';
			ob_end_clean();
		}
		$Closure();
		for ($i = $leastLevel; $i < $MaxLevel; $i++) {
			ob_start();
			echo array_pop($output);
		}
	}

	/**
	 * @return Body
	 */
	public function body(): Body {
		return $this->body;
	}

	/**
	 * 响应内容
	 * 本方法将会即时发送全部响应头
	 * @return Response
	 */
	public function sendReal(): Response {
		$this->obRestore(function() {
			$this->header()->send();
			$this->body()->send();
		}, 0);
		return $this;
	}

	/**
	 * 响应内容
	 * 终止进程并发送全部响应头, 抛弃所有其他缓冲区的内容
	 * @return void
	 */
	public function sendExit(): void {
		$this->obRestore(function() {
			$this->header()->send();
			$this->body()->send();
		}, 0, false);
		exit;
	}

	/**
	 * 返回页面
	 * @param string $file
	 * @return Response
	 * @throws \Gaara\Exception\Http\NotAcceptableHttpException
	 */
	//	public function view(string $file): Response {
	//		$data = obj(Template::class)->view($file);
	//		return $this->setContentType('html')->setContent($data);
	//	}

}
