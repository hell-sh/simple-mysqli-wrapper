<?php
class DBAPI
{
	private $connected = false;
	private $db = null;

	function query()
	{
		if(!$this->connected)
		{
			$this->db = new mysqli("hostname", "username", "password", "database"); // CHANGE LOGIN DATA HERE
			$this->connected = true;
		}
		$arg = func_get_args();
		$res = NULL;
		if($arg)
		{
			if(count($arg) > 2)
			{
				if($stmt = $this->db->prepare($arg[0]))
				{
					$i = 0;
					$bind_names[] = $arg[1];
					foreach($arg as $i => $a)
					{
						if($i > 1)
						{
							$bind_name = 'bind'.$a;
							$$bind_name = $a;
							$bind_names[] = &$$bind_name;
						}
						$i++;
					}
					call_user_func_array(array($stmt,'bind_param'),$bind_names);
					$stmt->execute();
					$res = $stmt->get_result();
					if($res instanceof mysqli_result)
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
				if($query = $this->db->query($arg[0]))
				{
					if($query instanceof mysqli_result)
					{
						$res = array();
						while($r = $query->fetch_assoc())
						{
							array_push($res, $r);
						}
					} else
					{
						return $query;
					}
				}
			}
			else
			{
				throw new Exception("DBAPI::query can't have only 2 Arguments.");
			}
		}
		else
		{
			throw new Exception("DBAPI::query needs at least 1 Argument.");
		}
		return $res;
	}

	function close()
	{
		$this->db->close();
		$this->connected = false;
	}
}
$db = new DBAPI();
?>
