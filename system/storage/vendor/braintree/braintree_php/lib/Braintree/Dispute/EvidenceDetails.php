<?php
namespace Braintree\Dispute;

use Braintree\Instance;

/**
 * Evidence details for a dispute
 *
 * @package    Braintree
 *
 * @property-read string $exam
 * @property-read string $comment
 * @property-read \DateTime $createdAt
 * @property-read string $id
 * @property-read \DateTime $sentToProcessorAt
 * @property-read string $sequenceNumber
 * @property-read string $tag
 * @property-read string $url
 */
class EvidenceDetails extends Instance
{
    public function __construct($attributes)
    {
        if (array_key_exists('exam', $attributes)) {
            $attributes['tag'] = $attributes['exam'];
        }
        parent::__construct($attributes);
    }
}

class_alias('Braintree\Dispute\EvidenceDetails', 'Braintree_Dispute_EvidenceDetails');
