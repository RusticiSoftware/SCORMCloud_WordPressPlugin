<?php

/* Software License Agreement (BSD License)
 *
 * Copyright (c) 2010-2011, Rustici Software, LLC
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the <organization> nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Rustici Software, LLC BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * @version $Id$
 * @author  Brian Rogers <brian.rogers@scorm.com>
 * @license http://opensource.org/licenses/lgpl-license.php
 *          GNU Lesser General Public License, Version 2.1
 * @package RusticiSoftware.ScormEngine.Cloud
 */

class ScormEngineService
{

    private $_configuration = null;
    private $_courseService = null;
    private $_registrationService = null;
    private $_uploadService = null;
    private $_ftpService = null;
    private $_serviceRequest = null;
    private $_taggingService = null;
    private $_reportingService = null;
    private $_debugService = null;
    private $_dispatchService = null;
    private $_invitationService = null;
    private $_lrsAccountService = null;
    private $_learnerService = null;

    public function __construct($scormEngineServiceUrl, $appId, $securityKey, $originString, $proxy = null)
    {
        $config = new RusticiSoftware\Cloud\V2\Configuration();
        $config->setUsername($appId);
        $config->setPassword($securityKey);
        $config->setHost($scormEngineServiceUrl);
        $this->_configuration = $config;
        $this->_courseService = new RusticiSoftware\Cloud\V2\Api\CourseApi(null, $this->_configuration, null);
        $this->_registrationService = new RusticiSoftware\Cloud\V2\Api\RegistrationApi(null, $this->_configuration, null);    
        $this->_reportingService = new RusticiSoftware\Cloud\V2\Api\ReportingApi(null, $this->_configuration, null);
        $this->_debugService = new RusticiSoftware\Cloud\V2\Api\PingApi(null, $this->_configuration, null);
        $this->_dispatchService = new RusticiSoftware\Cloud\V2\Api\DispatchApi(null, $this->_configuration, null);
        $this->_invitationService = new RusticiSoftware\Cloud\V2\Api\InvitationsApi(null, $this->_configuration, null);
        $this->_lrsAccountService = new RusticiSoftware\Cloud\V2\Api\XapiApi(null, $this->_configuration, null);
        $this->_learnerService = new RusticiSoftware\Cloud\V2\Api\LearnerApi(null, $this->_configuration, null);
    }

    public function isValidAccount()
    {
        $appId = $this->getAppId();
        $key = $this->getSecurityKey();
        $url = $this->getScormEngineServiceUrl();
        $origin = $this->getOriginString();
        if (empty($appId) || empty($key) || empty($url) || empty($origin)) {
            return false;
        }
        
        return $this->_debugService->pingAppId();
    }

    /**
     * <summary>
     * Contains all SCORM Engine Package-level (i.e., course) functionality.
     * </summary>
     */
    public function getCourseService()
    {
        return $this->_courseService;
    }

    /**
     * <summary>
     * Contains all SCORM Engine Package-level (i.e., course) functionality.
     * </summary>
     */
    public function getRegistrationService()
    {
        return $this->_registrationService;
    }

    /**
     * <summary>
     * Contains all SCORM Engine Upload/File Management functionality.
     * </summary>
     */
    public function getUploadService()
    {
        return $this->_uploadService;
    }

    /**
     * <summary>
     * Contains all SCORM Engine Reportage functionality.
     * </summary>
     */
    public function getReportingService()
    {
        return $this->_reportingService;
    }

    /**
     * <summary>
     * Contains all SCORM Engine FTP Management functionality.
     * </summary>
     */
    public function getFtpService()
    {
        return $this->_ftpService;
    }

    /**
     * <summary>
     * Contains SCORM Engine tagging functionality.
     * </summary>
     */
    public function getTaggingService()
    {
        return $this->_taggingService;
    }

    /**
     * <summary>
     * Contains SCORM Engine account info retrieval functionality.
     * </summary>
     */
    public function getAccountService()
    {
        return $this->_accountService;
    }

    /**
     * <summary>
     * Contains SCORM Engine debug functionality.
     * </summary>
     */
    public function getDebugService()
    {
        return $this->_debugService;
    }

    /**
     * <summary>
     * Contains SCORM Engine dispatch functionality.
     * </summary>
     */
    public function getDispatchService()
    {
        return $this->_dispatchService;
    }

    /**
     * <summary>
     * Contains SCORM Engine Invitation functionality.
     * </summary>
     */
    public function getInvitationService()
    {
        return $this->_invitationService;
    }

    /**
     * <summary>
     * Contains SCORM Engine Activity provider functionality.
     * </summary>
     */
    public function getLrsAccountService()
    {
        return $this->_lrsAccountService;
    }

    /**
     * <summary>
     * Contains SCORM Engine Learner functionality.
     * </summary>
     */
    public function getLearnerService()
    {
        return $this->_learnerService;
    }


    /**
     * <summary>
     * The Application ID obtained by registering with the SCORM Engine Service
     * </summary>
     */
    public function getAppId()
    {
        return $this->_configuration->getUsername();
    }

    /**
     * <summary>
     * The security key (password) linked to the Application ID
     * </summary>
     */
    public function getSecurityKey()
    {
        return $this->_configuration->getPassword();
    }

    /**
     * <summary>
     * URL to the service, ex: https://cloud.scorm.com/api/v2
     * </summary>
     */
    public function getScormEngineServiceUrl()
    {
        return $this->_configuration->getHost();
    }

    public function getOriginString()
    {
        return "rusticisoftware.wordpress.2.0.x";
    }

    /**
     * <summary>
     * CreateNewRequest
     * </summary>
     */
    public function CreateNewRequest()
    {
        return new ServiceRequest($this->_configuration);
    }
}
