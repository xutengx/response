<?php

declare(strict_types = 1);
namespace Xutengx\Response\Component;

class Body {

	/**
	 * 响应的格式 json
	 * @var string
	 */
	protected $contentType;
	/**
	 * 响应的内容
	 * @var mixed
	 */
	protected $content;
	/**
	 * 响应的编码 UTF-8
	 * @var string
	 */
	protected $char;
	/**
	 * 代码的编码 UTF-8
	 * @var string
	 */
	protected $documentsChar;

	/**
	 * Body constructor.
	 * @param string $documentsChar
	 */
	public function __construct(string $documentsChar = 'UTF-8') {
		$this->documentsChar = strtoupper($documentsChar);
	}

	/**
	 * 设置Http响应的文档类型
	 * @param string $contentType
	 * @return Body
	 */
	public function setContentType(string $contentType): Body {
		$this->contentType = strtolower($contentType);
		return $this;
	}

	/**
	 * 设置响应字符集
	 * @param string $char
	 * @return Body
	 */
	public function setChar(string $char): Body {
		$this->char = strtoupper($char);
		return $this;
	}

	/**
	 * 发送响应
	 * @return Body
	 */
	public function send(): Body {
		echo $this->charEncode($this->contentTypeEncode($this->content));
		return $this->setContent(null);
	}

	/**
	 * 设置响应内容
	 * @param mixed $content
	 * @return Body
	 */
	public function setContent($content): Body {
		$this->content = $content;
		return $this;
	}

	/**
	 * 响应格式
	 * @param mixed $data
	 * @return string
	 */
	protected function contentTypeEncode($data): string {
		$encode = '';
		switch ($this->contentType) {
			case 'json':
				$encode = json_encode($data, JSON_UNESCAPED_UNICODE);
				break;
			case 'xml':
				$encode = $this->tool->xml_encode($data, $this->char);
				break;
			case 'php':
				$encode = serialize($data);
				break;
			case 'html':
				$encode = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
				break;
		}
		return (string)$encode;
	}

	/**
	 * 响应编码
	 * @param string $content
	 * @return string
	 */
	protected function charEncode(string $content): string {
		return iconv($this->documentsChar, $this->char, $content);
	}

}
