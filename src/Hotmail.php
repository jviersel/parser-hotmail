<?php

namespace AbuseIO\Parsers;

use AbuseIO\Models\Incident;

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
        $subject = $this->parsedMail->getSubject();
        $report = [];

        if (preg_match('/complaint about message from ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', $subject, $matches)) {
            $report['Source-IP'] = $matches[1];
        }

        if ($this->hasRequiredFields($report) === true) {
            $report = $this->applyFilters($report);
            $incident = new Incident();
            $incident->source      = config("{$this->configBase}.parser.name");
            $incident->source_id   = false;
            $incident->ip          = $report['Source-IP'];
            $incident->domain      = false;
            $incident->class       = config("{$this->configBase}.feeds.{$this->feedName}.class");
            $incident->type        = config("{$this->configBase}.feeds.{$this->feedName}.type");
            $incident->timestamp   = false;
            $incident->information = json_encode($report);
            $this->incidents[] = $incident;
        }
        return $this->success();
    }
}