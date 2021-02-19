<?php
namespace hellsh;
class smw
{
	private $hostname;
	private $username;
	private $password;
	private $database;
	private $connected = false;
	private $con = null;

	function __construct($hostname, $username, $password, $database)
	{
		$this->hostname = $hostname;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
	}

	function query()
	{
		if(!$this->connected)
		{
			$this->con = new \mysqli($this->hostname, $this->username, $this->password, $this->database);
			$this->connected = true;
		}
		$arg = func_get_args();
		$res = NULL;
		if($arg)
		{
			if(count($arg) > 2)
			{
				if($stmt = $this->con->prepare(array_shift($arg)))
				{
					(new \ReflectionClass("mysqli_stmt"))->getMethod("bind_param")->invokeArgs($stmt, $arg);
					$stmt->execute();
					$res = $stmt->get_result();
					if($res instanceof \mysqli_result)
					{
						$fiels = json_decode(json_encode($res->fetch_fields()), true);
						$res = $res->fetch_all();
						$stmt->close();
						$nres = array();
						foreach($res as $row)
						{
							$nrow = array();
							foreach($row as $i => $val)
							{
								$nrow[$fiels[$i]["name"]] = $val;
							}
							array_push($nres, $nrow);
						}
						$res = $nres;
					}
					else
					{
						if($res === false && $stmt->errno != 0)
						{
							trigger_error("mysqli error {$stmt->error} ({$stmt->errno})");
						}
						return $res;
					}
				}
			}
			else if(count($arg) < 2)
			{
				if($query = $this->con->query($arg[0]))
				{
					if($query instanceof \mysqli_result)
					{
						$res = array();
						while($r = $query->fetch_assoc())
						{
							array_push($res, $r);
						}
					}
					else
					{
						return $query;
					}
				}
			}
			else
			{
				throw new \Exception("hellsh\smw::query can't have only 2 arguments.");
			}
		}
		else
		{
			throw new \Exception("hellsh\smw::query needs at least 1 argument.");
		}
		return $res;
	}

	function close()
	{
		$this->con->close();
		$this->connected = false;
	}
}
