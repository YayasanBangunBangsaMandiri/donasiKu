<?php
namespace GuzzleHttp;

/**
 * Mock ResponseInterface
 * This is a temporary solution to fix the missing dependency error
 */
interface ResponseInterface
{
    /**
     * Get the status code
     */
    public function getStatusCode();
    
    /**
     * Get the body of the response
     */
    public function getBody();
    
    /**
     * Get a response header
     */
    public function getHeader($header);
    
    /**
     * Get all headers
     */
    public function getHeaders();
} 