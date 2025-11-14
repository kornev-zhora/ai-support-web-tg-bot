<?php

namespace App\Enums;

enum TelegramCommand: string
{
    case START = 'start';
    case HELP = 'help';
    case SETTINGS = 'settings';
}
