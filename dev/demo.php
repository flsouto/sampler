<?php

require __DIR__.'/../FlSouto/Sampler.php';
use FlSouto\Sampler;

// 25 + 30 + 35 + 36 + 37 + 16 (1) + 17 (1)


function make(){

	$array = [25,30,35,36,37,16,17];
	$num = $array[array_rand($array)];

	$track = "/www/synth/projects5/120bpm/pattern{$num}.wav";

	$s = new Sampler($track);
	$s1 = $s()->cut(10,1)->x(4)->mod('pitch 100 reverb');
	//$s1->mix($s()->cut(5,.2)->x(8)->mod('pitch 100'));

	$s1->mix($s()->cut(9,2)->x(2));


	$trem1 = rand(1,4);
	$trem2 = rand(1,4);

	$a = $s1()->x(2)->mod("tremolo $trem1 99 pitch 1 reverb reverb gain 10 speed 1");
	$b = $s1()->x(1)->mod("tremolo $trem2 89 pitch 1 reverb reverb gain 10 speed .5");
	$a->mix($b)->mod('gain 15 speed');

	if(rand(0,1) && !in_array($n,[16,17])){
		$a->add(
			$a()->mod('speed .5 tempo 2')
		);
	} else {
		$a->x(2);
	}
	$a->mod('gain 15');

	return $a;


}

$a = make();

for($i=1;$i<=30;$i++){
	$a->add(make());
}

$a->play();



