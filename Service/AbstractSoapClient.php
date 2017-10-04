<?php

namespace EXS\CampaignerBundle\Service;

use EXS\CampaignerBundle\Model\ReportResult;

/**
 * Class AbstractSoapClient
 *
 * @package EXS\CampaignerBundle\Service
 */
abstract class AbstractSoapClient
{
    /**
     * @var \SoapClient
     */
    protected $client;

    /**
     * @var array
     */
    protected $authenticationNode;

    /**
     * @var string
     */
    protected $xsdPath;

    /**
     * AbstractSoapClient constructor.
     *
     * @param string $wsdlUrl
     * @param string $username
     * @param string $password
     * @param array  $clientOptions
     */
    public function __construct($wsdlUrl, $username, $password, array $clientOptions = [])
    {
        $requiredOptions = [
            'encoding' => 'UTF-8',
            'exceptions' => false, /* Bad calls won't throw an exception but returns a SoapFault object. */
            'soap_version' => SOAP_1_1,
            'trace' => true, /* So we can have response's headers. */
            'classmap' => [
                'ReportResult' => ReportResult::class,
            ],
            'typemap' => [
                [
                    'type_ns' => 'https://ws.campaigner.com/2013/01',
                    'type_name' => 'ReportResult',
                    'from_xml' => [$this, 'createReportResultFromXml'],
                ],
            ],
        ];

        $this->client = new \SoapClient($wsdlUrl, array_merge($clientOptions, $requiredOptions));

        $this->authenticationNode = [
            'authentication' => [
                'Username' => $username,
                'Password' => $password,
            ],
        ];
    }

    /**
     * @param string $xml
     *
     * @return ReportResult
     */
    public function createReportResultFromXml($xml)
    {
        $xmlElement = new \SimpleXMLElement($xml);

        $reportResultAttributes = (array)$xmlElement->attributes();
        $reportResultAttributes = current($reportResultAttributes);

        foreach ($xmlElement->getDocNamespaces() as $namespace) {
            $xmlElement->registerXPathNamespace('c', $namespace);
        }

        $attributeValues = [];
        $attributeTags = $xmlElement->xpath('//c:Attribute');

        foreach ($attributeTags as $attributeTag) {
            $attributeAttributes = (array)$attributeTag->attributes();
            $attributeAttributes = current($attributeAttributes);

            $attributeValues[$attributeAttributes['Id']] = (string)$attributeTag;
        }

        return new ReportResult($reportResultAttributes, $attributeValues);
    }

    /**
     * Proxy method to SoapClient::__soapCall() that format the request body has expected by the web service.
     * Will return :
     *  - an associative array of the result
     *  - true if result is empty but there were no error
     *  - false if result is empty but there an error
     *  - null if an error occurred or the result as an unknown form
     *
     * @param string $methodName
     * @param array  $parameters
     *
     * @return array|bool|null
     */
    protected function callMethod($methodName, array $parameters = [])
    {
        $responseHeaders = [];

        $result = $this->client->__soapCall(
            (string)$methodName,
            [
                (string)$methodName => array_merge(
                    $this->authenticationNode,
                    $parameters
                ),
            ],
            [],
            null,
            $responseHeaders
        );

        if ($result instanceof \SoapFault) {
            return null;
        }

        /* Transform a \stdClass object to an associative array. */
        $result = json_decode(json_encode($result), true);

        if (false === empty($result)) {
            return $result;
        }

        /* Transform a \stdClass object to an associative array. */
        $responseHeaders = json_decode(json_encode($responseHeaders), true);

        if (
            (true === isset($responseHeaders['ResponseHeader']))
            && (true === isset($responseHeaders['ResponseHeader']['ErrorFlag']))
        ) {
            return !(bool)$responseHeaders['ResponseHeader']['ErrorFlag'];
        }

        return null;
    }

    /**
     * Set xsd file's path.
     *
     * @param $xsdPath
     */
    public function setXsdPath($xsdPath)
    {
        $this->xsdPath = $xsdPath;
    }

    /**
     * Validate query against the xsd.
     *
     * @param string $query
     *
     * @return bool
     */
    protected function isValidXmlContactQuery($query)
    {
        $queryXml = new \DOMDocument();

        try {
            $queryXml->loadXML($query);

            return $queryXml->schemaValidate($this->xsdPath);
        } catch (\Exception $e) {
            return false;
        }
    }
}
