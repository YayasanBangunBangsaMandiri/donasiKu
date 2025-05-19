<?php
namespace GuzzleHttp;

/**
 * Simple mock GuzzleHttp Client class
 * This is a temporary solution to fix the missing dependency error
 */
class Client
{
    private $options;
    
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }
    
    /**
     * Mock method for sending GET requests
     */
    public function get($uri, array $options = [])
    {
        return new \stdClass();
    }
    
    /**
     * Mock method for sending POST requests
     */
    public function post($uri, array $options = [])
    {
        return new \stdClass();
    }
    
    /**
     * Mock method for sending requests of any method
     */
    public function request($method, $uri, array $options = [])
    {
        return new \stdClass();
    }
} 