<?php

/**
 * Created by PhpStorm.
 * User: Manoel
 * Date: 04-Jun-15
 * Time: 7:43 PM
 */
namespace BoletoBundle\Classes;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Monolog\Logger;



class BoletoConnectionClient
{

    const DateTimeFormat_ISO8601 = 'Y-m-d\TH:i:s.uP';

    /** @var  $httpClient Client */
    private $httpClient;

    /** @var $logger \Monolog\Logger */
    private $logger;

    /** @var  Response */
    private $response;

    /** @var $error_msg string */
    private $error_msg;

    private $token;


    public function getErrorMsg()
    {
        return $this->error_msg;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function  getToken()
    {
        return $this->token;
    }


    function __construct($boleto_url, $boleto_token, Logger $logger)
    {
        $this->logger = $logger; //store logger instance
        $this->error_msg = null;

        $this->token = $boleto_token;

        //create new guzzle http client
        $this->httpClient = new Client();

        //set base url
        $this->httpClient->setBaseUrl($boleto_url);

        //set auth header
        //$this->httpClient->setDefaultOption('auth', array(null, '', 'Basic'));

        //set accept response as json
        $this->httpClient->setDefaultOption('Accept', "application/json");
    }


    /**
     * This method will execute a get request within BoletoFacil API
     * If the request is successful, the method will return the received response in array format (json decoded)
     * If it returns false, it means that the request is unsuccessful and it may be necessary to check the $response variable to check the status code and the received error message
     *
     * @param $url - the complement of the base url for the get request
     *
     * @return false|array
     */
    public function get($url)
    {
        //erase error msg
        $this->error_msg=null;

        //log the request
        $this->logger->info("Executing GET request to: " . $this->httpClient->getBaseUrl() . $url);

        try
        {
            //execute the request
            $this->response = $this->httpClient->get($url)->send();

            // sugestão do Murilo - esta função receberia um array. Aqui, faria um for no array fazendo o set para cada elemento do array
            //$request = $this->httpClient->get($url);
            //$request->getQuery()->set('token', 'put token here');
            //$request->getQuery()->set('description', 'put description here');

            //execute the request
            //$this->response = $request->send();
        }
        catch(\Exception $e)
        {
            //log the error
            $this->logger->error("Error while making a GET request to BoletoFacil with the message: ".$e->getMessage());

            //get error msg
            $error_msg = json_decode((string) $this->response->getBody(), true);

            if (JSON_ERROR_NONE == json_last_error())
                $this->logger->error("Error msg from BoletoFacil: ".print_r($error_msg,true));

            //set the error msg
            $this->error_msg = $e->getMessage();

            return false;
        }

        $this->logger->info("GET request status code: ". $this->response->getStatusCode());

        //check if the request was well done
        if($this->response->getStatusCode() == 200)
        {
            //process the received response
            return $this->processResponse($this->response);

        }//end of if
        else
        {   //the request returned an error

            //set the error msg
            $this->error_msg = "Error status code received while making a GET request to BoletoFacil with the message: ".$this->response->getStatusCode().". With body: ".$this->response->getBody();

            //log the error
            $this->logger->error($this->error_msg);

            return false;

        }//end of else

    }//end of function get


    public function post($url, $data_array)
    {
        //erase error msg
        $this->error_msg = null;

        //encode the data array to json
        $json_data = json_encode($data_array);

        //log the request
        $this->logger->info("Executing POST request to: " . $this->httpClient->getBaseUrl() . $url. " With json data: ".$json_data);

        try
        {
            //execute the request with the json body
            $request = $this->httpClient->post($url, null, json_encode($data_array));

            $request->setHeader('Content-Type', 'application/json');

            $request->send();

        }


        catch(\Exception $e)
        {
            $this->response = $request->getResponse();

            //log the error
            $this->logger->error("Error while making a POST request to BoletoFacil with the message: ".$e->getMessage());

            //get error msg
            $error_msg = json_decode((string) $this->response->getBody(), true);

            if (JSON_ERROR_NONE == json_last_error())
                $this->logger->error("Error msg from BoletoFacil: ".print_r($error_msg,true));


            //set the error msg
            $this->error_msg = $e->getMessage();

            return false;
        }

        $this->response = $request->getResponse();

        $this->logger->info("POST request status code: ". $this->response->getStatusCode());

        //check if the request was well done

        if($this->response->getStatusCode() == 200)
        {
            //process the received response
            return $this->processResponse($this->response);

        }//end of if
        else
        {   //the request returned an error

            //set the error msg
            $this->error_msg = "Error status code received while making a GET request to BoletoFacil with the message: ".$this->response->getStatusCode().". With body: ".$this->response->getBody();

            //log the error
            $this->logger->error($this->error_msg);

            return false;

        }//end of else

    }//end of function get



    public function put($url, $data_array)
    {
        //erase error msg
        $this->error_msg=null;

        //encode the data array to json
        $json_data = json_encode($data_array);

        //log the request
        $this->logger->info("Executing PUT request to: " . $this->httpClient->getBaseUrl() . $url. " With json data: ".$json_data);

        try
        {
            //execute the request with the json body
            $request = $this->httpClient->put($url, null, json_encode($data_array));

            $request->setHeader('Content-Type', 'application/json');

            $this->response = $request->send();
        }
        catch(\Exception $e)
        {
            //log the error
            $this->logger->error("Error while making a PUT request to BoletoFacil with the message: ".$e->getMessage());

            //set the error msg
            $this->error_msg = $e->getMessage();

            return false;
        }

        $this->logger->info("PUT request status code: ". $this->response->getStatusCode());

        //check if the request was well done
        if($this->response->getStatusCode() == 200)
        {
            //process the received response
            return $this->processResponse($this->response);

        }//end of if
        else
        {   //the request returned an error

            //set the error msg
            $this->error_msg = "Error status code received while making a GET request to BoletoFacil with the message: ".$this->response->getStatusCode().". With body: ".$this->response->getBody();

            //log the error
            $this->logger->error($this->error_msg);

            return false;

        }//end of else

    }//end of function get


    private function processResponse(Response $response)
    {
        //check if the message has received json data
        if(strpos($response->getContentType(), "application/json")>-1)
        {
            try
            {
                //we received an json response, let's return the json decoded version of the received data
                $data = $response->json();
            }
            catch(\Exception $e)
            {
                //error while decoding json data
                $this->error_msg = "Error while decoding data from BoletoFacil request with exception: ".$e->getMessage();

                return false;
            }//end of catch

            //return the decoded data
            return $data;
        }
        else
        {
            //we didnt receive a json response as expected
            $this->error_msg = "Error while making a HTTP request to BoletoFacil: We didn't receive an JSON content as expected from url: ".$response->getEffectiveUrl();

            return false;
        }//end of else

    }//end of function processResponse

}

