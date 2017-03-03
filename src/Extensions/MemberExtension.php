<?php
namespace Zanderwar\SteamAuth\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\Security\Member;
use Zanderwar\Scraplands\Models\Player;
use Zanderwar\SteamAuth\SteamAuth;

/**
 * Class MemberExtension
 * 
 * @package Zanderwar\SteamAuth\Extensions
 * @property DBVarchar SteamID
 */
class MemberExtension extends DataExtension {
    private static $db = [
        'SteamID' => 'Varchar(30)'
    ];

    private static $summary_fields = [
        'SteamID'
    ];
}