<?php
/**
 * Author: Xavier
 */

namespace Prunatic\WebBundle\Entity;


class DuplicateIpException extends \Exception
{
    protected $message = 'Duplicate IP exception';
}