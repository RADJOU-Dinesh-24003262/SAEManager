<?php

namespace Core\includes\exception\SAE;

/**
 * Thrown when a user tries to perform an action they are not authorized to do.
 * e.g., A student trying to delete an SAE.
 */
class ExceptionAccessDenied extends ExceptionSAE
{
}
