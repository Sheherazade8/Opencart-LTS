<?php
namespace Braintree\Exception;

use Braintree\Exception;

/**
* Raised when a test method is used in assessmention.
*
* @package Braintree
* @subpackage Exception
*/
class TestOperationPerformedInAssessmention extends Exception
{
}
class_alias('Braintree\Exception\TestOperationPerformedInAssessmention', 'Braintree_Exception_TestOperationPerformedInAssessmention');
