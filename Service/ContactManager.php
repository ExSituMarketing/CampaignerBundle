<?php

namespace EXS\CampaignerBundle\Service;

use EXS\CampaignerBundle\Model\CustomAttribute;
use EXS\CampaignerBundle\Model\NullableString;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class SoapClient
 *
 * @package EXS\CampaignerBundle\Service
 */
class ContactManager extends AbstractSoapClient
{
    /**
     * This web method adds a custom contact attribute, or updates a default or custom contact attribute for all contacts.
     *
     * @param int    $attributeId
     * @param string $attributeName
     * @param string $attributeType
     * @param string $defaultValue
     * @param bool   $clearDefault
     *
     * @return mixed|null
     */
    public function CreateUpdateAttribute(
        $attributeId,
        $attributeName,
        $attributeType,
        $defaultValue = '',
        $clearDefault = true
    ) {
        $parameters = [
            'attributeId' => $attributeId,
            'attributeName' => $attributeName,
            'attributeType' => $attributeType,
            'clearDefault' => $clearDefault,
        ];

        if (false === $clearDefault) {
            $parameters['defaultValue'] = (string)$defaultValue;
        }

        return $this->callMethod(__FUNCTION__, $parameters);
    }

    /**
     * This web method deletes an existing custom attribute.
     *
     * @param int $id
     *
     * @return mixed|null
     */
    public function DeleteAttribute($id)
    {
        return $this->callMethod(__FUNCTION__, [
            'id' => $id,
        ]);
    }

    /**
     * This web method deletes one or more specified contacts.
     *
     * @param array $contactKeys
     *
     * @return mixed|null
     */
    public function DeleteContacts(array $contactKeys)
    {
        return $this->callMethod(__FUNCTION__, [
            'contactKeys' => $this->validateContactKeys($contactKeys),
        ]);
    }

    /**
     * This web method returns various untyped reports based on the contacts obtained using the
     * RunReport web method, as described in "RunReport Web Method".
     *
     * @param string $reportTicketId
     * @param int    $fromRow
     * @param int    $toRow
     * @param string $reportType
     *
     * @return mixed|null
     */
    public function DownloadReport($reportTicketId, $fromRow = 1, $toRow = 100, $reportType = 'rpt_Contact_Details')
    {
        $reportTypes = [
            'rpt_Detailed_Contact_Results_by_Campaign',
            'rpt_Summary_Contact_Results_by_Campaign',
            'rpt_Summary_Campaign_Results',
            'rpt_Summary_Campaign_Results_by_Domain',
            'rpt_Contact_Attributes',
            'rpt_Contact_Details',
            'rpt_Contact_Group_Membership',
            'rpt_Groups',
            'rpt_Tracked_Links',
        ];

        if (null === in_array($reportType, $reportTypes)) {
            throw new InvalidConfigurationException(sprintf('Invalid reportType value "%s".', $reportType));
        }

        $parameters = [
            'reportTicketId' => $reportTicketId,
            'fromRow' => $fromRow,
            'toRow' => $toRow,
            'reportType' => $reportType,
        ];

        return $this->callMethod(__FUNCTION__, $parameters);
    }

    /**
     * This web method returns information about attributes for up to 1000 specified contacts.
     *
     * @param array $contactKeys
     * @param bool  $includeStaticAttributes
     * @param bool  $includeCustomAttributes
     * @param bool  $includeSystemAttributes
     * @param bool  $includeGroupMembershipsAttributes
     *
     * @return mixed|null
     */
    public function GetContacts(
        array $contactKeys,
        $includeStaticAttributes = false,
        $includeCustomAttributes = false,
        $includeSystemAttributes = false,
        $includeGroupMembershipsAttributes = false
    ) {
        return $this->callMethod(__FUNCTION__, [
            'contactKeys' => $this->validateContactKeys($contactKeys),
            'contactInformationFilter' => [
                'IncludeStaticAttributes' => (bool)$includeStaticAttributes,
                'IncludeCustomAttributes' => (bool)$includeCustomAttributes,
                'IncludeSystemAttributes' => (bool)$includeSystemAttributes,
                'IncludeGroupMembershipsAttributes' => (bool)$includeGroupMembershipsAttributes,
            ]
        ]);
    }

