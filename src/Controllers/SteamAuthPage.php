<?php
namespace Zanderwar\SteamAuth\Controllers;

use SilverStripe\Control\Session;
use SilverStripe\Security\Member;
use Zanderwar\Scraplands\Models\Notification;
use Zanderwar\Scraplands\Models\Player;
use Zanderwar\SteamAuth\SteamAuth;

class SteamAuthPage extends \Page
{

}

class SteamAuthPageController extends \PageController
{
    private static $allowed_actions = [
        'validate',
        'logout'
    ];

    public function validate()
    {

        $redirectTo = Session::get('SteamAuth.ReturnUrl');

        if (!$redirectTo) {
            $redirectTo = "//{$_SERVER['HTTP_HOST']}/";
        }

        if (!$steamId = SteamAuth::validate()) {
            // todo
            return $this->redirect($redirectTo);
        }


        /** @var Member $member */
        $member = Member::get()->filter(
            [
                'SteamID' => $steamId
            ]
        )->first();

        if (!$member) {
            return $this->render([
                'Error'   => true,
                'Message' => 'You must have previously joined our game server to be able to log in to our website'
            ]);
        }

        $member->logIn();
        $player  = Player::currentPlayer();
        $welcome = Notification::get()->filter(
            [
                'PlayerID' => $player->ID,
                'Tag'      => 'welcome'
            ]
        )->first();

        if (!$welcome) {
            $notification           = Notification::create();
            $notification->PlayerID = $player->ID;
            $notification->Message  = "Welcome to Scraplands! Thank you for choosing us to be your #1 server to play Rust on. We love you!";
            $notification->Tag      = 'welcome';
            $notification->write();
        }

        return $this->redirect($redirectTo);
    }

    public function logOut()
    {
        Member::singleton()->logOut();
        $this->redirectBack();
    }
}