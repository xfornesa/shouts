<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Entity;


class OperationNotPermittedException extends \Exception
{
    protected $message = 'Operation not permitted';
}