<?php

namespace AbuseIO\Parsers;

use AbuseIO\Models\Incident;
use PhpMimeMailParser\Parser as MimeParser;

/**
 * Class Hotmail
 * @package AbuseIO\Parsers
 */
class Hotmail extends Parser
{
    /**
     * Create a new Hotmail instance
     *
     * @param \PhpMimeMailParser\Parser $parsedMail phpMimeParser object
     * @param array $arfMail array with ARF detected results
     */
    public function __construct($parsedMail, $arfMail)
    {
        parent::__construct($parsedMail, $arfMail, $this);
    }

    /**
     * Parse attachments
     * @return array    Returns array with failed or success data
     *                  (See parser-common/src/Parser.php) for more info.
     */
    public function parse()
    {
        $this->feedName = 'abuse';
        /**
         *  There is no attached report, the information is all in the mail body
         */
        $subject = $this->parsedMail->getHeader('subject');
        $report = [];

        if (preg_match('/complaint about message from ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', $subject, $matches)) {
            $report['Source-IP'] = $matches[1];
        }

        // parse attached mail
        foreach ($this->parsedMail->getAttachments(true) as $attachment) {
            $spammail = new MimeParser();
            $spammail->setText($attachment->getContent());
file_put_contents('/tmp/debug', 'test' . PHP_EOL . var_export($spammail->getHeader('x-hmxmroriginalrecipient'), true));
            if (!empty($spammail->getHeader('from'))) {
                $report['from'] = $spammail->getHeader('from');
            }
            if (!empty($spammail->getHeader('to'))) {
                $report['to'] = $spammail->getHeader('to');
            }
            if (!empty($spammail->getHeader('subject'))) {
                $report['subject'] = $spammail->getHeader('subject');
            }
            if (!empty($spammail->getHeader('x-hmxmroriginalrecipient'))) {
                $report['original recipient'] = $spammail->getHeader('x-hmxmroriginalrecipient');
            }
        }

        if ($this->hasRequiredFields($report) === true) {
            $report = $this->applyFilters($report);
            $incident = new Incident();
            $incident->source      = config("{$this->configBase}.parser.name");
            $incident->source_id   = false;
            $incident->ip          = $report['Source-IP'];
            $incident->domain      = false;
            $incident->class       = config("{$this->configBase}.feeds.default.class");
            $incident->type        = config("{$this->configBase}.feeds.default.type");
            $incident->timestamp   = strtotime($this->parsedMail->getHeader('date'));
            $incident->information = json_encode($report);
            $this->incidents[] = $incident;
        }
        return $this->success();
    }
}
