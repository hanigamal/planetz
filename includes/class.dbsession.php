<?php
/**
* @type Class
* @desc Save sessions to DB
* @package core
* @required DB class
* @author Hani Gamal

The MIT License (MIT)

Copyright (c) Sun Nov 01 2015 Hani Gamal

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORTOR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Instructions:

	Now, before you run this next code on production, you need to tweak two values in your php.ini file: session.gc_probability and session.gc_maxlifetime. The first one, in tandem with session.gc_divisor, sets how likely it is for PHP to trigger session clean up with each page request. By default, session.gc_probability is 1 and session.gc_divisor is 1000, which means it will execute session clean up once in every 1000 scripts.
***/
		class DBSession
		{
				public static function register()
				{
						ini_set('session.save_handler', 'user');
						session_set_save_handler(array('DBSession', 'open'), array('DBSession', 'close'), array('DBSession', 'read'), array('DBSession', 'write'), array('DBSession', 'destroy'), array('DBSession', 'gc'));
				}

				public static function open()
				{
						$db = Database::getDatabase();
						return $db->isWriteConnected();
				}

				public static function close()
				{
						return true;
				}

				public static function read($id)
				{
						$db = Database::getDatabase();
						$db->query('SELECT `data` FROM `sessions` WHERE `id` = :id:', array('id' => $id));
						return $db->hasRows() ? $db->getValue() : '';
				}

				public static function write($id, $data)
				{
						$db = Database::getDatabase();
						$db->query('DELETE FROM `sessions` WHERE `id` = :id:', array('id' => $id));
						$db->query('INSERT INTO `sessions` (`id`, `data`, `updated_on`) VALUES (:id:, :data:, :updated_on:)', array('id' => $id, 'data' => $data, 'updated_on' => time()));
						return ($db->affectedRows() == 1);
				}

				public static function destroy($id)
				{
						$db = Database::getDatabase();
						$db->query('DELETE FROM `sessions` WHERE `id` = :id:', array('id' => $id));
						return ($db->affectedRows() == 1);
				}

				public static function gc($max)
				{
						$db = Database::getDatabase();
						$db->query('DELETE FROM `sessions` WHERE `updated_on` < :updated_on:', array('updated_on' => time() - $max));
						return true;
				}
		}
