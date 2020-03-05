<?php
/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
namespace YouMi\Aliyun\ApiGateway\Http;

class HttpResponse
{
    private $content;
    private $body;
    private $header;
    private $requestId;
    private $errorMessage;
    private $contentType;
    private $httpStatusCode;

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function setHeader($header)
    {
        $this->header = $header;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getRequestId()
    {
        return $this->requestId;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    public function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode  = $httpStatusCode;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setContentType($contentType)
    {
        $this->contentType  = $contentType;
    }

    public function getSuccess()
    {
        if(200 <= $this->httpStatusCode && 300 > $this->httpStatusCode)
        {
            return true;
        }
        return false;
    }

    /**
     *提取header中的requestId和errorMessage
     */
    public function extractKey() {
        if ($this->header && is_array($this->header)) {
            foreach ($this->header as $key => $value) {
                if($key == "x-ca-request-id")
                {
                    $this->requestId = $value;
                }elseif($key == "x-ca-error-message")
                {
                    d($value);
                    $this->errorMessage = $value;
                }
            }
        }
    }
}
