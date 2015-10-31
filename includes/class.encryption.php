<?php
/**
* Class for encrypt data with base64_encode and encode data with base64_decode
* encrypte data from base64 with mcrypte function
* @author mohamed fawzy
* @license GPL
*/

class Encryption {
		// key for hashing algorthim
		private $skey   = "ljHjyIjiREIPO08542#69^@)(";

		/**
		*   encrypte  string with base64_encode
		*  @param string
		*  @return string $data
		*/
		public  function safe_b64encode($string) {

				$data = base64_encode($string);
				$data = str_replace(array('+','/','='),array('-','_',''),$data);
				return $data;
		}
		/**
		* decrypte string with base64_decode
		* @param string
		* @return string data
		*/
		public function safe_b64decode($string) {
				$data = str_replace(array('-','_'),array('+','/'),$string);
				$mod4 = strlen($data) % 4;
				if ($mod4) {
						$data .= substr('====', $mod4);
				}
				return base64_decode($data);
		}

		/**
		* encode our hashed safe_b64encode function string with mcrypt_encrypt then add salt key
		*  @param string
		* @return hashed string
		*/

		public  function encode($value){

				if(!$value){return false;}
				$text = $value;
				$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
				$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
				$crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->skey, $text, MCRYPT_MODE_ECB, $iv);
				return trim($this->safe_b64encode($crypttext));
		}

		/**
		* decode our hashed safe_b64decode function string with mcrypt_decrypt then remove salt key
		*  @param string
		* @return hashed string
		*/
		public function decode($value){

				if(!$value){return false;}
				$crypttext = $this->safe_b64decode($value);
				$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
				$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
				$decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->skey, $crypttext, MCRYPT_MODE_ECB, $iv);
				return trim($decrypttext);
		}
}
?>
