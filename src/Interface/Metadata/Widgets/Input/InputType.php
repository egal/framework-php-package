<?php

namespace Egal\Interface\Metadata\Widgets\Input;

enum InputType: string
{
    case Text = 'test';
    case Number = 'number';
    case Password = 'password';
    case Search ='search';
}
