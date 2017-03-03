# zanderwar/steamauth

Steam Authenticator for SilverStripe 4

When a user logs in via Steam, this module will check to see if a Member with the users SteamID already exists. If it doesn't it will create it as a regular user and log them in immediately.

If you're an admin, it's essential that you manually add your 64-bit Steam ID if you wish to be able to log into the administrator panel via Steam

#Installation

Installation is supported by composer only

```
composer require zander/steamauth ~1.0
```

1. Add these lines to your **/mysite/_config.php**

```php
// add this after your namespace or use FQCN instead
use SilverStripe\Security\Member;
use Zanderwar\SteamAuth\Extensions\MemberExtension;
use Zanderwar\SteamAuth\Extensions\PageControllerExtension;
/////

Member::add_extension(MemberExtension::class);
PageController::add_extension(PageControllerExtension::class);
```

2. Create a new page through the CMS using the SteamAuth Page Type

3. Create a `steamauth.yml` configuration file in `/mysite/_config.php`

```yml
Zanderwar\SteamAuth\SteamAuth:
  steam_api_key: ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789
  validate_url: "http://{{HOST}}/steamauth/validate"
```

`{{HOST}}` will automatically be replaced with `example.com` and `steamauth` should point to the SteamAuth Page Type url you created earlier

# Roadmap

- Forum Support