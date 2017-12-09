<?php
/**
Copyright (C) 2017  James Dimitrov (Jimok82)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *
 */

//SET API KEY HERE
//You can get your API Key Here:
//https://miningpoolhub.com/?page=account&action=edit

$api_key = "INSERT_YOUR_API_KEY_HERE";

$sum = 0;
$unsum = 0;


$coins = array(
	'bitcoin' => 'BTC',
	'ethereum' => 'ETH',
	'monero' => 'XMR',
);

$thresholds = array(
	'bitcoin' => '0.002',
	'ethereum' => '0.01',
	'monero' => '0.05'
);

$all_coins = array(
	'bitcoin' => 'BTC',
	'ethereum' => 'ETH',
	'monero' => 'XMR',
	'zcash' => 'ZEC',
	'vertcoin' => 'VTC',
	'feathercoin' => 'FTC',
	'digibyte-skein' => 'DGB',
	'musicoin' => 'MUSIC',
	'ethereum-classic' => 'ETC',
	'siacoin' => 'SC',
	'zcoin' => 'XZC',
	'bitcoin-gold' => 'BTG',
	'zencash' => 'ZEN'
);

$ch = curl_init("https://miningpoolhub.com/index.php?page=api&action=getuserallbalances&api_key=" . $api_key);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);
$result = json_decode($output);

$ch = curl_init("https://min-api.cryptocompare.com/data/pricemulti?fsyms=BTC,ETH,FTC,DGB,VTC,ZEN,MUSIC,ETC,ZEC,XMR,SC,XZC,BTG&tsyms=USD");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);
$prices = json_decode($output);


$coin_data = array();
$worker_data = array();
foreach ($result->getuserallbalances->data as $row) {

	$last_hour_stats_for_coin = json_decode($last_hour_stats->payload);
	$last_day_stats_for_coin = json_decode($last_day_stats->payload);

	$last_hour_data = $last_hour_stats_for_coin->{$row->coin};
	$last_day_data = $last_day_stats_for_coin->{$row->coin};

	$coin = (object)array();

	$coin->coin = $row->coin;
	$coin->confirmed = number_format($row->confirmed + $row->ae_confirmed + $row->exchange, 8);
	$coin->unconfirmed = number_format($row->unconfirmed + $row->ae_unconfirmed, 8);


	foreach ($prices as $price) {
		if (key_exists($row->coin, $all_coins)) {

			$code = $all_coins["$row->coin"];

			$price = $prices->{$code}->USD;

			$coin->confirmed_value = number_format($price * $coin->confirmed, 2);
			$coin->unconfirmed_value = number_format($price * $coin->unconfirmed, 2);

		}

	}

	$coin_data[] = $coin;

	$ch = curl_init("https://" . $row->coin . ".miningpoolhub.com/index.php?page=api&action=getuserworkers&api_key=".$api_key);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);
	$workers = json_decode($output);

	$worker_list = $workers->getuserworkers->data;

	$active_workers = array();
	foreach ($worker_list as $worker) {
		if ($worker->hashrate != 0) {
			$worker->coin = $row->coin;
			$worker_data[] = $worker;
		}
	}

}

$confirmed_total = 0;
$unconfirmed_total = 0;

foreach ($coin_data as $coin_datum) {
	$confirmed_total += $coin_datum->confirmed_value;
	$unconfirmed_total += $coin_datum->unconfirmed_value;
}

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
                    <th>Confirmed</th>
                    <th>Unconfirmed</th>
                    <th>Value (Conf.)</th>
                    <th>Value (Unconf.)</th>
                </tr>
				<?php

				foreach ($coin_data as $coin) {
					?>
                    <tr>
                        <td <?php if (array_key_exists($coin->coin, $coins)) {
							echo 'class="info"';
						} ?>>
                            <span <?php if (array_key_exists($coin->coin, $thresholds) && $coin->confirmed >= $thresholds["$coin->coin"]) {
	                            echo 'style="font-weight: bold; color: red;"';
                            } ?> ><?php echo $coin->coin; ?></span></td>
                        <td <?php if (array_key_exists($coin->coin, $coins)) {
							echo 'class="info"';
						} ?>><?php echo $coin->confirmed; ?><?php if (array_key_exists($coin->coin, $coins)) {
								echo " (" . number_format(100 * $coin->confirmed / $thresholds["$coin->coin"], 0) . "%)";
							} ?></td>
                        <td <?php if (array_key_exists($coin->coin, $coins)) {
							echo 'class="info"';
						} ?>><?php echo $coin->unconfirmed; ?></td>
                        <td <?php if ($coin->confirmed_value > 0) {
							echo 'class="success"';
						} ?>>$<?php echo number_format($coin->confirmed_value, 2); ?></td>
                        <td <?php if ($coin->unconfirmed_value > 0) {
							echo 'class="success"';
						} ?>>$<?php echo number_format($coin->unconfirmed_value, 2); ?></td>
                    </tr>
					<?php
				}
				?>
                <tr>
                    <td>TOTAL</td>
                    <td></td>
                    <td></td>
                    <td>$<?php echo number_format($confirmed_total, 2); ?></td>
                    <td>$<?php echo number_format($unconfirmed_total, 2); ?></td>
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
                        <td><?php echo $worker->monitor; ?></td>
                    </tr>
				<?php } ?>
            </table>
        </div>
    </div>
</body>
