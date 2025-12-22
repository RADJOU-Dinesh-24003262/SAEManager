<?php

namespace Core\includes\exception\SAE;

use Exception;

/**
 * Base Exception for all SAE related errors.
 * This allows controllers to catch 'ExceptionSAE' to handle any SAE logic error.
 */
class ExceptionSAE extends Exception
{
}
