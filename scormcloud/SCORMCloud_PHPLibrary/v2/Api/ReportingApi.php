<?php
/**
 * ReportingApi
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
 * ReportingApi Class Doc Comment
 *
 * @category Class
 * @package  RusticiSoftware\Cloud\V2
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */
class ReportingApi
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
     * Operation getAccountInfo
     *
     * Get all of the account information specified by the given app ID
     *
     *
     * @throws \RusticiSoftware\Cloud\V2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \RusticiSoftware\Cloud\V2\Model\ReportageAccountInfoSchema
     */
    public function getAccountInfo()
    {
        list($response) = $this->getAccountInfoWithHttpInfo();
        return $response;
    }

    /**
     * Operation getAccountInfoWithHttpInfo
     *
     * Get all of the account information specified by the given app ID
     *
     *
     * @throws \RusticiSoftware\Cloud\V2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \RusticiSoftware\Cloud\V2\Model\ReportageAccountInfoSchema, HTTP status code, HTTP response headers (array of strings)
     */
    public function getAccountInfoWithHttpInfo()
    {
        $returnType = '\RusticiSoftware\Cloud\V2\Model\ReportageAccountInfoSchema';
        $request = $this->getAccountInfoRequest();

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
                        '\RusticiSoftware\Cloud\V2\Model\ReportageAccountInfoSchema',
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
     * Operation getAccountInfoAsync
     *
     * Get all of the account information specified by the given app ID
     *
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getAccountInfoAsync()
    {
        return $this->getAccountInfoAsyncWithHttpInfo()
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getAccountInfoAsyncWithHttpInfo
     *
     * Get all of the account information specified by the given app ID
     *
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getAccountInfoAsyncWithHttpInfo()
    {
        $returnType = '\RusticiSoftware\Cloud\V2\Model\ReportageAccountInfoSchema';
        $request = $this->getAccountInfoRequest();

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
     * Create request for operation 'getAccountInfo'
     *
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function getAccountInfoRequest()
    {

        $resourcePath = '/reporting/accountInfo';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                ['application/json']
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
        // this endpoint requires OAuth (access token)
        if ($this->config->getAccessToken() !== null) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
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
            'GET',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation getReportageAuthToken
     *
     * Get a session authentication token to use when launching Reportage
     *
     * @param  string $nav_permission The navigation permissions for this Reportage session (required)
     * @param  bool $admin Grant admin privileges to this Reportage session (optional, default to false)
     *
     * @throws \RusticiSoftware\Cloud\V2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \RusticiSoftware\Cloud\V2\Model\ReportageAuthTokenSchema
     */
    public function getReportageAuthToken($nav_permission, $admin = 'false')
    {
        list($response) = $this->getReportageAuthTokenWithHttpInfo($nav_permission, $admin);
        return $response;
    }

    /**
     * Operation getReportageAuthTokenWithHttpInfo
     *
     * Get a session authentication token to use when launching Reportage
     *
     * @param  string $nav_permission The navigation permissions for this Reportage session (required)
     * @param  bool $admin Grant admin privileges to this Reportage session (optional, default to false)
     *
     * @throws \RusticiSoftware\Cloud\V2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \RusticiSoftware\Cloud\V2\Model\ReportageAuthTokenSchema, HTTP status code, HTTP response headers (array of strings)
     */
    public function getReportageAuthTokenWithHttpInfo($nav_permission, $admin = 'false')
    {
        $returnType = '\RusticiSoftware\Cloud\V2\Model\ReportageAuthTokenSchema';
        $request = $this->getReportageAuthTokenRequest($nav_permission, $admin);

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
                        '\RusticiSoftware\Cloud\V2\Model\ReportageAuthTokenSchema',
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
     * Operation getReportageAuthTokenAsync
     *
     * Get a session authentication token to use when launching Reportage
     *
     * @param  string $nav_permission The navigation permissions for this Reportage session (required)
     * @param  bool $admin Grant admin privileges to this Reportage session (optional, default to false)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getReportageAuthTokenAsync($nav_permission, $admin = 'false')
    {
        return $this->getReportageAuthTokenAsyncWithHttpInfo($nav_permission, $admin)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getReportageAuthTokenAsyncWithHttpInfo
     *
     * Get a session authentication token to use when launching Reportage
     *
     * @param  string $nav_permission The navigation permissions for this Reportage session (required)
     * @param  bool $admin Grant admin privileges to this Reportage session (optional, default to false)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getReportageAuthTokenAsyncWithHttpInfo($nav_permission, $admin = 'false')
    {
        $returnType = '\RusticiSoftware\Cloud\V2\Model\ReportageAuthTokenSchema';
        $request = $this->getReportageAuthTokenRequest($nav_permission, $admin);

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
     * Create request for operation 'getReportageAuthToken'
     *
     * @param  string $nav_permission The navigation permissions for this Reportage session (required)
     * @param  bool $admin Grant admin privileges to this Reportage session (optional, default to false)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function getReportageAuthTokenRequest($nav_permission, $admin = 'false')
    {
        // verify the required parameter 'nav_permission' is set
        if ($nav_permission === null || (is_array($nav_permission) && count($nav_permission) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $nav_permission when calling getReportageAuthToken'
            );
        }

        $resourcePath = '/reporting/reportageAuth';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        if ($nav_permission !== null) {
            $queryParams['navPermission'] = ObjectSerializer::toQueryValue($nav_permission);
        }
        // query params
        if ($admin !== null) {
            $queryParams['admin'] = ObjectSerializer::toQueryValue($admin);
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
                ['application/json']
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
        // this endpoint requires OAuth (access token)
        if ($this->config->getAccessToken() !== null) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
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
            'GET',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation getReportageLink
     *
     * Get the link to a page in Reportage with the given authentication and permissions
     *
     * @param  string $auth The reportage authentication token retrieved from a previous call to &#x60;GET reportageAuth&#x60; (required)
     * @param  string $report_url The Reportage URL to try and access (required)
     *
     * @throws \RusticiSoftware\Cloud\V2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \RusticiSoftware\Cloud\V2\Model\ReportageLinkSchema
     */
    public function getReportageLink($auth, $report_url)
    {
        list($response) = $this->getReportageLinkWithHttpInfo($auth, $report_url);
        return $response;
    }

    /**
     * Operation getReportageLinkWithHttpInfo
     *
     * Get the link to a page in Reportage with the given authentication and permissions
     *
     * @param  string $auth The reportage authentication token retrieved from a previous call to &#x60;GET reportageAuth&#x60; (required)
     * @param  string $report_url The Reportage URL to try and access (required)
     *
     * @throws \RusticiSoftware\Cloud\V2\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \RusticiSoftware\Cloud\V2\Model\ReportageLinkSchema, HTTP status code, HTTP response headers (array of strings)
     */
    public function getReportageLinkWithHttpInfo($auth, $report_url)
    {
        $returnType = '\RusticiSoftware\Cloud\V2\Model\ReportageLinkSchema';
        $request = $this->getReportageLinkRequest($auth, $report_url);

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
                        '\RusticiSoftware\Cloud\V2\Model\ReportageLinkSchema',
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
     * Operation getReportageLinkAsync
     *
     * Get the link to a page in Reportage with the given authentication and permissions
     *
     * @param  string $auth The reportage authentication token retrieved from a previous call to &#x60;GET reportageAuth&#x60; (required)
     * @param  string $report_url The Reportage URL to try and access (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getReportageLinkAsync($auth, $report_url)
    {
        return $this->getReportageLinkAsyncWithHttpInfo($auth, $report_url)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getReportageLinkAsyncWithHttpInfo
     *
     * Get the link to a page in Reportage with the given authentication and permissions
     *
     * @param  string $auth The reportage authentication token retrieved from a previous call to &#x60;GET reportageAuth&#x60; (required)
     * @param  string $report_url The Reportage URL to try and access (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getReportageLinkAsyncWithHttpInfo($auth, $report_url)
    {
        $returnType = '\RusticiSoftware\Cloud\V2\Model\ReportageLinkSchema';
        $request = $this->getReportageLinkRequest($auth, $report_url);

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
     * Create request for operation 'getReportageLink'
     *
     * @param  string $auth The reportage authentication token retrieved from a previous call to &#x60;GET reportageAuth&#x60; (required)
     * @param  string $report_url The Reportage URL to try and access (required)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function getReportageLinkRequest($auth, $report_url)
    {
        // verify the required parameter 'auth' is set
        if ($auth === null || (is_array($auth) && count($auth) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $auth when calling getReportageLink'
            );
        }
        // verify the required parameter 'report_url' is set
        if ($report_url === null || (is_array($report_url) && count($report_url) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $report_url when calling getReportageLink'
            );
        }

        $resourcePath = '/reporting/reportageLink';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        if ($auth !== null) {
            $queryParams['auth'] = ObjectSerializer::toQueryValue($auth);
        }
        // query params
        if ($report_url !== null) {
            $queryParams['reportUrl'] = ObjectSerializer::toQueryValue($report_url);
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
                ['application/json']
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
        // this endpoint requires OAuth (access token)
        if ($this->config->getAccessToken() !== null) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
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
            'GET',
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