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
				if($stmt = $this->con->prepare($arg[0]))
				{
					$i = 0;
					$bind_names[] = $arg[1];
					foreach($arg as $i => $a)
					{
						if($i > 1)
						{
							$bind_name = "bind".$a;
							$$bind_name = $a;
							$bind_names[] = &$$bind_name;
						}
						$i++;
					}
					call_user_func_array(array($stmt, "bind_param"),$bind_names);
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
