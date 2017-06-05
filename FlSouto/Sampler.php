<?php

namespace FlSouto;

class Sampler{

	protected $file;

	protected static $sequence = 0;

	function __construct($input){

		if($input instanceof self){
			$input = $input->file;
		}

		$ext = explode('.',$input);
		$ext = end($ext);

		$id = self::$sequence++;

		$this->file = __DIR__.'/tmp_dir/smp'.$id.'.'.$ext;

		copy($input, $this->file);

	}

	function __invoke(){
		return new self($this);
	}

	function mod($filters){
		$out = __DIR__.'/tmp_dir/mod.wav';
		shell_exec("sox {$this->file} $out $filters");
		copy($out, $this->file);
		return $this;
	}

	function cut($offset, $length){
		return $this->mod("trim $offset $length fade 0 $length 0");
	}

	function add($input){
		if($input instanceof self){
			$input = $input->file;
		}
		$out = __DIR__.'/tmp_dir/mod.wav';
		shell_exec("sox {$this->file} $input $out");
		copy($out, $this->file);
		return $this;
	}
	
	function mix($input){
		if($input instanceof self){
			$input = $input->file;
		}
		$out = __DIR__.'/tmp_dir/mod.wav';
		shell_exec("sox -m {$this->file} $input $out");
		copy($out, $this->file);
		return $this;
	}

	function save($as){
		copy($this->file, $as);
	}

	function x($times){
		if($times<2){
			return $this;
		}
		return $this->mod('repeat '.($times-1));
	}

	function play(){
		shell_exec("play {$this->file}");
	}

	function __destruct(){
		if(file_exists($this->file)){
			unlink($this->file);
		}
	}

}