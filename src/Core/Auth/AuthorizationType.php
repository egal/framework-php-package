<?php

namespace Egal\Core\Auth;

enum AuthorizationType: string
{
    case Cookie = 'cookie';
    case Header = 'header';
}
