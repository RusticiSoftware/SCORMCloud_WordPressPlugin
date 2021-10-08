<?php
/**
 * AuthenticationApi
 * PHP version 5
 *
 * @category Class
 * @package  RusticiSoftware\Cloud\V2
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */

/**
 * SCORM Cloud Rest API
 *
 * REST API used for SCORM Cloud integrations.
 *
 * OpenAPI spec version: 2.0
 * 
 * Generated by: https://github.com/swagger-api/swagger-codegen.git
 * Swagger Codegen version: 2.4.12
 */

/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace RusticiSoftware\Cloud\V2\Api;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use RusticiSoftware\Cloud\V2\ApiException;
use RusticiSoftware\Cloud\V2\Configuration;
use RusticiSoftware\Cloud\V2\HeaderSelector;
use RusticiSoftware\Cloud\V2\ObjectSerializer;

/**
 * AuthenticationApi Class Doc Comment
 *
 * @category Class
 * @package  RusticiSoftware\Cloud\V2
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */
class AuthenticationApi
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var HeaderSelector
     */
    protected $headerSelector;

    /**
     * @param ClientInterface $client
     * @param Configuration   $config
     * @param HeaderSelector  $selector
     */
    public function __construct(
        ClientInterface $client = null,
        Configuration $config = null,
        HeaderSelector $selector = null
    ) {
        $this->client = $client ?: new Client();
        $this->config = $config ?: new Configuration();
        $this->headerSelector = $selector ?: new HeaderSelector();
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Operation getAppToken
     *
     * Obtain an OAuth token for scoped access to an Application
     *
     * @param  string $scope scope (required)
     * @param  int $expiration expiration (optional, default to 300)
     *
     * @throws \RusticiSoftware\Cloud\V2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \RusticiSoftware\Cloud\V2\Model\ApplicationToken
     */
    public function getAppToken($scope, $expiration = '300')
    {
        list($response) = $this->getAppTokenWithHttpInfo($scope, $expiration);
        return $response;
    }

    /**
     * Operation getAppTokenWithHttpInfo
     *
     * Obtain an OAuth token for scoped access to an Application
     *
     * @param  string $scope (required)
     * @param  int $expiration (optional, default to 300)
     *
     * @throws \RusticiSoftware\Cloud\V2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \RusticiSoftware\Cloud\V2\Model\ApplicationToken, HTTP status code, HTTP response headers (array of strings)
     */
    public function getAppTokenWithHttpInfo($scope, $expiration = '300')
    {
        $returnType = '\RusticiSoftware\Cloud\V2\Model\ApplicationToken';
        $request = $this->getAppTokenRequest($scope, $expiration);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    $response->getBody()
                );
            }

            $responseBody = $response->getBody();
            if ($returnType === '\SplFileObject') {
                $content = $responseBody; //stream goes to serializer
            } else {
                $content = $responseBody->getContents();
                if ($returnType !== 'string') {
                    $content = json_decode($content);
                }
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\RusticiSoftware\Cloud\V2\Model\ApplicationToken',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\RusticiSoftware\Cloud\V2\Model\MessageSchema',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation getAppTokenAsync
     *
     * Obtain an OAuth token for scoped access to an Application
     *
     * @param  string $scope (required)
     * @param  int $expiration (optional, default to 300)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getAppTokenAsync($scope, $expiration = '300')
    {
        return $this->getAppTokenAsyncWithHttpInfo($scope, $expiration)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getAppTokenAsyncWithHttpInfo
     *
     * Obtain an OAuth token for scoped access to an Application
     *
     * @param  string $scope (required)
     * @param  int $expiration (optional, default to 300)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getAppTokenAsyncWithHttpInfo($scope, $expiration = '300')
    {
        $returnType = '\RusticiSoftware\Cloud\V2\Model\ApplicationToken';
        $request = $this->getAppTokenRequest($scope, $expiration);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    $responseBody = $response->getBody();
                    if ($returnType === '\SplFileObject') {
                        $content = $responseBody; //stream goes to serializer
                    } else {
                        $content = $responseBody->getContents();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'getAppToken'
     *
     * @param  string $scope (required)
     * @param  int $expiration (optional, default to 300)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function getAppTokenRequest($scope, $expiration = '300')
    {
        // verify the required parameter 'scope' is set
        if ($scope === null || (is_array($scope) && count($scope) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $scope when calling getAppToken'
            );
        }
        if ($expiration !== null && $expiration > 43200) {
            throw new \InvalidArgumentException('invalid value for "$expiration" when calling AuthenticationApi.getAppToken, must be smaller than or equal to 43200.');
        }
        if ($expiration !== null && $expiration < 60) {
            throw new \InvalidArgumentException('invalid value for "$expiration" when calling AuthenticationApi.getAppToken, must be bigger than or equal to 60.');
        }


        $resourcePath = '/oauth/authenticate/application/token';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // form params
        if ($scope !== null) {
            $formParams['scope'] = ObjectSerializer::toFormValue($scope);
        }
        // form params
        if ($expiration !== null) {
            $formParams['expiration'] = ObjectSerializer::toFormValue($expiration);
        }
        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                ['application/x-www-form-urlencoded']
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            $httpBody = $_tempBody;
            
            if($headers['Content-Type'] === 'application/json') {
                // \stdClass has no __toString(), so we should encode it manually
                if ($httpBody instanceof \stdClass) {
                    $httpBody = \GuzzleHttp\json_encode($httpBody);
                }
                // array has no __toString(), so we should encode it manually
                if(is_array($httpBody)) {
                    $httpBody = \GuzzleHttp\json_encode(ObjectSerializer::sanitizeForSerialization($httpBody));
                }
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $multipartContents[] = [
                        'name' => $formParamName,
                        'contents' => $formParamValue
                    ];
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }

        // this endpoint requires HTTP basic authentication
        if ($this->config->getUsername() !== null || $this->config->getPassword() !== null) {
            $headers['Authorization'] = 'Basic ' . base64_encode($this->config->getUsername() . ":" . $this->config->getPassword());
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);
        return new Request(
            'POST',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Create http client option
     *
     * @throws \RuntimeException on file opening failure
     * @return array of http client options
     */
    protected function createHttpClientOption()
    {
        $options = [];
        if ($this->config->getDebug()) {
            $options[RequestOptions::DEBUG] = fopen($this->config->getDebugFile(), 'a');
            if (!$options[RequestOptions::DEBUG]) {
                throw new \RuntimeException('Failed to open the debug file: ' . $this->config->getDebugFile());
            }
        }

        return $options;
    }
}