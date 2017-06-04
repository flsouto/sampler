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

	function clone(){
		return new Sampler($this);
	}

	function mod($filters){
		$out = __DIR__.'/tmp_dir/mod.wav';
		shell_exec("sox {$this->file} $out $filters");
		copy($out, $this->file);
		return $this;
	}

	function cut($from, $to){
		return $this->mod("trim $from $to");
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

	function __destruct(){
		if(file_exists($this->file)){
			unlink($this->file);
		}
	}

}