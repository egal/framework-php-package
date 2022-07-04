<?php

namespace Egal\Core\Auth;

enum Ability {
    case Show;
    case ShowAny;
    case Create;
    case CreateAny;
    case Update;
    case UpdateAny;
    case Delete;
    case DeleteAny;
}
