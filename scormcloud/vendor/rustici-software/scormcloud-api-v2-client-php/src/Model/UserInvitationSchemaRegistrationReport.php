<?php
/**
 * UserInvitationSchemaRegistrationReport
 *
 * PHP version 7
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

namespace RusticiSoftware\Cloud\V2\Model;

use \ArrayAccess;
use \RusticiSoftware\Cloud\V2\ObjectSerializer;

/**
 * UserInvitationSchemaRegistrationReport Class Doc Comment
 *
 * @category Class
 * @description An high level overview of information about the registration of the user to the invitation.
 * @package  RusticiSoftware\Cloud\V2
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */
class UserInvitationSchemaRegistrationReport implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'UserInvitationSchema_registrationReport';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'complete' => '\RusticiSoftware\Cloud\V2\Model\RegistrationCompletion',
        'success' => '\RusticiSoftware\Cloud\V2\Model\RegistrationSuccess',
        'total_seconds_tracked' => 'double',
        'score' => '\RusticiSoftware\Cloud\V2\Model\ScoreSchema'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'complete' => null,
        'success' => null,
        'total_seconds_tracked' => 'double',
        'score' => null
    ];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerTypes()
    {
        return self::$swaggerTypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerFormats()
    {
        return self::$swaggerFormats;
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'complete' => 'complete',
        'success' => 'success',
        'total_seconds_tracked' => 'totalSecondsTracked',
        'score' => 'score'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'complete' => 'setComplete',
        'success' => 'setSuccess',
        'total_seconds_tracked' => 'setTotalSecondsTracked',
        'score' => 'setScore'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'complete' => 'getComplete',
        'success' => 'getSuccess',
        'total_seconds_tracked' => 'getTotalSecondsTracked',
        'score' => 'getScore'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$swaggerModelName;
    }

    

    

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['complete'] = isset($data['complete']) ? $data['complete'] : null;
        $this->container['success'] = isset($data['success']) ? $data['success'] : null;
        $this->container['total_seconds_tracked'] = isset($data['total_seconds_tracked']) ? $data['total_seconds_tracked'] : null;
        $this->container['score'] = isset($data['score']) ? $data['score'] : null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets complete
     *
     * @return \RusticiSoftware\Cloud\V2\Model\RegistrationCompletion
     */
    public function getComplete()
    {
        return $this->container['complete'];
    }

    /**
     * Sets complete
     *
     * @param \RusticiSoftware\Cloud\V2\Model\RegistrationCompletion $complete complete
     *
     * @return $this
     */
    public function setComplete($complete)
    {
        $this->container['complete'] = $complete;

        return $this;
    }

    /**
     * Gets success
     *
     * @return \RusticiSoftware\Cloud\V2\Model\RegistrationSuccess
     */
    public function getSuccess()
    {
        return $this->container['success'];
    }

    /**
     * Sets success
     *
     * @param \RusticiSoftware\Cloud\V2\Model\RegistrationSuccess $success success
     *
     * @return $this
     */
    public function setSuccess($success)
    {
        $this->container['success'] = $success;

        return $this;
    }

    /**
     * Gets total_seconds_tracked
     *
     * @return double
     */
    public function getTotalSecondsTracked()
    {
        return $this->container['total_seconds_tracked'];
    }

    /**
     * Sets total_seconds_tracked
     *
     * @param double $total_seconds_tracked total_seconds_tracked
     *
     * @return $this
     */
    public function setTotalSecondsTracked($total_seconds_tracked)
    {
        $this->container['total_seconds_tracked'] = $total_seconds_tracked;

        return $this;
    }

    /**
     * Gets score
     *
     * @return \RusticiSoftware\Cloud\V2\Model\ScoreSchema
     */
    public function getScore()
    {
        return $this->container['score'];
    }

    /**
     * Sets score
     *
     * @param \RusticiSoftware\Cloud\V2\Model\ScoreSchema $score score
     *
     * @return $this
     */
    public function setScore($score)
    {
        $this->container['score'] = $score;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Sets value based on offset.
     *
     * @param integer $offset Offset
     * @param mixed   $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        if (defined('JSON_PRETTY_PRINT')) { // use JSON pretty print
            return json_encode(
                ObjectSerializer::sanitizeForSerialization($this),
                JSON_PRETTY_PRINT
            );
        }

        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


