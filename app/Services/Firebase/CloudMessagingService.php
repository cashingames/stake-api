<?php

namespace App\Services\Firebase;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

/**
 * Interact with Firebase Cloud Messaging (FCM)
 */
class CloudMessagingService{

    /**
     * Basic notification to be used across all platforms.
     * 
     * This will contain title and body to be displayed in the notification tray of the device.
     *
     * @var array
     */
    private $notification;

    /**
     * Data message
     * 
     * Customer key-value pair of data to by handled within the app
     * 
     * @var array
     */
    private $data;

    /**
     * Recipient of the message
     * 
     * Topic name or device token to send notification to
     * 
     * @var string
     */

    private $to;

    /**
     * FCM Server key for authentication
     */
    private $serverKey;

    private $endpoint = "https://fcm.googleapis.com/fcm/send";

    private $response;

    public function __construct(string $serverKey)
    {
        $this->serverKey = $serverKey;
    }

    /**
     * Set serverKey
     * 
     * Overwrites serverKey set in constructor
     * 
     * @param string $serverKey
     * @return $this
     */
    public function setServerKey($serverKey){
        $this->serverKey = $serverKey;
        return $this;
    }

    public function getServerKey(){
        return $this->serverKey;
    }

    /**
     * Set notification object
     * 
     * @param array $data
     * @return $this
     */
    public function setNotification(array $data){
        if (!array_key_exists('title', $data)){
            throw new \Exception("The notification data must contain a `title` key", 1);
        }
        if (!array_key_exists('body', $data)){
            throw new \Exception("The notification data must contain a `body` key", 1);
        }

        $this->notification = $data;
        return $this;
    }

    /**
     * Set custom data payload
     * 
     * @param array $data
     * @return $this
     */
    public function setData(array $data){
        $this->data = $data;
        return $this;
    }

    /**
     * Set recipient of notification
     * 
     * @param string $to
     * @return $this
     */
    public function setTo(string $to){
        $this->to = $to;
        return $this;
    }

    /**
     * Send out notification to recipient
     * 
     * If argument $data is not null, the method assumes you want to overwrite the data and notification properties
     * 
     * @param array|mixed $data
     */
    public function send(array $data=null){
        if (isset($data) && array_key_exists('notification', $data)){
            $this->setNotification($data['notification']);
        }
        if (isset($data) && array_key_exists('data', $data)){
            $this->setData($data['data']);
        }
        $client = new Client([
            'headers' => [
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);

        try {
            $response = $client->request("POST", $this->endpoint, [
                'json' => [
                    'notification' => $this->notification,
                    'data' => $this->data,
                    'to' => $this->to
                ]
            ]);

            $this->setResponse(json_decode($response->getBody()));
            return $this->getResponse();
        } catch (ConnectException $th) {
            throw $th;
        }
    }
    
    private function setResponse($response){
        $this->response = $response;
    }

    /**
     * Get response data after sending notification
     * 
     * This method should only be called after `send` method has been called
     */
    public function getResponse(){
        if (!isset($this->response)){
            throw new Exception("Unable to get response, ensure that you have called the `send` method");
        }
        return $this->response;
    }
}