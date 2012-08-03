<?PHP
    class Error
    {
        // Singleton object. Leave $me alone.
        private static $me;

        public $errors; // Array of errors
        public $style;  // CSS rules to apply to error elements

        private function __construct($style = "border:1px solid red;")
        {
            $this->errors = array();
            $this->style = $style;
        }

        // Get Singleton object
        public static function getError()
        {
            if(is_null(self::$me))
                self::$me = new Error();
            return self::$me;
        }

        // Returns an unordered list of error messages
        public function __tostring()
        {
            return $this->alert();
        }

        // Returns true if there are no errors
        public function ok()
        {
            return count($this->errors) == 0;
        }

        // Manually add an error
        public function add($id, $msg)
        {
            if(isset($this->errors[$id]) && !is_array($this->errors[$id]))
                $this->errors[$id] = array($msg);
            else
                $this->errors[$id][] = $msg;
        }

        // Delete all errors associated with an element's id
        public function delete($id)
        {
            unset($this->errors[$id]);
        }

        // Returns the error message associated with an element.
        // This may return a string or an array - so be sure to test before echoing!
        public function msg($id)
        {
            return $this->errors[$id];
        }

        // Outputs the CSS to style the error elements
        public function css($header = true)
        {
            $out = '';
            if(count($this->errors) > 0)
            {
                if($header) $out .= '<style type="text/css" media="screen">';
                $out .= "#" . implode(", #", array_keys($this->errors)) . " { {$this->style} }";
                if($header) $out .= '</style>';
            }
            echo $out;
        }

        // Returns an unordered list of error messages
        public function ul($class = 'warn')
        {
            if(count($this->errors) == 0) return '';

            $out = "<ul class='$class'>";
            foreach($this->errors as $error)
                $out .= "<li>" . implode("</li><li>", $error) . "</li>";
            $out .= "</ul>";

            return $out;
        }

        // Returns error alerts
        public function alert()
        {
            if(count($this->errors) == 0)
                return '';

            $out = '';
            foreach($this->errors as $error)
                $out .= "<p class='alert error'>" . implode(' ', $error) . "</p>";

            return $out;
        }

        // Below are a collection of tests for error conditions in your user's input...
        // Be sure to customize these to suit your app's needs. Especially the error messages.

        // Is the (string) value empty?
        public function blank($val, $id, $name = null)
        {
            if(trim($val) == '')
            {
                if(is_null($name)) $name = ucwords($id);
                $this->add($id, "$name cannot be left blank.");
                return false;
            }

            return true;
        }

        // Is a number between a given range? (inclusive)
        public function range($val, $lower, $upper, $id, $name = null)
        {
            if($val < $lower || $val > $upper)
            {
                if(is_null($name)) $name = ucwords($id);
                $this->add($id, "$name must be between $lower and $upper.");
                return false;
            }

            return true;
        }

        // Is a string an appropriate length?
        public function length($val, $lower, $upper, $id, $name = null)
        {
            if(strlen($val) < $lower)
            {
                if(is_null($name)) $name = ucwords($id);
                $this->add($id, "$name must be at least $lower characters.");
                return false;
            }
            elseif(strlen($val) > $upper)
            {
                if(is_null($name)) $name = ucwords($id);
                $this->add($id, "$name cannot be more than $upper characters long.");
                return false;
            }

            return true;
        }

        // Do the passwords match?
        public function passwords($pass1, $pass2, $id)
        {
            if($pass1 !== $pass2)
            {
                $this->add($id, 'The passwords you entered do not match.');
                return false;
            }

            return true;
        }

        // Does a value match a given regex?
        public function regex($val, $regex, $id, $msg)
        {
            if(preg_match($regex, $val) === 0)
            {
                $this->add($id, $msg);
                return false;
            }

            return true;
        }

        // Is an email address valid?
        public function email($val, $id = 'email')
        {
            if(!preg_match("/^([_a-z0-9+-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $val))
            {
                $this->add($id, 'The email address you entered is not valid.');
                return false;
            }

            return true;
        }

        // Is a string a parseable and valid date?
        public function date($val, $id)
        {
            if(chkdate($val) === false)
            {
                $this->add($id, 'Please enter a valid date');
                return false;
            }

            return true;
        }

        // Is a birth date at least 18 years old?
        public function adult($val, $id)
        {
            if( dater($val) > ( (date('Y') - 18) . date('-m-d H:i:s') ) )
            {
                $this->add($id, 'You must be at least 18 years old.');
                return false;
            }

            return true;
        }

        // Is a string a valid phone number?
        public function phone($val, $id)
        {
            $val = preg_replace('/[^0-9]/', '', $val);
            if(strlen($val) != 7 && strlen($val) != 10)
            {
                $this->add($id, 'Please enter a valid 7 or 10 digit phone number.');
                return false;
            }

            return true;
        }

        // Did we get a successful file upload?
        // Typically, you'd pass in $_FILES['file']
        public function upload($val, $id)
        {
            if(!is_uploaded_file($val['tmp_name']) || !is_readable($val['tmp_name']))
            {
                $this->add($id, 'Your file was not uploaded successfully. Please try again.');
                return false;
            }

            return true;
        }

        // Test if string $val is a valid, decimal number.
        public function nan($val, $id, $name = null)
        {
            if(preg_match('/^-?[0-9]+(\.[0-9]+)?$/', $val) == 0)
            {
                if(is_null($name)) $name = ucwords($id);
                $this->add($id, "$name must be a number.");
                return false;
            }
            return true;
        }

        // Valid URL?
        // This is hardly perfect, but it's good enough for now...
        // TODO: Make URL validation more robust
        public function url($val, $id, $name = null)
        {
            $info = @parse_url($val);
            if(($info === false) || ($info['scheme'] != 'http' && $info['scheme'] != 'https') || ($info['host'] == ''))
            {
                if(is_null($name)) $name = ucwords($id);
                $this->add($id, "$name is not a valid URL.");
                return false;
            }
            return true;
        }
    }
