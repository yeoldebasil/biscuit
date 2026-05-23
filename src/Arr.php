<?php

namespace Yeoldebasil\Biscuit;

use Iterator, Countable, ValueError;

class Arr implements Iterator, Countable 
{
	protected array $data = [];
	protected int 	$position = 0;

	function __construct(array $data) {
		if (!array_is_list($contents))
			throw new ValueError("Map array is not yet supported", 1);
			
		$this->data = $data;
	}

	// necessary for countable
	function count(): int 
	{
		return count($this->data);
	}

	// necessary for iterator
	function rewind(): void 
	{
		$this->position = 0;
	}

	function current(): mixed 
	{
		return $this->data[$this->position];
	}

	function key(): mixed 
	{
		return $this->data[$this->position];
	}

	function next(): void
	{
		++$this->position;
	}

	function valid(): bool 
	{
		return isset($this->data[$this->position]);
	}

	// necessary for property access
	function &__get($key) 
	{
		return $this->data[$key];
	}

	function __set($key, $value) 
	{
		$this->data[$key] = $value;
	}

	function __isset($key) 
	{
		return isset($this->data[$key]);
	}

	function __unset($key) 
	{
		unset($this->data[$key]);
	}

	function contains(mixed $needle, bool $strict = false): bool
    {
		return in_array($needle, $this->data, $strict);
	}
	
	function lastKey() 
    {
		return array_key_last($this->data);
	}

	function flip() 
    {
		return new $this(array_flip($this->data));
	}

	function glue(?string $separator = null) 
    {
		return $separator 
			? implode($separator, $this->data)
			: implode($this->data);
	}

	function max(): mixed 
    {
		return max($this->data);
	}

	function json(): Str 
    {
		return new Str(
			json_encode($this->data);
		);
	}
}