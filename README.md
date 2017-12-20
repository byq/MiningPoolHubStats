This is a simple stats page which will show your current confirmed and unconfirmed coins, their balance in USD, and your current workers on MiningPoolHub.com

**INSTALLATION**
* Make sure you have curl extension for PHP installed, otherwise install it executing `apt install php5-curl` (for a Debian/Ubuntu system running PHP5). To ensure that curl extension is loaded restart apache with `service apache2 restart`.
* Copy minerstats.php, miningpoolhubstats.class.php and config.php into your web space.
* Update config.php to include your API key, selected FIAT currency for conversion and selected crypto for conversion
* open /minerstats.php in a browser

Note: You can also pass the information via GET, for example:

_example: minerstats.php?api_key=THIS_IS_MY_API_KEY_&amp;fiat=FIAT_CURRENCY_CODE

**Please make sure to only edit the config.php file!**


**FOR THOSE WITH NO EXPERIENCE WITH WEB SERVERS**

I have also created a web page for every one to use

(For USD conversion) https://miningpoolhubstats.com/USD/API_KEY_GOES_HERE

(For EURO conversion) https://miningpoolhubstats.com/EUR/API_KEY_GOES_HERE


**THIS IS AWESOME! I WANT TO HELP!**

If this helps you in any way and you feel so inclined to donate something, I won't say no :)

BTC: 17ZjS6ZJTCNWrd17kkZpgRHYZJjkq5qT5A

LTC: LdGQgurUKH2J7iBBPcXWyLKUb8uUgXCfFF

ETH: 0x6e259a08a1596653cbf66b2ae2c36c46ca123523
