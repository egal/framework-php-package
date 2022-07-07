<?php

namespace Egal\Core\Auth;

enum TokenType: string
{
    case Access = 'access';
    case Refresh = 'refresh';
}
