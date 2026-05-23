<?php

namespace Yeoldebasil\Biscuit;

use Stringable, Iterator, Countable;

class Str implements Stringable, Iterator, Countable 
{
	protected Arr $buffer;
	protected int $bol = 0;
	protected int $row = 0;
	public int $length = 0;

	function __construct($contents) {
		$this->buffer = new arr(
			mb_str_split((string) $contents)
		);

		$this->length = mb_strlen($contents);
	}

	// necessary for countable
	function count(): int 
    {
		return count($this->buffer);
	}

	// necessary for iterator
	function rewind(): void 
    {
		$this->buffer->rewind();
	}

	function current(): mixed 
    {
		return $this->buffer->current();
	}

	function key(): mixed 
    {
		return $this->buffer->key();
	}

	function next(): void 
    {
		$this->buffer->next();
	}

	function valid(): bool
    {
		return $this->buffer->valid();
	}

	function print(): void 
    {
		print($this->__toString());
	}

	function upper() 
    {
		return new $this(
			mb_strtoupper($this->buffer->glue())
		);
	}

	function lower() 
    {
		return new $this(
			mb_strtolower($this->buffer->glue())
		);
	}

	function ucfirst() 
    {
		return new $this(
			ucfirst($this->buffer->glue())
		);
	}

	function cut(int $offset, ?int $length = null) {
		return new Str(substr($this->buffer->glue(), $offset, $length));
	}

	function explode(str $separator) 
    {
		return new arr(
			explode($separator, $this->buffer->glue())
		);
	}

	function repeat(int $multiplier) 
    {
		return new $this(
			str_repeat($this->data, $multiplier)
		);
	}	

	function md5() 
    {
		return new $this(md5($this->contents));
	}

	function sha1() 
    {
		return new $this(sha1($this->contents));
	}

	function eval(): mixed 
    {
		return eval($this->data);
	}

	function contains(string $needle): bool 
    {
		return str_contains($this->data, $needle);
	}

	function endsWith(string $needle): bool 
    {
		return str_ends_with($this->contents, $needle);
	}

	function base64() 
    {
		return new $this(
			base64_encode($this->contents)
		);
	}

	function toArray() 
    {
		return explode("\n", $this->contents);		
	}

	function tabs(int $level) 
    {
		$buff = "";

		foreach ($this->toArray() as $line) {
			$buff .= str("\t")->repeat($level) . "{$line}\n";
		}

		return new $this($buff);
	}	

	function __toString() 
    {
		return $this->buffer->glue();
	}
}