    /**
     * This web method synchronously adds contacts and defines their information, or updates information for existing contacts for up to 1000 contacts.
     *
     * @param bool  $updateExistingContacts
     * @param bool  $triggerWorkflow
     * @param array $contacts
     * @param array $globalAddToGroup
     * @param array $globalRemoveFromGroup
     *
     * @return mixed|null
     */
    public function ImmediateUpload(
        $updateExistingContacts,
        $triggerWorkflow,
        array $contacts,
        array $globalAddToGroup = null,
        array $globalRemoveFromGroup = null
    ) {
        $parameters = [
            'UpdateExistingContacts' => $updateExistingContacts,
            'TriggerWorkflow' => $triggerWorkflow,
            'contacts' => $this->validateContactData($contacts),
        ];

        if (null !== $globalAddToGroup) {
            $parameters['globalAddToGroup'] = $globalAddToGroup;
        }

        if (null !== $globalRemoveFromGroup) {
            $parameters['globalRemoveFromGroup'] = $globalRemoveFromGroup;
        }

        return $this->callMethod(__FUNCTION__, $parameters);
    }

    /**
     * This web method lists all contact attributes and their properties (such as the identifier and type).
     *
     * @param bool $includeAllDefaultAttributes
     * @param bool $includeAllCustomAttributes
     * @param bool $includeAllSystemAttributes
     *
     * @return mixed|null
     */
    public function ListAttributes(
        $includeAllDefaultAttributes = true,
        $includeAllCustomAttributes = true,
        $includeAllSystemAttributes = true
    ) {
        return $this->callMethod(__FUNCTION__, [
            'IncludeAllDefaultAttributes' => $includeAllDefaultAttributes,
            'IncludeAllCustomAttributes' => $includeAllCustomAttributes,
            'IncludeAllSystemAttributes' => $includeAllSystemAttributes,
        ]);
    }

    /**
     * This web method lists all contact fields and their properties (such as the identifier and type).
     *
     * @param bool $includeAllDefaultAttributes
     * @param bool $includeAllCustomAttributes
     * @param bool $includeAllSystemAttributes
     *
     * @return mixed|null
     */
    public function ListContactFields(
        $includeAllDefaultAttributes = true,
        $includeAllCustomAttributes = true,
        $includeAllSystemAttributes = true
    ) {
        return $this->callMethod(__FUNCTION__, [
            'IncludeAllDefaultAttributes' => $includeAllDefaultAttributes,
            'IncludeAllCustomAttributes' => $includeAllCustomAttributes,
            'IncludeAllSystemAttributes' => $includeAllSystemAttributes,
        ]);
    }

    /**
     * This web method changes one contact's status from Unsubscribed to Subscribed, HardBounce, SoftBounce, or Pending.
     *
     * @param int    $contactId
     * @param string $contactUniqueIdentifier
     * @param string $status
     *
     * @return mixed|null
     */
    public function ResubscribeContact($contactId, $contactUniqueIdentifier, $status)
    {
        return $this->callMethod(__FUNCTION__, [
            'contactKey' => $this->validateContactKeys([
                'ContactId' => $contactId,
                'ContactUniqueIdentifier' => $contactUniqueIdentifier,
            ]),
            'status' => $this->validateStatus($status),
        ]);
    }

    /**
     * This web method processes an XML query string to obtain rows of contact information, which are then stored on Campaigner®.
     * The web method also returns a ticket ID for the query request and the number of rows obtained.
     *
     * @param $xmlContactQuery
     *
     * @return mixed|null
     */
    public function RunReport($xmlContactQuery)
    {
        if (false === $this->isValidXmlContactQuery($xmlContactQuery)) {
            return null;
        }

        return $this->callMethod(__FUNCTION__, [
            'xmlContactQuery' => $xmlContactQuery,
        ]);
    }

    /**
     * Like ImmediateUpload, the UploadMassContacts web method uploads contact information for multiple contacts at the same time to Campaigner®,
     * and performs additional processing, such as changing group memberships for contacts being uploaded.
     *
     * @param bool $updateExistingContacts
     * @param bool  $triggerWorkflow
     * @param array $contacts
     * @param array $globalAddToGroup
     * @param array $globalRemoveFromGroup
     *
     * @return mixed
     */
    public function UploadMassContacts(
        $updateExistingContacts,
        $triggerWorkflow,
        array $contacts,
        array $globalAddToGroup = null,
        array $globalRemoveFromGroup = null
    ) {
        $parameters = [
            'UpdateExistingContacts' => $updateExistingContacts,
            'TriggerWorkflow' => $triggerWorkflow,
            'contacts' => $this->validateContactData($contacts),
        ];

        if (null !== $globalAddToGroup) {
            $parameters['globalAddToGroup'] = $globalAddToGroup;
        }

        if (null !== $globalRemoveFromGroup) {
            $parameters['globalRemoveFromGroup'] = $globalRemoveFromGroup;
        }

        return $this->callMethod(__FUNCTION__, $parameters);
    }

