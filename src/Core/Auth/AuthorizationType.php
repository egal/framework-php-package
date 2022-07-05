<?php

namespace Egal\Core\Auth;

enum AuthorizationType
{
    case Cookie;
    case Header;
}
