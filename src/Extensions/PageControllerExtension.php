<?php
namespace Zanderwar\SteamAuth\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Security\Member;
use SilverStripe\View\ArrayData;
use Zanderwar\SteamAuth\SteamAuth;

/**
 * Class PageControllerExtension
 * 
 * @package Zanderwar\SteamAuth\Extensions
 */
class PageControllerExtension extends Extension
{
    public function SteamLoginUrl($returnTo = false) {
        return SteamAuth::getLoginUrl($returnTo, false);
    }

}