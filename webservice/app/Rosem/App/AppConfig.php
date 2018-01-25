<?php

namespace Rosem\App;

use ArrayAccess;
use Countable;
use TrueStd\App\AppConfigInterface;

class AppConfig implements AppConfigInterface, ArrayAccess, Countable
{
    /**
     * Data array
     *
     * @var array $data
     */
    protected $data;

    /**
     * Query string delimiter
     *
     * @var string $delimiter
     */
    protected $delimiter;

    /**
     * ArraySeparatorQuery constructor.
     *
     * @param array  $data
     * @param string $delimiter
     */
    public function __construct(array $data = [], string $delimiter = '.')
    {
        $this->delimiter = $delimiter;
        $this->data = $data;
    }

    public static function fromArray(array $data)
    {
        return new static($data);
    }

    /**
     * Recursively getting value from array by query
     *
     * @param array $array
     * @param mixed $default
     * @param int   $offset
     * @param array $path
     * @param int   $lastIndex
     *
     * @return mixed
     */
    protected function getVal(array &$array, &$default, array &$path, int $offset, int &$lastIndex)
    {
        $next = &$array[$path[$offset]];

        return $offset < $lastIndex
            ? $this->getVal($next, $default, $path, ++$offset, $lastIndex)
            : (null === $next ? $default : $next);
    }

    /**
     * Recursively getting array item reference by query
     *
     * @param array   $array
     * @param integer $offset
     * @param         $path
     * @param         $lastIndex
     *
     * @return mixed
     */
    protected function &getRef(array &$array, array &$path, int $offset, int &$lastIndex)
    {
        $next = &$array[$path[$offset]] ?? [];

        if ($offset < $lastIndex) {
            return $this->getRef($next, $path, ++$offset, $lastIndex);
        }

        return $next;
    }

    /**
     * Select value by query
     *
     * @param string $query
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $query, $default = null)
    {
        $path = explode($this->delimiter, $query);
        $lastIndex = count($path) - 1;

        return $this->getVal($this->data, $default, $path, 0, $lastIndex);
    }

    /**
     * Set value by query
     *
     * @param string $query
     * @param mixed  $value
     */
    public function set(string $query, $value) : void
    {
        $path = explode($this->delimiter, $query);
        $lastIndex = count($path) - 1;
        $placeholder = &$this->getRef($this->data, $path, 0, $lastIndex);
        $placeholder = $value;
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * Offset to set
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        null === $offset ? $this->data[] = $value : $this->data[$offset] = $value;
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * Count elements of an object.
     *
     * @return int The custom count as an integer.
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->data);
    }
}
