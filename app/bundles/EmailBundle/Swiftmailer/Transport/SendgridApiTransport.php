<?php

namespace Mautic\EmailBundle\Swiftmailer\Transport;

use Mautic\EmailBundle\Swiftmailer\SendGrid\Callback\SendGridApiCallback;
use Mautic\EmailBundle\Swiftmailer\SendGrid\SendGridApiFacade;
use Symfony\Component\HttpFoundation\Request;

class SendgridApiTransport implements \Swift_Transport, TokenTransportInterface, CallbackTransportInterface
{
    /**
     * @var SendGridApiFacade
     */
    private $sendGridApiFacade;

    /**
     * @var \Swift_Events_SimpleEventDispatcher
     */
    private $swiftEventDispatcher;

    /**
     * @var bool
     */
    private $started = false;

    /**
     * @var SendGridApiCallback
     */
    private $sendGridApiCallback;

    public function __construct(SendGridApiFacade $sendGridApiFacade, SendGridApiCallback $sendGridApiCallback)
    {
        $this->sendGridApiFacade   = $sendGridApiFacade;
        $this->sendGridApiCallback = $sendGridApiCallback;
    }

    /**
     * Test if this Transport mechanism has started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Start this Transport mechanism.
     *
     * @throws \Swift_TransportException
     */
    public function start()
    {
        $this->started = true;
    }

    /**
     * Stop this Transport mechanism.
     */
    public function stop()
    {
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param string[] $failedRecipients An array of failures by-reference
     *
     * @return int
     *
     * @throws \Swift_TransportException
     */
    public function send(\Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->sendGridApiFacade->send($message);

        return count($message->getTo());
    }

    /**
     * Register a plugin in the Transport.
     */
    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        $this->getDispatcher()->bindEventListener($plugin);
    }

    /**
     * @return \Swift_Events_SimpleEventDispatcher
     */
    private function getDispatcher()
    {
        if (null === $this->swiftEventDispatcher) {
            $this->swiftEventDispatcher = new \Swift_Events_SimpleEventDispatcher();
        }

        return $this->swiftEventDispatcher;
    }

    /**
     * Return the max number of to addresses allowed per batch.  If there is no limit, return 0.
     *
     * @return int
     */
    public function getMaxBatchLimit()
    {
        // Sengrid allows to include max 1000 email address into 1 batch
        return 1000;
    }

    /**
     * Get the count for the max number of recipients per batch.
     *
     * @param int    $toBeAdded Number of emails about to be added
     * @param string $type      Type of emails being added (to, cc, bcc)
     *
     * @return int
     */
    public function getBatchRecipientCount(\Swift_Message $message, $toBeAdded = 1, $type = 'to')
    {
        // Sengrid counts all email address (to, cc and bcc)
        // https://sendgrid.com/docs/API_Reference/Web_API_v3/Mail/errors.html#message.personalizations

        $toCount  = is_countable($message->getTo()) ? count($message->getTo()) : 0;
        $ccCount  = is_countable($message->getCc()) ? count($message->getCc()) : 0;
        $bccCount = is_countable($message->getBcc()) ? count($message->getBcc()) : 0;

        return $toCount + $ccCount + $bccCount + $toBeAdded;
    }

    /**
     * Function required to check that $this->message is instanceof MauticMessage, return $this->message->getMetadata() if it is and array() if not.
     *
     * @throws \Exception
     */
    public function getMetadata()
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Returns a "transport" string to match the URL path /mailer/{transport}/callback.
     *
     * @return mixed
     */
    public function getCallbackPath()
    {
        return 'sendgrid_api';
    }

    /**
     * Processes the response.
     */
    public function processCallbackRequest(Request $request)
    {
        $this->sendGridApiCallback->processCallbackRequest($request);
    }

    /**
     * @return bool
     */
    public function ping()
    {
        return true;
    }
}
