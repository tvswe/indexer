<?php

class Indexer implements \Serializable
{
    protected $callables = [];

    protected $index = [];

    public function __construct(...$callables)
    {
        if(!$callables) {
            throw new \InvalidArgumentException('Callables are missed!');
        }

        $set = 1;
        foreach ($callables as $callable) {
            if(!is_callable($callable)) {
                throw new \InvalidArgumentException('Expected callable, but got ' . gettype($callable) . '!');
            }

            $this->callables[$set++] = $callable;
        }

        $this->rebuild();
    }

    public function rebuild($set = 0)
    {
        if(array_key_exists($set, $this->callables)) {
            $this->index[$set] = call_user_func($this->callables[$set]);
            return;
        }

        if(!$set) {
            array_walk(array_keys($this->callables), [$this, 'rebuild']);
            return;
        }

        throw new \UnexpectedValueException('Index not valid!');
    }

    public function getResult($set = 0) {
        if(!$set) {
            return call_user_func_array('array_merge', $this->index);
        }

        if(array_key_exists($set, $this->index)) {
            return $this->index[$set];
        }

        throw new \UnexpectedValueException('Index not valid!');
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize([
            'callables' => $this->callables,
            'index' => $this->index
        ]);
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->callables = $data['callables'];
        $this->index = $data['index'];
    }
}
