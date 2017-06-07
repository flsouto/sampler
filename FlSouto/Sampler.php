<?php

namespace FlSouto;

class Sampler{

	protected $file;

	protected static $sequence = 0;

	function __construct($input){

        $id = self::$sequence++;

        if($input instanceof self){
			$input = $input->file;
		}

		if(mb_substr($input, 0, 7)=='silence'){
            
            $len = mb_substr($input, 7);
            
            $this->file = __DIR__.'/tmp_dir/silence'.$id.'.wav';
		    shell_exec("sox -n -r 44100 -c 2 $this->file trim 0 $len");
		    
        } else {
		    
            $ext = explode('.',$input);
            $ext = end($ext);
            $this->file = __DIR__.'/tmp_dir/smp'.$id.'.'.$ext;
            copy($input, $this->file);
        }


	}

	static function silence($length){
	    return new self("silence $length");
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