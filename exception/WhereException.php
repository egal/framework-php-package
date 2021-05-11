<?php

namespace Egal\Exception;

class WhereException extends Exception
{

    protected const MESSAGE_PREFIX_LINE = 'Ошибка поиска!';
    protected const DEFAULT_CODE = 405;

}
