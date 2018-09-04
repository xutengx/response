<?php

declare(strict_types = 1);
namespace Xutengx\Response\Traits;

use Xutengx\Model\QueryChunk;
use Xutengx\Response\Response;
use Xutengx\Tool\Tool;

/**
 * 提供下载响应
 */
trait File {

	/**
	 * 下载大文件
	 * @param string $downloadFile
	 * @param string $downloadFileName
	 * @return Response
	 */
	public function download(string $downloadFile, string $downloadFileName = null): Response {
		$file     = $this->tool->absoluteDir($downloadFile);
		$filename = $downloadFileName ?? basename($file);

		$this->header()
		     ->set('Accept-Length', filesize($file))
		     ->set('Content-Length', filesize($file))
		     ->set('Content-Type', 'application/octet-stream')
		     ->set('Accept-Ranges', 'bytes')
		     ->set('Content-Description', 'File Transfer')
		     ->set('Content-Transfer-Encoding', 'chunked')
		     ->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
		$this->sendReal();

		// 手动 sendReal 避免频繁开关缓冲区
		$this->obRestore(function() use ($file) {
			if (false !== ($handler = fopen($file, 'rb'))) {
				flock($handler, LOCK_SH);
				while ($chunk = fread($handler, 4096)) {
					echo $chunk;
				}
				flock($handler, LOCK_UN);
				fclose($handler);
			}
		}, 0, false);
		return $this;
	}

	/**
	 * 直接下载某个文件
	 * @param string $downloadFile
	 * @param string $downloadFileName
	 * @return Response
	 */
	public function downloadMemory(string $downloadFile, string $downloadFileName = null): Response {
		$file     = $this->tool->absoluteDir($downloadFile);
		$filename = $downloadFileName ?? basename($file);

		$handler = fopen($file, 'rb');
		flock($handler, LOCK_SH);
		$this->header()
		     ->set('Accept-Length', filesize($file))
		     ->set('Accept-Ranges', 'bytes')
		     ->set('Content-type', 'application/octet-stream')
		     ->set('Content-Disposition', "attachment; filename=" . $filename);
		$this->setContent(fread($handler, filesize($file)))->sendReal();
		flock($handler, LOCK_UN);
		fclose($handler);
		return $this;
	}

	/**
	 * 将数据库分块 (getChunk()) 或者 数据库结果 (getAll()), 导出为csv格式
	 * 为性能提升, 将直接发送响应头
	 * @param array|QueryChunk $QueryChunkOrArray
	 * @param string $downloadFileName
	 * @return Response
	 */
	public function exportCsv($QueryChunkOrArray, string $downloadFileName = null): Response {
		$filename = $downloadFileName ? rtrim($downloadFileName, '.csv') . '.csv' : time() . '.csv';

		$this->header()
		     ->set('Content-Type', 'mime/type')
		     ->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

		// 手动 sendReal 避免频繁开关缓冲区
		$this->obRestore(function() use ($QueryChunkOrArray) {
			$is_QueryChunk = ($QueryChunkOrArray instanceof QueryChunk) ? true : false;
			foreach ($QueryChunkOrArray as $v) {
				$this->setContent($this->arrayKeyValueToCsvRows($v, $is_QueryChunk))->send();
				break;
			}
			foreach ($QueryChunkOrArray as $v) {
				$this->body()->setContent($this->arrayValueToCsvRow($v))->send();
			}
		}, 0, false);
		return $this;
	}

	/**
	 * 一位数组的键和值分别转化为csv的一行
	 * 生成器需要返回2行, 而普通数组只需要1行
	 * @param array $arr
	 * @param bool $is_QueryChunk
	 * @return string
	 */
	protected function arrayKeyValueToCsvRows(array $arr, bool $is_QueryChunk = true): string {
		$keyArr = array_keys($arr);
		$str    = '"' . implode('","', $keyArr) . '"' . "\n";
		$str    .= $is_QueryChunk ? $this->arrayValueToCsvRow($arr) : '';
		return $str;
	}

	/**
	 * 一位数组的值转化为csv的一行
	 * 在数字左侧加上等号
	 * @param array $arr
	 * @return string
	 */
	protected function arrayValueToCsvRow(array $arr): string {
		$str = '';
		foreach ($arr as $v)
			$str .= (is_numeric($v) ? '=' : '') . '"' . $v . '",';
		return rtrim($str, ',') . "\n";
	}

}
