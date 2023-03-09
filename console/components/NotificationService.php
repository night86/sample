<?php

namespace console\components;

use Yii;
use yii\base\Component;
use yii\swiftmailer\Mailer;

class NotificationService extends Component
{

    /**
     * Send an e-mail to recipient
     *
     * @param string $sendTo recipient e-mail address
     * @param array $viewParams
     * @param string $emailSubject
     * @param NotificationAttachment|NotificationAttachment[]|null $attachments
     * @param string $layout
     * @return bool
     * @throws InvalidConfigException
     */
    public function send($sendTo, $viewParams = [], $emailSubject = '', $attachments = null, $layout = null): bool
    {

        $caller = debug_backtrace()[1]['function'];

        if (isset($this->config['exclude']) && in_array($sendTo, $this->config['exclude'], true)) {
            Yii::info("{$sendTo} is in the exclusion list, email won't be send.", 'tasks');
            return true;
        }

        Yii::info("Sending {$caller} email to {$sendTo}.", 'tasks');

        $subject = empty($emailSubject) ? $this->config["{$caller}Subject"] ?? 'Notification' : $emailSubject;

        // For test env use mailCatcher component
        /** @var Mailer $mailer */
        $mailer = Yii::$app->get('mailer');

        // restarting the transport to preventing the SSL timeout, because of long working worker
        if ($mailer->getTransport()->isStarted()) {
            $mailer->getTransport()->stop();
            $mailer->getTransport()->start();
        }

        if ($layout !== null) {
            $mailer->htmlLayout = $layout;
        }

        $message = $mailer
            ->compose($caller, $viewParams)
            ->setFrom($this->from)
            ->setTo($sendTo)
            ->setSubject(Yii::t('app', $subject));

        // Set up bbc for notification
        $bbc = $this->globalBcc ? [$this->globalBcc] : [];
        if (isset($this->config["{$caller}Bcc"]) && !empty($this->config["{$caller}Bcc"])) {
            $bbc[] = $this->config["{$caller}Bcc"];
        }
        if (!empty($bbc)) {
            $message->setBcc($bbc);
        }

        if (is_array($attachments)) {
            /** @var NotificationAttachment $attachment */
            foreach ($attachments as $attachment) {
                $message->attach(
                    $attachment->getPath(),
                    [
                        'fileName' => $attachment->getFileName(),
                        'contentType' => $attachment->getContentType()
                    ]
                );
            }
        } elseif ($attachments instanceof NotificationAttachment) {
            $message->attach(
                $attachments->getPath(),
                [
                    'fileName' => $attachments->getFileName(),
                    'contentType' => $attachments->getContentType()
                ]
            );
        }

        $sendResult = $message->send();
        if (!$sendResult) {
            Yii::error("Couldn't send {$caller} email to {$sendTo}.", 'tasks');
        }

        $mailer->getTransport()->stop();

        $this->setDefault();

        return $sendResult;
    }
}