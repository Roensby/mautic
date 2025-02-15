<?php

return [
    'routes' => [
        'main' => [
            'mautic_email_index' => [
                'path'       => '/emails/{page}',
                'controller' => 'Mautic\EmailBundle\Controller\EmailController::indexAction',
            ],
            'mautic_email_graph_stats' => [
                'path'       => '/emails-graph-stats/{objectId}/{isVariant}/{dateFrom}/{dateTo}',
                'controller' => 'Mautic\EmailBundle\Controller\EmailGraphStatsController::viewAction',
            ],
            'mautic_email_action' => [
                'path'       => '/emails/{objectAction}/{objectId}',
                'controller' => 'Mautic\EmailBundle\Controller\EmailController::executeAction',
            ],
            'mautic_email_contacts' => [
                'path'       => '/emails/view/{objectId}/contact/{page}',
                'controller' => 'Mautic\EmailBundle\Controller\EmailController::contactsAction',
            ],
        ],
        'api' => [
            'mautic_api_emailstandard' => [
                'standard_entity' => true,
                'name'            => 'emails',
                'path'            => '/emails',
                'controller'      => 'Mautic\EmailBundle\Controller\Api\EmailApiController',
            ],
            'mautic_api_sendemail' => [
                'path'       => '/emails/{id}/send',
                'controller' => 'Mautic\EmailBundle\Controller\Api\EmailApiController::sendAction',
                'method'     => 'POST',
            ],
            'mautic_api_sendcontactemail' => [
                'path'       => '/emails/{id}/contact/{leadId}/send',
                'controller' => 'Mautic\EmailBundle\Controller\Api\EmailApiController::sendLeadAction',
                'method'     => 'POST',
            ],
            'mautic_api_reply' => [
                'path'       => '/emails/reply/{trackingHash}',
                'controller' => 'Mautic\EmailBundle\Controller\Api\EmailApiController::replyAction',
                'method'     => 'POST',
            ],
        ],
        'public' => [
            'mautic_plugin_tracker' => [
                'path'         => '/plugin/{integration}/tracking.gif',
                'controller'   => 'Mautic\EmailBundle\Controller\PublicController::pluginTrackingGifAction',
                'requirements' => [
                    'integration' => '.+',
                ],
            ],
            'mautic_email_tracker' => [
                'path'       => '/email/{idHash}.gif',
                'controller' => 'Mautic\EmailBundle\Controller\PublicController::trackingImageAction',
            ],
            'mautic_email_webview' => [
                'path'       => '/email/view/{idHash}',
                'controller' => 'Mautic\EmailBundle\Controller\PublicController::indexAction',
            ],
            'mautic_email_unsubscribe' => [
                'path'       => '/email/unsubscribe/{idHash}',
                'controller' => 'Mautic\EmailBundle\Controller\PublicController::unsubscribeAction',
            ],
            'mautic_email_resubscribe' => [
                'path'       => '/email/resubscribe/{idHash}',
                'controller' => 'Mautic\EmailBundle\Controller\PublicController::resubscribeAction',
            ],
            'mautic_mailer_transport_callback' => [
                'path'       => '/mailer/{transport}/callback',
                'controller' => 'Mautic\EmailBundle\Controller\PublicController::mailerCallbackAction',
                'method'     => ['GET', 'POST'],
            ],
            'mautic_email_preview' => [
                'path'       => '/email/preview/{objectId}',
                'controller' => 'Mautic\EmailBundle\Controller\PublicController::previewAction',
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'items' => [
                'mautic.email.emails' => [
                    'route'    => 'mautic_email_index',
                    'access'   => ['email:emails:viewown', 'email:emails:viewother'],
                    'parent'   => 'mautic.core.channels',
                    'priority' => 100,
                ],
            ],
        ],
    ],
    'categories' => [
        'email' => null,
    ],
    'services' => [
        'other' => [
            'mautic.spool.delegator' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\Spool\DelegatingSpool::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'swiftmailer.mailer.default.transport.real',
                ],
            ],

            // Mailers
            'mautic.transport.spool' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\Transport\SpoolTransport::class,
                'arguments' => [
                    'swiftmailer.mailer.default.transport.eventdispatcher',
                    'mautic.spool.delegator',
                ],
            ],

            'mautic.transport.amazon' => [
                'class'        => \Mautic\EmailBundle\Swiftmailer\Transport\AmazonTransport::class,
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'arguments'    => [
                    '%mautic.mailer_amazon_region%',
                    '%mautic.mailer_amazon_other_region%',
                    '%mautic.mailer_port%',
                    'mautic.transport.amazon.callback',
                ],
                'methodCalls' => [
                    'setUsername' => ['%mautic.mailer_user%'],
                    'setPassword' => ['%mautic.mailer_password%'],
                ],
            ],
            'mautic.transport.amazon_api' => [
                'class'        => \Mautic\EmailBundle\Swiftmailer\Transport\AmazonApiTransport::class,
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'arguments'    => [
                    'translator',
                    'mautic.transport.amazon.callback',
                    'monolog.logger.mautic',
                ],
                'methodCalls' => [
                    'setRegion' => [
                        '%mautic.mailer_amazon_region%',
                        '%mautic.mailer_amazon_other_region%',
                    ],
                    'setUsername' => ['%mautic.mailer_user%'],
                    'setPassword' => ['%mautic.mailer_password%'],
                ],
            ],
            'mautic.transport.mandrill' => [
                'class'        => 'Mautic\EmailBundle\Swiftmailer\Transport\MandrillTransport',
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'arguments'    => [
                    'translator',
                    'mautic.email.model.transport_callback',
                ],
                'methodCalls'  => [
                    'setUsername'      => ['%mautic.mailer_user%'],
                    'setPassword'      => ['%mautic.mailer_api_key%'],
                ],
            ],
            'mautic.transport.mailjet' => [
                'class'        => 'Mautic\EmailBundle\Swiftmailer\Transport\MailjetTransport',
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'arguments'    => [
                    'mautic.email.model.transport_callback',
                    '%mautic.mailer_mailjet_sandbox%',
                    '%mautic.mailer_mailjet_sandbox_default_mail%',
                ],
                'methodCalls' => [
                    'setUsername' => ['%mautic.mailer_user%'],
                    'setPassword' => ['%mautic.mailer_password%'],
                ],
            ],
            'mautic.transport.momentum' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\Transport\MomentumTransport::class,
                'arguments' => [
                    'mautic.transport.momentum.callback',
                    'mautic.transport.momentum.facade',
                ],
                'tag'          => 'mautic.email_transport',
                'tagArguments' => [
                    \Mautic\EmailBundle\Model\TransportType::TRANSPORT_ALIAS => 'mautic.email.config.mailer_transport.momentum',
                    \Mautic\EmailBundle\Model\TransportType::FIELD_HOST      => true,
                    \Mautic\EmailBundle\Model\TransportType::FIELD_PORT      => true,
                    \Mautic\EmailBundle\Model\TransportType::FIELD_API_KEY   => true,
                ],
            ],
            'mautic.transport.momentum.adapter' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\Momentum\Adapter\Adapter::class,
                'arguments' => [
                    'mautic.transport.momentum.sparkpost',
                ],
            ],
            'mautic.transport.momentum.service.swift_message' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\Momentum\Service\SwiftMessageService::class,
            ],
            'mautic.transport.momentum.validator.swift_message' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\Momentum\Validator\SwiftMessageValidator\SwiftMessageValidator::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'mautic.transport.momentum.callback' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\Momentum\Callback\MomentumCallback::class,
                'arguments' => [
                    'mautic.email.model.transport_callback',
                ],
            ],
            'mautic.transport.momentum.facade' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\Momentum\Facade\MomentumFacade::class,
                'arguments' => [
                    'mautic.transport.momentum.adapter',
                    'mautic.transport.momentum.service.swift_message',
                    'mautic.transport.momentum.validator.swift_message',
                    'mautic.transport.momentum.callback',
                    'monolog.logger.mautic',
                ],
            ],
            'mautic.transport.momentum.sparkpost' => [
                'class'     => \SparkPost\SparkPost::class,
                'factory'   => ['@mautic.sparkpost.factory', 'create'],
                'arguments' => [
                    '%mautic.mailer_host%',
                    '%mautic.mailer_api_key%',
                    '%mautic.mailer_port%',
                ],
            ],
            'mautic.transport.sendgrid' => [
                'class'        => \Mautic\EmailBundle\Swiftmailer\Transport\SendgridTransport::class,
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'methodCalls'  => [
                    'setUsername' => ['%mautic.mailer_user%'],
                    'setPassword' => ['%mautic.mailer_password%'],
                ],
            ],
            'mautic.transport.sendgrid_api' => [
                'class'        => \Mautic\EmailBundle\Swiftmailer\Transport\SendgridApiTransport::class,
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'arguments'    => [
                    'mautic.transport.sendgrid_api.facade',
                    'mautic.transport.sendgrid_api.calback',
                ],
            ],
            'mautic.transport.sendgrid_api.facade' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\SendGrid\SendGridApiFacade::class,
                'arguments' => [
                    'mautic.transport.sendgrid_api.sendgrid_wrapper',
                    'mautic.transport.sendgrid_api.message',
                    'mautic.transport.sendgrid_api.response',
                ],
            ],
            'mautic.transport.sendgrid_api.mail.base' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\SendGrid\Mail\SendGridMailBase::class,
                'arguments' => [
                    'mautic.helper.plain_text_message',
                ],
            ],
            'mautic.transport.sendgrid_api.mail.personalization' => [
                'class' => \Mautic\EmailBundle\Swiftmailer\SendGrid\Mail\SendGridMailPersonalization::class,
            ],
            'mautic.transport.sendgrid_api.mail.metadata' => [
                'class' => \Mautic\EmailBundle\Swiftmailer\SendGrid\Mail\SendGridMailMetadata::class,
            ],
            'mautic.transport.sendgrid_api.mail.attachment' => [
                'class' => \Mautic\EmailBundle\Swiftmailer\SendGrid\Mail\SendGridMailAttachment::class,
            ],
            'mautic.transport.sendgrid_api.message' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\SendGrid\SendGridApiMessage::class,
                'arguments' => [
                    'mautic.transport.sendgrid_api.mail.base',
                    'mautic.transport.sendgrid_api.mail.personalization',
                    'mautic.transport.sendgrid_api.mail.metadata',
                    'mautic.transport.sendgrid_api.mail.attachment',
                ],
            ],
            'mautic.transport.sendgrid_api.response' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\SendGrid\SendGridApiResponse::class,
                'arguments' => [
                    'monolog.logger.mautic',
                ],
            ],
            'mautic.transport.sendgrid_api.sendgrid_wrapper' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\SendGrid\SendGridWrapper::class,
                'arguments' => [
                    'mautic.transport.sendgrid_api.sendgrid',
                ],
            ],
            'mautic.transport.sendgrid_api.sendgrid' => [
                'class'     => \SendGrid::class,
                'arguments' => [
                    '%mautic.mailer_api_key%',
                ],
            ],
            'mautic.transport.sendgrid_api.calback' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\SendGrid\Callback\SendGridApiCallback::class,
                'arguments' => [
                    'mautic.email.model.transport_callback',
                ],
            ],
            'mautic.transport.amazon.callback' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\Amazon\AmazonCallback::class,
                'arguments' => [
                    'translator',
                    'monolog.logger.mautic',
                    'mautic.http.client',
                    'mautic.email.model.transport_callback',
                ],
            ],
            'mautic.transport.elasticemail' => [
                'class'        => 'Mautic\EmailBundle\Swiftmailer\Transport\ElasticemailTransport',
                'arguments'    => [
                    'translator',
                    'monolog.logger.mautic',
                    'mautic.email.model.transport_callback',
                ],
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'methodCalls'  => [
                    'setUsername' => ['%mautic.mailer_user%'],
                    'setPassword' => ['%mautic.mailer_password%'],
                ],
            ],
            'mautic.transport.pepipost' => [
                'class'        => \Mautic\EmailBundle\Swiftmailer\Transport\PepipostTransport::class,
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'arguments'    => [
                    'translator',
                    'monolog.logger.mautic',
                    'mautic.email.model.transport_callback',
                ],
                'methodCalls' => [
                    'setUsername' => ['%mautic.mailer_user%'],
                    'setPassword' => ['%mautic.mailer_password%'],
                ],
            ],
            'mautic.transport.postmark' => [
                'class'        => 'Mautic\EmailBundle\Swiftmailer\Transport\PostmarkTransport',
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'methodCalls'  => [
                    'setUsername' => ['%mautic.mailer_user%'],
                    'setPassword' => ['%mautic.mailer_password%'],
                ],
            ],
            'mautic.transport.sparkpost' => [
                'class'        => 'Mautic\EmailBundle\Swiftmailer\Transport\SparkpostTransport',
                'serviceAlias' => 'swiftmailer.mailer.transport.%s',
                'arguments'    => [
                    '%mautic.mailer_api_key%',
                    'translator',
                    'mautic.email.model.transport_callback',
                    'mautic.sparkpost.factory',
                    'monolog.logger.mautic',
                    'mautic.helper.core_parameters',
                ],
            ],
            'mautic.sparkpost.factory' => [
                'class'     => \Mautic\EmailBundle\Swiftmailer\Sparkpost\SparkpostFactory::class,
                'arguments' => [
                    'mautic.guzzle.client',
                ],
            ],
            'mautic.guzzle.client.factory' => [
                'class' => \Mautic\EmailBundle\Swiftmailer\Guzzle\ClientFactory::class,
            ],
            /**
             * Needed for Sparkpost integration. Can be removed when this integration is moved to
             * its own plugin.
             */
            'mautic.guzzle.client' => [
                'class'     => \Http\Adapter\Guzzle7\Client::class,
                'factory'   => ['@mautic.guzzle.client.factory', 'create'],
            ],
            'mautic.helper.mailbox' => [
                'class'     => 'Mautic\EmailBundle\MonitoredEmail\Mailbox',
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.helper.paths',
                ],
            ],
            'mautic.message.search.contact' => [
                'class'     => \Mautic\EmailBundle\MonitoredEmail\Search\ContactFinder::class,
                'arguments' => [
                    'mautic.email.repository.stat',
                    'mautic.lead.repository.lead',
                    'monolog.logger.mautic',
                ],
            ],
            'mautic.message.processor.bounce' => [
                'class'     => \Mautic\EmailBundle\MonitoredEmail\Processor\Bounce::class,
                'arguments' => [
                    'swiftmailer.mailer.default.transport.real',
                    'mautic.message.search.contact',
                    'mautic.email.repository.stat',
                    'mautic.lead.model.lead',
                    'translator',
                    'monolog.logger.mautic',
                    'mautic.lead.model.dnc',
                ],
            ],
            'mautic.message.processor.unsubscribe' => [
                'class'     => \Mautic\EmailBundle\MonitoredEmail\Processor\Unsubscribe::class,
                'arguments' => [
                    'swiftmailer.mailer.default.transport.real',
                    'mautic.message.search.contact',
                    'translator',
                    'monolog.logger.mautic',
                    'mautic.lead.model.dnc',
                ],
            ],
            'mautic.message.processor.feedbackloop' => [
                'class'     => \Mautic\EmailBundle\MonitoredEmail\Processor\FeedbackLoop::class,
                'arguments' => [
                    'mautic.message.search.contact',
                    'translator',
                    'monolog.logger.mautic',
                    'mautic.lead.model.dnc',
                ],
            ],
            'mautic.message.processor.replier' => [
                'class'     => \Mautic\EmailBundle\MonitoredEmail\Processor\Reply::class,
                'arguments' => [
                    'mautic.email.repository.stat',
                    'mautic.message.search.contact',
                    'mautic.lead.model.lead',
                    'event_dispatcher',
                    'monolog.logger.mautic',
                    'mautic.tracker.contact',
                    'mautic.helper.email.address',
                ],
            ],
            'mautic.helper.mailer' => [
                'class'     => \Mautic\EmailBundle\Helper\MailHelper::class,
                'arguments' => [
                    'mautic.factory',
                    'mailer',
                ],
            ],
            'mautic.helper.plain_text_message' => [
                'class'     => \Mautic\EmailBundle\Helper\PlainTextMessageHelper::class,
            ],
            'mautic.validator.email' => [
                'class'     => \Mautic\EmailBundle\Helper\EmailValidator::class,
                'arguments' => [
                    'translator',
                    'event_dispatcher',
                ],
            ],
            'mautic.email.fetcher' => [
                'class'     => \Mautic\EmailBundle\MonitoredEmail\Fetcher::class,
                'arguments' => [
                    'mautic.helper.mailbox',
                    'event_dispatcher',
                    'translator',
                ],
            ],
            'mautic.email.helper.stat' => [
                'class'     => \Mautic\EmailBundle\Stat\StatHelper::class,
                'arguments' => [
                    'mautic.email.repository.stat',
                ],
            ],
            'mautic.email.helper.request.storage' => [
                'class'     => \Mautic\EmailBundle\Helper\RequestStorageHelper::class,
                'arguments' => [
                    'mautic.helper.cache_storage',
                ],
            ],
            'mautic.email.helper.stats_collection' => [
                'class'     => \Mautic\EmailBundle\Helper\StatsCollectionHelper::class,
                'arguments' => [
                    'mautic.email.stats.helper_container',
                ],
            ],
            'mautic.email.stats.helper_container' => [
                'class' => \Mautic\EmailBundle\Stats\StatHelperContainer::class,
            ],
            'mautic.email.stats.helper_bounced' => [
                'class'     => \Mautic\EmailBundle\Stats\Helper\BouncedHelper::class,
                'arguments' => [
                    'mautic.stats.aggregate.collector',
                    'doctrine.dbal.default_connection',
                    'mautic.generated.columns.provider',
                    'mautic.helper.user',
                ],
                'tag' => 'mautic.email_stat_helper',
            ],
            'mautic.email.stats.helper_clicked' => [
                'class'     => \Mautic\EmailBundle\Stats\Helper\ClickedHelper::class,
                'arguments' => [
                    'mautic.stats.aggregate.collector',
                    'doctrine.dbal.default_connection',
                    'mautic.generated.columns.provider',
                    'mautic.helper.user',
                ],
                'tag' => 'mautic.email_stat_helper',
            ],
            'mautic.email.stats.helper_failed' => [
                'class'     => \Mautic\EmailBundle\Stats\Helper\FailedHelper::class,
                'arguments' => [
                    'mautic.stats.aggregate.collector',
                    'doctrine.dbal.default_connection',
                    'mautic.generated.columns.provider',
                    'mautic.helper.user',
                ],
                'tag' => 'mautic.email_stat_helper',
            ],
            'mautic.email.stats.helper_opened' => [
                'class'     => \Mautic\EmailBundle\Stats\Helper\OpenedHelper::class,
                'arguments' => [
                    'mautic.stats.aggregate.collector',
                    'doctrine.dbal.default_connection',
                    'mautic.generated.columns.provider',
                    'mautic.helper.user',
                ],
                'tag' => 'mautic.email_stat_helper',
            ],
            'mautic.email.stats.helper_sent' => [
                'class'     => \Mautic\EmailBundle\Stats\Helper\SentHelper::class,
                'arguments' => [
                    'mautic.stats.aggregate.collector',
                    'doctrine.dbal.default_connection',
                    'mautic.generated.columns.provider',
                    'mautic.helper.user',
                ],
                'tag' => 'mautic.email_stat_helper',
            ],
            'mautic.email.stats.helper_unsubscribed' => [
                'class'     => \Mautic\EmailBundle\Stats\Helper\UnsubscribedHelper::class,
                'arguments' => [
                    'mautic.stats.aggregate.collector',
                    'doctrine.dbal.default_connection',
                    'mautic.generated.columns.provider',
                    'mautic.helper.user',
                ],
                'tag' => 'mautic.email_stat_helper',
            ],
        ],
        'models' => [
            'mautic.email.model.email' => [
                'class'     => \Mautic\EmailBundle\Model\EmailModel::class,
                'arguments' => [
                    'mautic.helper.ip_lookup',
                    'mautic.helper.theme',
                    'mautic.helper.mailbox',
                    'mautic.helper.mailer',
                    'mautic.lead.model.lead',
                    'mautic.lead.model.company',
                    'mautic.page.model.trackable',
                    'mautic.user.model.user',
                    'mautic.channel.model.queue',
                    'mautic.email.model.send_email_to_contacts',
                    'mautic.tracker.device',
                    'mautic.page.repository.redirect',
                    'mautic.helper.cache_storage',
                    'mautic.tracker.contact',
                    'mautic.lead.model.dnc',
                    'mautic.email.helper.stats_collection',
                    'mautic.security',
                    'doctrine.dbal.default_connection',
                ],
            ],
            'mautic.email.model.send_email_to_user' => [
                'class'     => \Mautic\EmailBundle\Model\SendEmailToUser::class,
                'arguments' => [
                    'mautic.email.model.email',
                    'event_dispatcher',
                    'mautic.lead.validator.custom_field',
                    'mautic.validator.email',
                ],
            ],
            'mautic.email.model.send_email_to_contacts' => [
                'class'     => \Mautic\EmailBundle\Model\SendEmailToContact::class,
                'arguments' => [
                    'mautic.helper.mailer',
                    'mautic.email.helper.stat',
                    'mautic.lead.model.dnc',
                    'translator',
                ],
            ],
            'mautic.email.model.transport_callback' => [
                'class'     => \Mautic\EmailBundle\Model\TransportCallback::class,
                'arguments' => [
                    'mautic.lead.model.dnc',
                    'mautic.message.search.contact',
                    'mautic.email.repository.stat',
                ],
            ],
            'mautic.email.transport_type' => [
                'class'     => \Mautic\EmailBundle\Model\TransportType::class,
                'arguments' => [],
            ],
        ],
        'validator' => [
            'mautic.email.validator.multiple_emails_valid_validator' => [
                'class'     => \Mautic\EmailBundle\Validator\MultipleEmailsValidValidator::class,
                'arguments' => [
                    'mautic.validator.email',
                ],
                'tag' => 'validator.constraint_validator',
            ],
            'mautic.email.validator.email_or_token_list_validator' => [
                'class'     => \Mautic\EmailBundle\Validator\EmailOrEmailTokenListValidator::class,
                'arguments' => [
                    'mautic.validator.email',
                    'mautic.lead.validator.custom_field',
                ],
                'tag' => 'validator.constraint_validator',
            ],
        ],
        'repositories' => [
            'mautic.email.repository.email' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Mautic\EmailBundle\Entity\Email::class,
                ],
            ],
            'mautic.email.repository.emailReply' => [
                'class'     => \Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Mautic\EmailBundle\Entity\EmailReply::class,
                ],
            ],
            'mautic.email.repository.stat' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Mautic\EmailBundle\Entity\Stat::class,
                ],
            ],
        ],
        'fixtures' => [
            'mautic.email.fixture.email' => [
                'class'     => Mautic\EmailBundle\DataFixtures\ORM\LoadEmailData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['mautic.email.model.email'],
            ],
        ],
    ],
    'parameters' => [
        'mailer_api_key'                 => null, // Api key from mail delivery provider.
        'mailer_from_name'               => 'Mautic',
        'mailer_from_email'              => 'email@yoursite.com',
        'mailer_reply_to_email'          => null,
        'mailer_return_path'             => null,
        'mailer_transport'               => 'smtp',
        'mailer_append_tracking_pixel'   => true,
        'mailer_convert_embed_images'    => false,
        'mailer_host'                    => '',
        'mailer_port'                    => null,
        'mailer_user'                    => null,
        'mailer_password'                => null,
        'mailer_encryption'              => null, // tls or ssl,
        'mailer_auth_mode'               => null, // plain, login or cram-md5
        'mailer_amazon_region'           => 'us-east-1',
        'mailer_amazon_other_region'     => null,
        'mailer_custom_headers'          => [],
        'mailer_spool_type'              => 'memory', // memory = immediate; file = queue
        'mailer_spool_path'              => '%kernel.project_dir%/var/spool',
        'mailer_spool_msg_limit'         => null,
        'mailer_spool_time_limit'        => null,
        'mailer_spool_recover_timeout'   => 900,
        'mailer_spool_clear_timeout'     => 1800,
        'unsubscribe_text'               => null,
        'webview_text'                   => null,
        'unsubscribe_message'            => null,
        'resubscribe_message'            => null,
        'monitored_email'                => [
            'general' => [
                'address'         => null,
                'host'            => null,
                'port'            => '993',
                'encryption'      => '/ssl',
                'user'            => null,
                'password'        => null,
                'use_attachments' => false,
            ],
            'EmailBundle_bounces' => [
                'address'           => null,
                'host'              => null,
                'port'              => '993',
                'encryption'        => '/ssl',
                'user'              => null,
                'password'          => null,
                'override_settings' => 0,
                'folder'            => null,
            ],
            'EmailBundle_unsubscribes' => [
                'address'           => null,
                'host'              => null,
                'port'              => '993',
                'encryption'        => '/ssl',
                'user'              => null,
                'password'          => null,
                'override_settings' => 0,
                'folder'            => null,
            ],
            'EmailBundle_replies' => [
                'address'           => null,
                'host'              => null,
                'port'              => '993',
                'encryption'        => '/ssl',
                'user'              => null,
                'password'          => null,
                'override_settings' => 0,
                'folder'            => null,
            ],
        ],
        'mailer_is_owner'                                                   => false,
        'default_signature_text'                                            => null,
        'email_frequency_number'                                            => 0,
        'email_frequency_time'                                              => 'DAY',
        'show_contact_preferences'                                          => false,
        'show_contact_frequency'                                            => false,
        'show_contact_pause_dates'                                          => false,
        'show_contact_preferred_channels'                                   => false,
        'show_contact_categories'                                           => false,
        'show_contact_segments'                                             => false,
        'mailer_mailjet_sandbox'                                            => false,
        'mailer_mailjet_sandbox_default_mail'                               => null,
        'disable_trackable_urls'                                            => false,
        'theme_email_default'                                               => 'blank',
        'mailer_sparkpost_region'                                           => 'us',
        'mailer_memory_msg_limit'                                           => 100,
        \Mautic\EmailBundle\Form\Type\ConfigType::MINIFY_EMAIL_HTML         => false,
    ],
];
