<?php

/**
 * Created by PhpStorm.
 * User: srana
 * Date: 8/24/2017
 * Time: 11:59 AM
 */

namespace App;

use App;

class ApiResponse {

    public $response = null;
    public $error = '';

    /**
     * ApiResponse constructor.
     */
    public function __construct() {
        $this->error = new ApiError();
    }

    /**
     * @return mixed
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response) {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getError() {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error) {
        $this->error = $error;
    }

    public function outputResponse(ApiResponse $apiResponse) {
        return response()->json($apiResponse);
    }

}

class ApiError {

    public $type = '';
    public $message = '';

    /**
     * @return mixed
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message) {
        $this->message = $message;
    }

}