    /**
     * Formats "ContactUniqueIdentifier" request node.
     *
     * @param array $contactKeys
     *
     * @return array
     */
    private function validateContactKeys(array $contactKeys)
    {
        $contactKeys = array_map(function ($contactKey) {
            return $this->validateContactKey($contactKey);
        }, $contactKeys);

        return $contactKeys;
    }

    /**
     * @param array $contactKey
     *
     * @return array
     */
    private function validateContactKey(array $contactKey)
    {
        if (false === isset($contactData['ContactUniqueIdentifier'])) {
            throw new InvalidConfigurationException('Missing "ContactUniqueIdentifier" parameter.');
        }

        if (isset($contactKey['ContactId'])) {
            return [
                'ContactId' => (int)$contactKey['ContactId'],
                'ContactUniqueIdentifier' => (string)$contactKey['ContactUniqueIdentifier'],
            ];
        }

        return [
            'ContactId' => 0,
            'ContactUniqueIdentifier' => (string)$contactKey['ContactUniqueIdentifier'],
        ];
    }

    /**
     *
     *
     * @param array $contactData
     *
     * @return array
     */
    private function validateContactData(array $contactData)
    {
        $validatedContactData = [];

        if (false === isset($contactData['ContactKey'])) {
            throw new InvalidConfigurationException('Missing "ContactKey" parameter.');
        }

        $validatedContactData['ContactKey'] = $this->validateContactKey($contactData['ContactKey']);

        $validatedContactData['EmailAddress'] = new NullableString(
            !isset($contactData['EmailAddress']),
            (string)$contactData['EmailAddress']
        );

        $validatedContactData['FirstName'] = new NullableString(
            !isset($contactData['FirstName']),
            (string)$contactData['FirstName']
        );

        $validatedContactData['LastName'] = new NullableString(
            !isset($contactData['LastName']),
            (string)$contactData['LastName']
        );

        $validatedContactData['PhoneNumber'] = new NullableString(
            !isset($contactData['PhoneNumber']),
            (string)$contactData['PhoneNumber']
        );

        $validatedContactData['Fax'] = new NullableString(
            !isset($contactData['Fax']),
            (string)$contactData['Fax']
        );

        $validatedContactData['Status'] = isset($contactData['Status']) ? $this->validateStatus($contactData['Status']) : null;

        $validatedContactData['MailFormat'] = isset($contactData['MailFormat']) ? $this->validateMailFormat($contactData['MailFormat']) : null;

        $validatedContactData['IsTestContact'] = isset($contactData['IsTestContact']) ? (bool)$contactData['IsTestContact'] : null;

        if (true === isset($contactData['CustomAttributes'])) {
            $customAttributes = [];

            foreach ($contactData['CustomAttributes'] as $customAttributeId => $customAttributeValue) {
                $customAttributes[] = new CustomAttribute(
                    $customAttributeId,
                    empty($customAttributeValue),
                    empty($customAttributeValue) ? null : $customAttributeValue
                );
            }

            $validatedContactData['CustomAttributes'] = $customAttributes;
        }

        if (true === isset($contactData['AddToGroup'])) {
            $validatedContactData['AddToGroup'] = $contactData['AddToGroup'];
        }

        if (true === isset($contactData['RemoveFromGroup'])) {
            $validatedContactData['RemoveFromGroup'] = $contactData['RemoveFromGroup'];
        }

        return $validatedContactData;
    }

    /**
     * @param string $status
     *
     * @return string
     */
    private function validateStatus($status)
    {
        $statuses = [
            'Unsubscribed',
            'Subscribed',
            'HardBounce',
            'SoftBounce',
            'Pending',
        ];

        if (null === in_array($status, $statuses)) {
            throw new InvalidConfigurationException(sprintf('Invalid Status "%s".', $status));
        }

        return $status;
    }

    /**
     * @param string $mailFormat
     *
     * @return string
     */
    private function validateMailFormat($mailFormat)
    {
        $mailFormats = [
            'Text',
            'HTML',
            'Both',
        ];

        if (null === in_array($mailFormat, $mailFormats)) {
            throw new InvalidConfigurationException(sprintf('Invalid MailFormat "%s".', $mailFormat));
        }

        return $mailFormat;
    }
}