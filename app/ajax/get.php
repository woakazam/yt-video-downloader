<?php

	require_once ($_SERVER['DOCUMENT_ROOT']. '/woa-Loader.php');

	function cURL($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	function getVideoDetails($videoID){
		$output = cURL('https://www.youtube.com/get_video_info?&video_id='. $videoID. '&asv=3&el=detailpage&hl=en_US');
		parse_str(urldecode($output), $videoDetails);
		return json_decode($videoDetails['player_response'], TRUE);
	}

	function getPlayer($url){
		$output = cURL($url);
		$finder = preg_match_all('@/yts/jsbin/player(.*js)@i', $output, $matches);
		return 'https://www.youtube.com'. $matches[0][0];
	}

	function getPlayerDechip($url){
		$output = cURL($url);
		$finder = preg_match_all('@a[.]split[(]""[)];(.*);return a[.]join[(]""[)]@i', $output, $matches);
		return $matches[1][0];
	}

	function setPlayerDechipFunction($url){
		$output = cURL($url);
		$finder = preg_match_all('@var \w+={(\w+:\w+\(.*?)}};@s', $output, $matches);
		$params = explode('},', $matches[1][0]);
		$return = [];
		foreach ($params as $index => $value) {
			$temp 	= explode(':', trim($value));
			$finder = preg_match_all('@[{](.*)@', $temp[1], $matches);
			$return[$temp[0]] = $matches[1][0];
		}
		return $return;
	}

	function setPlayerDechipPatterns($str, $dechip){
		$finder = preg_match_all('@'. $str. '.(\w+\(\w+,\d+\))@', $dechip, $matches);
		foreach ($matches as $index => $value) {
			$value = str_replace('(a,', '->(', $value);
			$matches[$index] = $value;
		}
		return $matches[1];
	}

	$BaseURL = 'https://www.youtube.com/watch?v=';
	$videoID = $_POST['id'];
	$details = getVideoDetails($videoID);
	$formats = $details['streamingData']['adaptiveFormats'];
	
	#Signature Decode Gerekmiyor!
	if (!isset($formats[0]['cipher'])){
		$output = [];
		foreach ($formats as $index => $value) {
			$output[$index]['Code'] = $formats[$index]['itag'];
			$output[$index]['Rate'] = round($formats[$index]['bitrate'] / 1000). ' kbps';
			$output[$index]['Type'] = isset($formats[$index]['qualityLabel']) === TRUE ? $formats[$index]['qualityLabel'] : $formats[$index]['quality'];
			$output[$index]['Format'] = isset($formats[$index]['audioQuality']) === TRUE ? 'Sadece Audio' : 'Sadece Video';
			$output[$index]['Link'] = $formats[$index]['url'];
		}
		$Success = array(
			'a' => 200,
			'b' => $output
		);
		returnJSON($Success);
	}

	#Signature Decode Gerekiyor!
	$player = getPlayer($BaseURL. $videoID);
	$dechip = getPlayerDechip($player);
	$setPlayerDechipFunction = setPlayerDechipFunction($player);
	$setPlayerDechipPatterns = setPlayerDechipPatterns(substr($dechip, 0, 2), $dechip);

	$output = [];
	foreach ($formats as $index => $value) {
		parse_str(urldecode($formats[$index]['cipher']), $Cipher);
		$Signature = str_split($Cipher['s']);
		for ($i = 0; $i < count($setPlayerDechipPatterns); $i++) {
			$Cmd = explode('->', $setPlayerDechipPatterns[$i]);
			$Nmb = intval(str_replace(['(', ')'], '', $Cmd[1]));
			$Fnc = $setPlayerDechipFunction[$Cmd[0]];
			switch ($Fnc) {
				case 'a.reverse()':
					$Signature = array_reverse($Signature);
					break;
				case 'var c=a[0];a[0]=a[b%a.length];a[b]=c':
					$c = $Signature[0];
					$Signature[0] = $Signature[$Nmb%count($Signature)];
					$Signature[$Nmb] = $c;
					break;
				case 'var c=a[0];a[0]=a[b%a.length];a[b%a.length]=c':
					$c = $Signature[0];
					$Signature[0] = $Signature[$Nmb%count($Signature)];
					$Signature[$Nmb%count($Signature)] = $c;
					break;
				case 'a.splice(0,b)':
					$Signature = array_splice($Signature, $Nmb);
					break;
				case 'a.reverse()':
					$Signature = array_reverse($Signature);
					break;
				
				default:
					$Failed = array(
						'a' => 500,
						'b' => null
					);
					returnJSON($Failed);
					break;
			}
		}
		$Signature = implode('', $Signature);

		$output[$index]['Code'] = $formats[$index]['itag'];
		$output[$index]['Rate'] = round($formats[$index]['bitrate'] / 1000). ' kbps';
		$output[$index]['Type'] = isset($formats[$index]['qualityLabel']) === TRUE ? $formats[$index]['qualityLabel'] : 'Tiny';
		$output[$index]['Format'] = isset($formats[$index]['audioQuality']) === TRUE ? 'Sadece Audio' : 'Sadece Video';

		unset($Cipher['s'], $Cipher['sp']);
		$Link = '';
		foreach ($Cipher as $key => $query) {
			$key == 'url' ? $Link .= $query : $Link .= '&'. $key. '='. $query;
		}
		$output[$index]['Link'] = $Link. '&sig='. $Signature;
	}

	$Success = array(
		'a' => 200,
		'b' => $output
	);
	returnJSON($Success);