<?php
namespace Zanderwar\SteamAuth;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Session;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Object;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;

class SteamAuth extends Object
{
    const STEAM_LOGIN_URL = 'https://steamcommunity.com/openid/login';

    /**
     * Get the URL to sign into steam
     *
     * @param mixed $returnTo URI to tell steam where to return, MUST BE THE FULL URI WITH THE PROTOCOL
     * @param bool  $useAmp   Use &amp; in the URL, true; or just &, false.
     *
     * @return string The string to go in the URL
     */
    public static function getLoginUrl($returnTo = false, $useAmp = true)
    {
        Session::set('SteamAuth.ReturnUrl', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . Controller::join_links($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']));
        
        if (!$returnTo) {
            $returnTo = static::config()->validate_url;
            if (!$returnTo) {
                return null;
            }
        }

        $returnTo = str_replace("{{HOST}}", $_SERVER['HTTP_HOST'], $returnTo);

        $params = array(
            'openid.ns'         => 'http://specs.openid.net/auth/2.0',
            'openid.mode'       => 'checkid_setup',
            'openid.return_to'  => $returnTo,
            'openid.realm'      => (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'],
            'openid.identity'   => 'http://specs.openid.net/auth/2.0/identifier_select',
            'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
        );

        $sep = ($useAmp) ? '&amp;' : '&';

        return self::STEAM_LOGIN_URL . '?' . http_build_query($params, '', $sep);
    }

    /**
     * Validate the incoming data
     *
     * @return string Returns the SteamID64 if successful or empty string on failure
     */
    public static function validate()
    {
        // Star off with some basic params
        $params = array(
            'openid.assoc_handle' => $_GET['openid_assoc_handle'],
            'openid.signed'       => $_GET['openid_signed'],
            'openid.sig'          => $_GET['openid_sig'],
            'openid.ns'           => 'http://specs.openid.net/auth/2.0',
        );

        // Get all the params that were sent back and resend them for validation
        $signed = explode(',', $_GET['openid_signed']);
        foreach ($signed as $item) {
            $val                       = $_GET['openid_' . str_replace('.', '_', $item)];
            $params['openid.' . $item] = get_magic_quotes_gpc() ? stripslashes($val) : $val;
        }
        // Finally, add the all important mode.
        $params['openid.mode'] = 'check_authentication';

        // Stored to send a Content-Length header
        $data    = http_build_query($params);
        $context = stream_context_create(array(
            'http' => array(
                'method'  => 'POST',
                'header'  =>
                    "Accept-language: en\r\n" .
                    "Content-type: application/x-www-form-urlencoded\r\n" .
                    "Content-Length: " . strlen($data) . "\r\n",
                'content' => $data,
            ),
        ));
        $result  = file_get_contents(self::STEAM_LOGIN_URL, false, $context);

        // Validate wheather it's true and if we have a good ID
        preg_match("#^http://steamcommunity.com/openid/id/([0-9]{17,25})#", $_GET['openid_claimed_id'], $matches);
        $steamID64 = is_numeric($matches[1]) ? $matches[1] : 0;

        // Return our final value
        return preg_match("#is_valid\s*:\s*true#i", $result) == 1 ? $steamID64 : false;
    }

    /**
     * @param      $steamId
     * @param null $displayName
     *
     * @return Member
     */
    public function createMember($steamId, $displayName = null)
    {
        /** @var Member $member */
        $member = Member::get()->filter(
            [
                'SteamID' => $steamId
            ]
        )->first();


        if ($member) {
            return $member;
        }

        $this->extend('onBeforeMemberCreate', $member);

        $member            = Member::create();
        $member->SteamID   = $steamId;
        $member->FirstName = $displayName;
        $member->write();

        $member->Groups()->add(
            Group::get()->filter(
                [
                    "Title" => "Member"
                ]
            )->first()
        );

        $member->write();

        $this->extend('onAfterMemberCreate', $member);

        return $member;
    }

    /**
     * @return \SilverStripe\Core\Config\Config_ForClass
     */
    public static function config()
    {
        return Config::inst()->forClass(self::class);
    }
}