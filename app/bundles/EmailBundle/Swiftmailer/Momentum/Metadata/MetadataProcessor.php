<?php

namespace Mautic\EmailBundle\Swiftmailer\Momentum\Metadata;

use Mautic\EmailBundle\Helper\MailHelper;
use Mautic\EmailBundle\Swiftmailer\Message\MauticMessage;

class MetadataProcessor
{
    /**
     * @var array
     */
    private $metadata = [];

    /**
     * @var array
     */
    private $substitutionKeys = [];

    /**
     * @var array
     */
    private $substitutionMergeVars = [];

    /**
     * @var array
     */
    private $mauticTokens = [];

    /**
     * @var \Swift_Message
     */
    private $message;

    /**
     * @var string
     */
    private $campaignId;

    /**
     * MetadataProcessor constructor.
     */
    public function __construct(\Swift_Message $message)
    {
        $this->message = $message;

        $metadata       = ($message instanceof MauticMessage) ? $message->getMetadata() : [];
        $this->metadata = $metadata;

        // Build the substitution merge vars
        $this->buildSubstitutionData();

        if (count($this->mauticTokens)) {
            // Update the content with the substitution merge vars
            MailHelper::searchReplaceTokens($this->mauticTokens, $this->substitutionMergeVars, $this->message);
        }
    }

    /**
     * @return array|mixed
     */
    public function getMetadata($email)
    {
        if (!isset($this->metadata[$email])) {
            return [];
        }

        $metadata = $this->metadata[$email];

        // remove the tokens as they'll be part of the substitution data
        unset($metadata['tokens']);

        return $metadata;
    }

    /**
     * @return array
     */
    public function getSubstitutionData($email)
    {
        if (!isset($this->metadata[$email])) {
            return [];
        }

        $substitutionData = [];
        foreach ($this->metadata[$email]['tokens'] as $token => $value) {
            $substitutionData[$this->substitutionKeys[$token]] = $value;
        }

        return $substitutionData;
    }

    /**
     * @return string|null
     */
    public function getCampaignId()
    {
        // Sparkpost/Momentum only supports 64 bytes
        return $this->campaignId ? mb_strcut($this->campaignId, 0, 64) : null;
    }

    private function buildSubstitutionData()
    {
        // Sparkpost uses {{ name }} for tokens so Mautic's need to be converted; although using their {{{ }}} syntax to prevent HTML escaping
        $metadataSample = reset($this->metadata);
        if (!$metadataSample) {
            return;
        }

        $tokens             = (!empty($metadataSample['tokens'])) ? $metadataSample['tokens'] : [];
        $this->mauticTokens = array_keys($tokens);

        foreach ($this->mauticTokens as $token) {
            $this->substitutionKeys[$token]      = strtoupper(preg_replace('/[^a-z0-9]+/i', '', $token));
            $this->substitutionMergeVars[$token] = '{{{ '.$this->substitutionKeys[$token].' }}}';
        }

        $this->extractCampaignId($metadataSample);
    }

    private function extractCampaignId(array $metadataSample)
    {
        // Extract and build a campaign ID from the metadata sample
        if (!empty($metadataSample['utmTags']['utmCampaign'])) {
            $this->campaignId = $metadataSample['utmTags']['utmCampaign'];

            return;
        }

        if (empty($metadataSample['emailId'])) {
            return;
        }

        $this->campaignId = $metadataSample['emailId'].':'.$metadataSample['emailName'];
    }
}
