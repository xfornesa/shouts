<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Service;

use Prunatic\WebBundle\Entity\Shout;
use \Swift_Mailer as Swift_Mailer;
use Symfony\Component\Templating\EngineInterface;

class NotificationManager
{
    /** @var Swift_Mailer $mailer */
    protected $mailer;

    /** @var EngineInterface $templating */
    protected $templating;

    /**
     * @param Swift_Mailer $mailer
     * @param EngineInterface $templating
     */
    public function __construct(Swift_Mailer $mailer, EngineInterface $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    /**
     * @param Shout $shout
     * @param string $url
     * @return bool
     */
    public function sendShoutRemovalConfirmationEmail($shout, $url)
    {
        // TODO get data from configuration
        $subject = 'Confirmació per silenciar un crit';
        $from = 'send@example.com';
        $to = $shout->getEmail();

        // TODO render body with a template
        $body = <<<EOF
            Hola, si us play fes clic aquí per confirmar que vols silenciar el teu crit:
            <a href="{$url}">{$url}</a>
EOF;

        /** @var \Swift_Message $message */
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($body);
        ;

        $i_recipients = $this->mailer->send($message);

        return $i_recipients > 0;
    }
}