<?php
//error_reporting(E_ERROR);
//ini_set('display_errors', 1);
/**
 * Copyright (C) 2017  James Dimitrov (Jimok82)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *
 */

//EDIT BELOW THIS LINE!!!

//SET API KEY HERE
//You can get your API Key Here:
//https://miningpoolhub.com/?page=account&action=edit
$api_key = "INSERT_YOUR_API_KEY_HERE";

//Set FIAT code if you like (USD, EUR, GBP, etc.)
$fiat = "SET_FIAT_CODE_HERE";

//Set CRYPTO code if you like (BTC, ETH, etc.)
$crypto = "SET_CRYPTO_CODE_HERE";


//DO NOT EDIT BELOW THIS LINE!!!


//Check to see we have an API key. Show an error if none is defined.
if ($_GET['api_key'] != null) {
	$api_key = filter_var($_GET['api_key'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
}
if ($api_key == null || $api_key == "INSERT_YOUR_API_KEY_HERE" || strlen($api_key) <= 32) {
	die("Please enter an API key: example: " . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?api_key=ENTER_YOUR_KEY_HERE");
}

//Check to see what we are converting to. Default to USD
if ($_GET['fiat'] != null) {
	$fiat = filter_var($_GET['fiat'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
}
if ($fiat == "SET_FIAT_CODE_HERE" || strlen($fiat) >= 4) {
	$fiat = "USD";
}

//Check to see what we are converting to. Default to BTC
if ($_GET['crypto'] != null) {
	$crypto = filter_var($_GET['crypto'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
}
if ($crypto == "SET_CRYPTO_CODE_HERE" || strlen($crypto) >= 4) {
	$crypto = "ETH";
}

//Initialize some variables
$sum_of_confirmed = 0;
$sum_of_unconfirmed = 0;
$confirmed_total = 0;
$unconfirmed_total = 0;
$confirmed_total_c = 0;
$unconfirmed_total_c = 0;
$coin_data = array();
$worker_data = array();

//Define the coin codes and minimum payout thresholds for all coins
$all_coins = (object)array(
	'bitcoin' => (object)array('code' => 'BTC', 'min_payout' => '0.002'),
	'ethereum' => (object)array('code' => 'ETH', 'min_payout' => '0.01'),
	'monero' => (object)array('code' => 'XMR', 'min_payout' => '0.05'),
	'zcash' => (object)array('code' => 'ZEC', 'min_payout' => '0.002'),
	'vertcoin' => (object)array('code' => 'VTC', 'min_payout' => '0.1'),
	'feathercoin' => (object)array('code' => 'FTC', 'min_payout' => '0.002'),
	'digibyte-skein' => (object)array('code' => 'DGB', 'min_payout' => '0.01'),
	'musicoin' => (object)array('code' => 'MUSIC', 'min_payout' => '0.002'),
	'ethereum-classic' => (object)array('code' => 'ETC', 'min_payout' => '0002'),
	'siacoin' => (object)array('code' => 'SC', 'min_payout' => '0.002'),
	'zcoin' => (object)array('code' => 'XZC', 'min_payout' => '0.002'),
	'bitcoin-gold' => (object)array('code' => 'BTG', 'min_payout' => '0.002'),
	'bitcoin-cash' => (object)array('code' => 'BCH', 'min_payout' => '0.0005'),
	'zencash' => (object)array('code' => 'ZEN', 'min_payout' => '0.002'),
	'litecoin' => (object)array('code' => 'LTC', 'min_payout' => '0.002'),
	'monacoin' => (object)array('code' => 'MONA', 'min_payout' => '0.1'),
	'groestlcoin' => (object)array('code' => 'GRS', 'min_payout' => '0.002')
);

function make_api_call($url)
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);
	return json_decode($output);
}

//Make the main API call
$result = make_api_call("https://miningpoolhub.com/index.php?page=api&action=getuserallbalances&api_key=" . $api_key);


//Set up the conversion string for all coins we need from the cryptocompare API
$all_coin_list = array();
foreach ($all_coins as $coin) {
	$all_coin_list[] = $coin->code;
}
$all_coin_string = implode(",", $all_coin_list);

//Make the conversion rate API call
$prices = make_api_call("https://min-api.cryptocompare.com/data/pricemulti?fsyms=" . $all_coin_string . "&tsyms=" . $fiat . "," . $crypto);


//Main loop - Get all the coin info we can get
foreach ($result->getuserallbalances->data as $row) {

	$coin = (object)array();

	$coin->coin = $row->coin;
	$coin->confirmed = number_format($row->confirmed + $row->ae_confirmed + $row->exchange, 8);
	$coin->unconfirmed = number_format($row->unconfirmed + $row->ae_unconfirmed, 8);


	//If a conversion rate was returned by API, set it
	foreach ($prices as $price) {
		if (key_exists($row->coin, $all_coins)) {

			$code = $all_coins->{$row->coin}->code;

			//Get fiat prices
			$price = $prices->{$code}->{$fiat};

			$coin->confirmed_value = number_format($price * $coin->confirmed, 2);
			$coin->unconfirmed_value = number_format($price * $coin->unconfirmed, 2);

			//get crypto prices
			$cprice = $prices->{$code}->{$crypto};

			$coin->confirmed_value_c = number_format($cprice * $coin->confirmed, 8);
			$coin->unconfirmed_value_c = number_format($cprice * $coin->unconfirmed, 8);

		}

	}

	//Add the coin data into the main array we build the table with
	$coin_data[] = $coin;

	//Get all of the worker stats - Separate API call for each coin...gross...
	$workers = make_api_call("https://" . $row->coin . ".miningpoolhub.com/index.php?page=api&action=getuserworkers&api_key=" . $api_key);


	//Get the stats for every active worker with hashrate > 0
	$worker_list = $workers->getuserworkers->data;
	$active_workers = array();
	foreach ($worker_list as $worker) {
		if ($worker->hashrate != 0) {
			$worker->coin = $row->coin;
			$worker_data[] = $worker;
		}
	}

}

//Sum up the totals by traversing the coin data loop and summing everything up
foreach ($coin_data as $coin_datum) {
	$confirmed_total += $coin_datum->confirmed_value;
	$unconfirmed_total += $coin_datum->unconfirmed_value;
	$confirmed_total_c += $coin_datum->confirmed_value_c;
	$unconfirmed_total_c += $coin_datum->unconfirmed_value_c;
}


//GENERATE THE UI HERE
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Miner Stats</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <style>
        * {
            font-size: 9px;
            line-height: 1.0;
        }
    </style>
</head>
<body>
<script language="JavaScript">
    var timerInit = function (cb, time, howOften) {
        // time passed in is seconds, we convert to ms
        var convertedTime = time * 1000;
        var convetedDuration = howOften * 1000;
        var args = arguments;
        var funcs = [];

        for (var i = convertedTime; i > 0; i -= convetedDuration) {
            (function (z) {
                // create our return functions, within a private scope to preserve the loop value
                // with ES6 we can use let i = convertedTime
                funcs.push(setTimeout.bind(this, cb.bind(this, args), z));

            })(i);
        }

        // return a function that gets called on load, or whatever our event is
        return function () {

            //execute all our functions with their corresponsing timeouts
            funcs.forEach(function (f) {
                f();
            });
        };

    };

    // our doer function has no knowledge that its being looped or that it has a timeout
    var doer = function () {
        var el = document.querySelector('#timer');
        var previousValue = Number(el.innerHTML);
        if (previousValue == 1) {
            location.reload();
        } else {
            document.querySelector('#timer').innerHTML = previousValue - 1;
        }
    };


    // call the initial timer function, with the cb, how many iterations we want (30 seconds), and what the duration between iterations is (1 second)
    window.onload = timerInit(doer, 60, 1);
</script>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">MinerStats</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">Stats</a></li>
            </ul>
            <ul class="nav navbar-nav pull-right">
                <li>
                    <a id="timer" class="nav">60</a>
                </li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>
<div class="container"><br><br><br><br><br>
    <h1>MiningPoolHub Stats</h1>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-striped">
                <tr>
                    <th>Coin</th>
                    <th>Confirmed (% of min payout)</th>
                    <th>Unconfirmed</th>
                    <th>Total</th>
                    <th>Total in <?php echo $crypto; ?></th>
                    <th>Value (Conf.)</th>
                    <th>Value (Unconf.)</th>
                    <th>Value (Total)</th>
                </tr>
				<?php

				foreach ($coin_data as $coin) {
					?>
                    <tr>
                        <td>
                            <span <?php if ($coin->confirmed >= $all_coins->{$coin->coin}->min_payout) {
	                            echo 'style="font-weight: bold; color: red;"';
                            } ?> ><?php echo $coin->coin; ?></span></td>
                        <td><?php echo $coin->confirmed; ?><?php echo " (" . number_format(100 * $coin->confirmed / $all_coins->{$coin->coin}->min_payout, 0) . "%)"; ?></td>
                        <td <?php if (array_key_exists($coin->coin, $payout_coins)) {
							echo 'class="info"';
						} ?>><?php echo $coin->unconfirmed; ?></td>
                        <td <?php if (array_key_exists($coin->coin, $payout_coins)) {
							echo 'class="info"';
						} ?>><?php echo number_format($coin->confirmed + $coin->unconfirmed, 8); ?></td>
                        <td <?php if ($coin->unconfirmed_value > 0) {
							echo 'class="success"';
						} ?>><?php echo number_format($coin->confirmed_value_c + $coin->unconfirmed_value_c, 4) . " " . $crypto; ?></td>
                        <td <?php if ($coin->confirmed_value > 0) {
							echo 'class="success"';
						} ?>><?php echo number_format($coin->confirmed_value, 2) . " " . $fiat; ?></td>
                        <td <?php if ($coin->unconfirmed_value > 0) {
							echo 'class="success"';
						} ?>><?php echo number_format($coin->unconfirmed_value, 2) . " " . $fiat; ?></td>
                        <td <?php if ($coin->unconfirmed_value > 0) {
							echo 'class="success"';
						} ?>><?php echo number_format($coin->confirmed_value + $coin->unconfirmed_value, 2) . " " . $fiat; ?></td>
                    </tr>
					<?php
				}
				?>
                <tr>
                    <td>TOTAL</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><?php echo number_format($confirmed_total_c + $unconfirmed_total_c, 4) . " " . $crypto; ?></td>
                    <td><?php echo number_format($confirmed_total, 2) . " " . $fiat; ?></td>
                    <td><?php echo number_format($unconfirmed_total, 2) . " " . $fiat; ?></td>
                    <td><?php echo number_format($confirmed_total + $unconfirmed_total, 2) . " " . $fiat; ?></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-striped">
                <tr>
                    <th>Worker</th>
                    <th>Coin</th>
                    <th>Hashrate</th>
                    <th>Monitor</th>
                </tr>
				<?php foreach ($worker_data as $worker) { ?>
                    <tr>
                        <td><?php echo $worker->username; ?></td>
                        <td><?php echo $worker->coin; ?></td>
                        <td><?php echo number_format($worker->hashrate, 2); ?></td>
                        <td><?php echo $worker->monitor == 1 ? "Enabled" : "Disabled"; ?></td>
                    </tr>
				<?php } ?>
            </table>
        </div>
    </div>
</body>
