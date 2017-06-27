<?php

namespace FlSouto;

if(!is_dir(__DIR__.'/tmp_dir/')){
    mkdir(__DIR__.'/tmp_dir/');
}

class Sampler{

	protected $file;

	protected static $sequence = 0;

	function __construct($input, $reference=false){

        $id = self::$sequence++;

        if($input instanceof self){
			$input = $input->file;
		}

		if(substr($input, 0, 7)=='silence'){
            
            $len = substr($input, 7);
            
            $this->file = __DIR__.'/tmp_dir/silence'.$id.'.wav';
		    shell_exec("sox -n -r 44100 -c 2 $this->file trim 0 $len");
		    
        } else {

		    if($reference){
		        $this->file = $input;
            } else {
                $ext = explode('.',$input);
                $ext = end($ext);
                $this->file = __DIR__.'/tmp_dir/smp'.$id.'.'.$ext;
                copy($input, $this->file);
            }
        }


	}

	static function silence($length){
	    return new self("silence $length");
    }

	function __invoke(){
		return new self($this);
	}

	function len(){
		$len = `soxi -d {$this->file}`;
		$parts = explode(":",$len);
		$len = 0;
		if(!empty($parts[1])){
			$len = intval($parts[1]) * 60;
		}
		$len += ltrim($parts[2],'0');
		return $len;

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
	
	function mix($input, $normalize=true){
		if($input instanceof self){
			$input = $input->file;
		}
		$out = __DIR__.'/tmp_dir/mod.wav';
		if(!$normalize){
			shell_exec("sox -m -v 1 {$this->file} -v 1 $input $out");			
		} else {
			shell_exec("sox -m {$this->file} $input $out");
		}
		copy($out, $this->file);
		return $this;
	}

	function unsilence($treshold='2.0'){
		return $this->mod("silence -l 1 0.1 1% -1 $treshold 1%");
	}
	
	function chop($num_slices){
	    $repeat = $num_slices - 1;
	    $trim = $this->len() / $num_slices;
	    return $this->mod("trim 0 $trim repeat $repeat");
    }
    
    function fade($from_val, $to_val, $attr='gain'){
        
        $steps = range($from_val, $to_val);
        $smp_size = $this->len() / count($steps);

        /** @var self $stream */
        $stream = null;

        foreach($steps as $i => $value){

            $offset = $i * $smp_size;
            $out = __DIR__.'/tmp_dir/fade.wav';
            shell_exec("sox $this->file $out trim $offset $smp_size $attr $value");
            if(is_null($stream)){
                $stream = new self($out);
            } else {
                $stream->add($out);
            }

        }

        shell_exec("mv {$stream->file} {$this->file}");

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

	function resize($newlen){
	    return $this->mod('speed '.($this->len()/$newlen));
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